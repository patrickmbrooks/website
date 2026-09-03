<?php
/**
 * Brooks Law Essentials — image optimizer module.
 *
 * The Simple Image Optimizer, merged into Essentials (v2.0.0). Compresses
 * JPEG/PNG uploads, auto-rotates, strips EXIF/GPS, scales oversized
 * originals, optional WebP output, plus a bulk optimizer for the existing
 * library (Media → Image Optimizer). Runs only at upload time — safe to
 * leave active permanently.
 *
 * If the ORIGINAL standalone "Simple Image Optimizer" plugin is still
 * active, this module steps aside (class guard below) — deactivate and
 * delete the standalone plugin after installing Essentials 2.0.0.
 *
 * @package Brooks_Essentials
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Simple_Image_Optimizer' ) ) :

final class Simple_Image_Optimizer {

	const OPTION     = 'sio_options';
	const META_FLAG  = '_sio_optimized';
	const META_SAVED = '_sio_bytes_saved';
	const TOTAL_OPT  = 'sio_total_bytes_saved';

	/** @var Simple_Image_Optimizer|null */
	private static $instance = null;

	/**
	 * Bytes saved for files optimized during the current request, keyed by
	 * normalized file path, so the attachment-metadata hook can attach the
	 * numbers to the attachment record once it exists.
	 *
	 * @var array<string,int>
	 */
	private $pending = array();

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		// Upload pipeline. ('wp_handle_upload' fires for sideloads too,
		// with $context = 'sideload'.)
		add_filter( 'wp_handle_upload', array( $this, 'handle_upload' ), 10, 2 );
		add_filter( 'wp_generate_attachment_metadata', array( $this, 'mark_attachment' ), 20, 2 );

		// Core image-pipeline tweaks.
		add_filter( 'wp_editor_set_quality', array( $this, 'filter_quality' ), 10, 2 );
		add_filter( 'jpeg_quality', array( $this, 'filter_quality' ) );
		add_filter( 'big_image_size_threshold', array( $this, 'filter_big_image_threshold' ) );
		add_filter( 'image_editor_output_format', array( $this, 'filter_output_format' ), 10, 3 );

		// Admin.
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'wp_ajax_sio_bulk_step', array( $this, 'ajax_bulk_step' ) );
		add_filter( 'plugin_action_links_' . plugin_basename( BROOKS_ESS_FILE ), array( $this, 'action_links' ) );
	}

	/* --------------------------------------------------------------------
	 * Options
	 * ------------------------------------------------------------------ */

	public static function defaults() {
		return array(
			'quality'       => 82,   // JPEG/WebP quality.
			'max_dimension' => 2560, // Longest edge for the "-scaled" original. 0 = never scale.
			'webp'          => 0,    // Generate thumbnails as WebP.
			'strip_meta'    => 1,    // Strip EXIF/GPS (color profile is preserved).
		);
	}

	private function opt( $key ) {
		$opts = wp_parse_args( (array) get_option( self::OPTION, array() ), self::defaults() );
		return isset( $opts[ $key ] ) ? $opts[ $key ] : null;
	}

	private function quality() {
		return min( 100, max( 40, (int) $this->opt( 'quality' ) ) );
	}

	/* --------------------------------------------------------------------
	 * Core pipeline filters
	 * ------------------------------------------------------------------ */

	/**
	 * Quality used by WordPress when generating thumbnails / scaled copies.
	 */
	public function filter_quality( $quality, $mime_type = '' ) {
		return $this->quality();
	}

	/**
	 * Longest edge allowed for the original before WordPress creates a
	 * "-scaled" copy to serve as the full-size image.
	 */
	public function filter_big_image_threshold( $threshold ) {
		$max = (int) $this->opt( 'max_dimension' );
		if ( $max <= 0 ) {
			return false; // Never scale.
		}
		return $max;
	}

	/**
	 * When WebP is enabled, have WordPress generate all sub-sizes
	 * (thumbnails + the scaled full-size) as WebP instead of JPEG.
	 * The uploaded original stays on disk as a JPEG master.
	 */
	public function filter_output_format( $formats, $filename = null, $mime_type = null ) {
		if ( $this->opt( 'webp' ) && self::webp_supported() ) {
			$formats['image/jpeg'] = 'image/webp';
		}
		return $formats;
	}

	public static function webp_supported() {
		return wp_image_editor_supports( array( 'mime_type' => 'image/webp' ) );
	}

	/* --------------------------------------------------------------------
	 * Optimize on upload
	 * ------------------------------------------------------------------ */

	/**
	 * Runs right after the file lands in uploads/, before any thumbnails are
	 * generated — so every sub-size is derived from the optimized original.
	 *
	 * @param array  $upload  { file, url, type }
	 * @param string $context 'upload' or 'sideload'.
	 * @return array
	 */
	public function handle_upload( $upload, $context = 'upload' ) {
		if ( ! is_array( $upload ) || empty( $upload['file'] ) || empty( $upload['type'] ) ) {
			return $upload;
		}
		if ( ! in_array( $upload['type'], array( 'image/jpeg', 'image/png' ), true ) ) {
			return $upload;
		}
		if ( ! file_exists( $upload['file'] ) ) {
			return $upload;
		}

		wp_raise_memory_limit( 'image' );

		$saved = $this->optimize_file( $upload['file'], $upload['type'] );
		if ( $saved > 0 ) {
			$this->pending[ wp_normalize_path( $upload['file'] ) ] = $saved;
		}

		return $upload;
	}

	/**
	 * Once the attachment exists, flag it as optimized and record savings.
	 */
	public function mark_attachment( $metadata, $attachment_id ) {
		$mime = get_post_mime_type( $attachment_id );
		if ( ! in_array( $mime, array( 'image/jpeg', 'image/png' ), true ) ) {
			return $metadata;
		}

		update_post_meta( $attachment_id, self::META_FLAG, time() );

		if ( ! empty( $this->pending ) ) {
			$candidates = array();
			$file       = get_attached_file( $attachment_id );
			if ( $file ) {
				$candidates[] = wp_normalize_path( $file );
			}
			if ( function_exists( 'wp_get_original_image_path' ) ) {
				$orig = wp_get_original_image_path( $attachment_id );
				if ( $orig ) {
					$candidates[] = wp_normalize_path( $orig );
				}
			}
			foreach ( $candidates as $path ) {
				if ( isset( $this->pending[ $path ] ) ) {
					$saved = (int) $this->pending[ $path ];
					unset( $this->pending[ $path ] );
					if ( $saved > 0 ) {
						$prev = (int) get_post_meta( $attachment_id, self::META_SAVED, true );
						update_post_meta( $attachment_id, self::META_SAVED, $prev + $saved );
						$this->bump_total( $saved );
					}
					break;
				}
			}
		}

		return $metadata;
	}

	private function bump_total( $bytes ) {
		$total = (int) get_option( self::TOTAL_OPT, 0 );
		update_option( self::TOTAL_OPT, $total + (int) $bytes, false );
	}

	/* --------------------------------------------------------------------
	 * The optimizer
	 * ------------------------------------------------------------------ */

	/**
	 * Recompress a JPEG or PNG in place. The rewritten file only replaces
	 * the original if it is actually smaller.
	 *
	 * @return int Bytes saved (0 if nothing changed).
	 */
	public function optimize_file( $path, $mime ) {
		clearstatcache( true, $path );
		$before = (int) @filesize( $path );
		if ( $before <= 0 || ! is_writable( $path ) ) {
			return 0;
		}

		$tmp   = $path . '.sio.tmp';
		$strip = (bool) $this->opt( 'strip_meta' );
		$q     = $this->quality();

		if ( extension_loaded( 'imagick' ) && class_exists( 'Imagick' ) ) {
			$wrote = $this->imagick_optimize( $path, $mime, $q, $strip, $tmp );
		} elseif ( extension_loaded( 'gd' ) ) {
			$wrote = $this->gd_optimize( $path, $mime, $q, $tmp );
		} else {
			return 0;
		}

		if ( ! $wrote || ! file_exists( $tmp ) ) {
			if ( file_exists( $tmp ) ) {
				@unlink( $tmp );
			}
			return 0;
		}

		clearstatcache( true, $tmp );
		$after = (int) @filesize( $tmp );

		if ( $after > 0 && $after < $before ) {
			$perms = @fileperms( $path );
			if ( @rename( $tmp, $path ) ) {
				if ( $perms ) {
					@chmod( $path, $perms & 0777 );
				}
				return $before - $after;
			}
		}

		@unlink( $tmp );
		return 0;
	}

	/**
	 * Imagick path: auto-rotate, strip metadata (keeping the ICC color
	 * profile), progressive JPEG with 4:2:0 chroma subsampling, max PNG
	 * deflate.
	 */
	private function imagick_optimize( $path, $mime, $q, $strip, $tmp ) {
		try {
			$img = new Imagick( $path );

			if ( 'image/jpeg' === $mime ) {
				$this->imagick_auto_orient( $img );
			}

			if ( $strip ) {
				$icc = null;
				try {
					$profiles = $img->getImageProfiles( 'icc', true );
					if ( ! empty( $profiles['icc'] ) ) {
						$icc = $profiles['icc'];
					}
				} catch ( Exception $e ) {
					$icc = null;
				}
				$img->stripImage();
				if ( $icc ) {
					$img->profileImage( 'icc', $icc );
				}
			}

			if ( 'image/jpeg' === $mime ) {
				$img->setImageCompressionQuality( $q );
				$img->setInterlaceScheme( Imagick::INTERLACE_PLANE );
				if ( method_exists( $img, 'setSamplingFactors' ) ) {
					$img->setSamplingFactors( array( '2x2', '1x1', '1x1' ) );
				}
			} else { // PNG: lossless; 9 = max deflate, 5 = adaptive filtering.
				$img->setImageCompressionQuality( 95 );
			}

			$ok = $img->writeImage( $tmp );
			$img->clear();

			return (bool) $ok;
		} catch ( Exception $e ) {
			return false;
		}
	}

	private function imagick_auto_orient( $img ) {
		if ( ! method_exists( $img, 'getImageOrientation' ) ) {
			return;
		}
		try {
			$orientation = $img->getImageOrientation();
			$bg          = new ImagickPixel( '#000000' );

			switch ( $orientation ) {
				case Imagick::ORIENTATION_TOPRIGHT: // 2
					$img->flopImage();
					break;
				case Imagick::ORIENTATION_BOTTOMRIGHT: // 3
					$img->rotateImage( $bg, 180 );
					break;
				case Imagick::ORIENTATION_BOTTOMLEFT: // 4
					$img->flipImage();
					break;
				case Imagick::ORIENTATION_LEFTTOP: // 5
					$img->rotateImage( $bg, 90 );
					$img->flopImage();
					break;
				case Imagick::ORIENTATION_RIGHTTOP: // 6
					$img->rotateImage( $bg, 90 );
					break;
				case Imagick::ORIENTATION_RIGHTBOTTOM: // 7
					$img->rotateImage( $bg, -90 );
					$img->flopImage();
					break;
				case Imagick::ORIENTATION_LEFTBOTTOM: // 8
					$img->rotateImage( $bg, -90 );
					break;
				default:
					return;
			}
			$img->setImageOrientation( Imagick::ORIENTATION_TOPLEFT );
		} catch ( Exception $e ) {
			// Leave the image as-is.
		}
	}

	/**
	 * GD fallback: auto-rotate via EXIF, progressive JPEG. GD always drops
	 * metadata on save (including the color profile), so "strip metadata"
	 * effectively happens regardless of the setting here.
	 */
	private function gd_optimize( $path, $mime, $q, $tmp ) {
		if ( 'image/jpeg' === $mime ) {
			if ( ! function_exists( 'imagecreatefromjpeg' ) ) {
				return false;
			}
			$im = @imagecreatefromjpeg( $path );
			if ( ! $im ) {
				return false;
			}

			$orientation = 0;
			if ( function_exists( 'exif_read_data' ) ) {
				$exif = @exif_read_data( $path );
				if ( is_array( $exif ) && ! empty( $exif['Orientation'] ) ) {
					$orientation = (int) $exif['Orientation'];
				}
			}
			switch ( $orientation ) {
				case 2:
					imageflip( $im, IMG_FLIP_HORIZONTAL );
					break;
				case 3:
					$rot = imagerotate( $im, 180, 0 );
					if ( $rot ) { imagedestroy( $im ); $im = $rot; }
					break;
				case 4:
					imageflip( $im, IMG_FLIP_VERTICAL );
					break;
				case 5:
					$rot = imagerotate( $im, -90, 0 );
					if ( $rot ) { imagedestroy( $im ); $im = $rot; }
					imageflip( $im, IMG_FLIP_HORIZONTAL );
					break;
				case 6:
					$rot = imagerotate( $im, -90, 0 );
					if ( $rot ) { imagedestroy( $im ); $im = $rot; }
					break;
				case 7:
					$rot = imagerotate( $im, 90, 0 );
					if ( $rot ) { imagedestroy( $im ); $im = $rot; }
					imageflip( $im, IMG_FLIP_HORIZONTAL );
					break;
				case 8:
					$rot = imagerotate( $im, 90, 0 );
					if ( $rot ) { imagedestroy( $im ); $im = $rot; }
					break;
			}

			imageinterlace( $im, true ); // Progressive JPEG.
			$ok = imagejpeg( $im, $tmp, $q );
			imagedestroy( $im );
			return (bool) $ok;
		}

		if ( 'image/png' === $mime ) {
			if ( ! function_exists( 'imagecreatefrompng' ) ) {
				return false;
			}
			$im = @imagecreatefrompng( $path );
			if ( ! $im ) {
				return false;
			}
			if ( imageistruecolor( $im ) ) {
				imagealphablending( $im, false );
				imagesavealpha( $im, true );
			}
			$ok = imagepng( $im, $tmp, 9 ); // Max lossless deflate.
			imagedestroy( $im );
			return (bool) $ok;
		}

		return false;
	}

	/* --------------------------------------------------------------------
	 * Bulk optimizer (for images uploaded while the plugin was inactive)
	 * ------------------------------------------------------------------ */

	private function unoptimized_query_args( $per_page ) {
		return array(
			'post_type'              => 'attachment',
			'post_status'            => 'inherit',
			'post_mime_type'         => array( 'image/jpeg', 'image/png' ),
			'posts_per_page'         => $per_page,
			'fields'                 => 'ids',
			'orderby'                => 'ID',
			'order'                  => 'ASC',
			'no_found_rows'          => false,
			'update_post_term_cache' => false,
			'meta_query'             => array(
				array(
					'key'     => self::META_FLAG,
					'compare' => 'NOT EXISTS',
				),
			),
		);
	}

	public function ajax_bulk_step() {
		check_ajax_referer( 'sio_bulk', 'nonce' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => 'Insufficient permissions.' ), 403 );
		}

		wp_raise_memory_limit( 'image' );
		if ( function_exists( 'set_time_limit' ) ) {
			@set_time_limit( 120 );
		}

		$query            = new WP_Query( $this->unoptimized_query_args( 3 ) );
		$remaining_before = (int) $query->found_posts;
		$processed        = 0;
		$saved_batch      = 0;

		foreach ( $query->posts as $attachment_id ) {
			$saved_batch += $this->optimize_attachment( (int) $attachment_id );
			$processed++;
		}

		$remaining = max( 0, $remaining_before - $processed );

		wp_send_json_success(
			array(
				'processed' => $processed,
				'remaining' => $remaining,
				'saved'     => $saved_batch,
				'saved_h'   => size_format( max( 0, $saved_batch ) ),
				'total_h'   => size_format( (int) get_option( self::TOTAL_OPT, 0 ) ),
				'done'      => ( 0 === $remaining || 0 === $processed ),
			)
		);
	}

	/**
	 * Recompress an existing attachment in place: the true original, the
	 * "-scaled" copy if present, and every generated sub-size. Filenames
	 * and URLs never change.
	 */
	private function optimize_attachment( $attachment_id ) {
		$mime = get_post_mime_type( $attachment_id );
		$file = get_attached_file( $attachment_id );
		$meta = wp_get_attachment_metadata( $attachment_id );
		$dir  = $file ? trailingslashit( dirname( $file ) ) : '';

		$paths = array();
		if ( $file ) {
			$paths[ wp_normalize_path( $file ) ] = $mime;
		}
		if ( function_exists( 'wp_get_original_image_path' ) ) {
			$orig = wp_get_original_image_path( $attachment_id );
			if ( $orig ) {
				$paths[ wp_normalize_path( $orig ) ] = $mime;
			}
		}
		if ( $dir && ! empty( $meta['sizes'] ) && is_array( $meta['sizes'] ) ) {
			foreach ( $meta['sizes'] as $size ) {
				if ( ! empty( $size['file'] ) ) {
					$size_mime = ! empty( $size['mime-type'] ) ? $size['mime-type'] : $mime;
					$paths[ wp_normalize_path( $dir . $size['file'] ) ] = $size_mime;
				}
			}
		}

		$saved = 0;
		foreach ( $paths as $path => $path_mime ) {
			if ( $path && file_exists( $path ) && in_array( $path_mime, array( 'image/jpeg', 'image/png' ), true ) ) {
				$saved += (int) $this->optimize_file( $path, $path_mime );
			}
		}

		// Keep the file sizes WordPress stores (6.0+) accurate.
		if ( is_array( $meta ) && $saved > 0 ) {
			$changed = false;
			if ( isset( $meta['filesize'] ) && $file && file_exists( $file ) ) {
				clearstatcache( true, $file );
				$meta['filesize'] = (int) filesize( $file );
				$changed          = true;
			}
			if ( $dir && ! empty( $meta['sizes'] ) && is_array( $meta['sizes'] ) ) {
				foreach ( $meta['sizes'] as $name => $size ) {
					if ( isset( $size['filesize'] ) && ! empty( $size['file'] ) && file_exists( $dir . $size['file'] ) ) {
						clearstatcache( true, $dir . $size['file'] );
						$meta['sizes'][ $name ]['filesize'] = (int) filesize( $dir . $size['file'] );
						$changed                            = true;
					}
				}
			}
			if ( $changed ) {
				wp_update_attachment_metadata( $attachment_id, $meta );
			}
		}

		update_post_meta( $attachment_id, self::META_FLAG, time() );
		if ( $saved > 0 ) {
			$prev = (int) get_post_meta( $attachment_id, self::META_SAVED, true );
			update_post_meta( $attachment_id, self::META_SAVED, $prev + $saved );
			$this->bump_total( $saved );
		}

		return $saved;
	}

	/* --------------------------------------------------------------------
	 * Admin
	 * ------------------------------------------------------------------ */

	public function action_links( $links ) {
		$url = admin_url( 'options-general.php?page=sio-settings' );
		array_unshift( $links, '<a href="' . esc_url( $url ) . '">Settings</a>' );
		return $links;
	}

	public function admin_menu() {
		add_options_page(
			'Image Optimizer',
			'Image Optimizer',
			'manage_options',
			'sio-settings',
			array( $this, 'render_page' )
		);
	}

	public function register_settings() {
		register_setting(
			'sio_group',
			self::OPTION,
			array(
				'type'              => 'array',
				'sanitize_callback' => array( $this, 'sanitize' ),
				'default'           => self::defaults(),
			)
		);
	}

	public function sanitize( $input ) {
		$out             = self::defaults();
		$input           = (array) $input;
		$out['quality']  = isset( $input['quality'] ) ? min( 100, max( 40, (int) $input['quality'] ) ) : $out['quality'];
		$out['max_dimension'] = isset( $input['max_dimension'] ) ? min( 10000, max( 0, (int) $input['max_dimension'] ) ) : $out['max_dimension'];
		$out['webp']       = empty( $input['webp'] ) ? 0 : 1;
		$out['strip_meta'] = empty( $input['strip_meta'] ) ? 0 : 1;
		return $out;
	}

	public function render_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$quality  = $this->quality();
		$max_dim  = (int) $this->opt( 'max_dimension' );
		$webp     = (int) $this->opt( 'webp' );
		$strip    = (int) $this->opt( 'strip_meta' );
		$webp_ok  = self::webp_supported();
		$total    = (int) get_option( self::TOTAL_OPT, 0 );

		$count_query = new WP_Query( $this->unoptimized_query_args( 1 ) );
		$remaining   = (int) $count_query->found_posts;

		$engine = 'none';
		if ( extension_loaded( 'imagick' ) && class_exists( 'Imagick' ) ) {
			$engine = 'Imagick';
		} elseif ( extension_loaded( 'gd' ) ) {
			$engine = 'GD';
		}
		?>
		<div class="wrap">
			<h1>Image Optimizer</h1>
			<p>Runs only at the moment an image is uploaded — no background tasks, no external services. Safe to leave active permanently.</p>
			<p>
				<strong>Image engine:</strong> <?php echo esc_html( $engine ); ?> &nbsp;|&nbsp;
				<strong>Total saved so far:</strong> <?php echo esc_html( size_format( $total ) ); ?>
			</p>
			<?php if ( 'none' === $engine ) : ?>
				<div class="notice notice-error"><p>Neither the Imagick nor GD PHP extension is available, so images cannot be optimized. Ask your host to enable one of them.</p></div>
			<?php endif; ?>

			<form method="post" action="options.php">
				<?php settings_fields( 'sio_group' ); ?>
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row"><label for="sio-quality">JPEG quality</label></th>
						<td>
							<input name="<?php echo esc_attr( self::OPTION ); ?>[quality]" type="number" id="sio-quality" value="<?php echo esc_attr( $quality ); ?>" min="40" max="100" class="small-text" />
							<p class="description">82 is the sweet spot for photos on the web — visually clean, much smaller files. Applies to the uploaded original and all thumbnails.</p>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="sio-maxdim">Max image dimension</label></th>
						<td>
							<input name="<?php echo esc_attr( self::OPTION ); ?>[max_dimension]" type="number" id="sio-maxdim" value="<?php echo esc_attr( $max_dim ); ?>" min="0" max="10000" step="10" class="small-text" /> px
							<p class="description">Uploads with a longer edge than this get a scaled-down copy that WordPress serves as the full-size image (the original is kept on disk). Set 0 to never scale. Default 2560.</p>
						</td>
					</tr>
					<tr>
						<th scope="row">WebP</th>
						<td>
							<label for="sio-webp">
								<input name="<?php echo esc_attr( self::OPTION ); ?>[webp]" type="checkbox" id="sio-webp" value="1" <?php checked( $webp, 1 ); ?> <?php disabled( ! $webp_ok ); ?> />
								Generate thumbnails as WebP instead of JPEG
							</label>
							<?php if ( ! $webp_ok ) : ?>
								<p class="description">Your server&#8217;s image library does not support WebP, so this option is unavailable.</p>
							<?php else : ?>
								<p class="description">Applies to newly uploaded JPEGs (the original stays on disk as a JPEG master). To convert existing images, run the free &#8220;Regenerate Thumbnails&#8221; plugin once after enabling this.</p>
							<?php endif; ?>
						</td>
					</tr>
					<tr>
						<th scope="row">Metadata</th>
						<td>
							<label for="sio-strip">
								<input name="<?php echo esc_attr( self::OPTION ); ?>[strip_meta]" type="checkbox" id="sio-strip" value="1" <?php checked( $strip, 1 ); ?> />
								Strip EXIF metadata (camera info, GPS location) from uploads
							</label>
							<p class="description">Photos are auto-rotated first so nothing displays sideways. The ICC color profile is preserved so colors don&#8217;t shift. With the GD engine, metadata is always removed regardless of this setting.</p>
						</td>
					</tr>
				</table>
				<?php submit_button(); ?>
			</form>

			<hr />

			<h2>Bulk optimize existing images</h2>
			<p>
				New uploads are handled automatically — this is only for images added while the plugin was inactive.
				Files are recompressed in place; filenames and URLs never change, and a file is only replaced if the result is actually smaller.
			</p>
			<p><strong><?php echo esc_html( number_format_i18n( $remaining ) ); ?></strong> image<?php echo 1 === $remaining ? '' : 's'; ?> not yet processed by this plugin.</p>
			<p>
				<button type="button" class="button button-primary" id="sio-bulk-start" <?php disabled( 0 === $remaining || 'none' === $engine ); ?>>Optimize now</button>
			</p>
			<p id="sio-bulk-status" style="font-weight:600;"></p>
		</div>
		<script>
		window.sioBulk = <?php echo wp_json_encode( array(
			'ajax'  => admin_url( 'admin-ajax.php' ),
			'nonce' => wp_create_nonce( 'sio_bulk' ),
		) ); ?>;
		(function () {
			var btn = document.getElementById('sio-bulk-start');
			if (!btn) { return; }
			var status = document.getElementById('sio-bulk-status');
			var cfg = window.sioBulk || {};
			var totalProcessed = 0;

			function step() {
				var body = new URLSearchParams();
				body.append('action', 'sio_bulk_step');
				body.append('nonce', cfg.nonce);
				fetch(cfg.ajax, { method: 'POST', credentials: 'same-origin', body: body })
					.then(function (r) { return r.json(); })
					.then(function (res) {
						if (!res || !res.success) {
							status.textContent = 'Error — check the browser console.';
							btn.disabled = false;
							return;
						}
						var d = res.data;
						totalProcessed += d.processed;
						if (d.done) {
							status.textContent = 'Done. Optimized ' + totalProcessed + ' image' + (totalProcessed === 1 ? '' : 's') + ' this run. Total saved to date: ' + d.total_h + '.';
							btn.disabled = false;
						} else {
							status.textContent = 'Optimized ' + totalProcessed + ' so far — ' + d.remaining + ' remaining. Total saved to date: ' + d.total_h + '.';
							step();
						}
					})
					.catch(function () {
						status.textContent = 'Request failed — try again.';
						btn.disabled = false;
					});
			}

			btn.addEventListener('click', function () {
				btn.disabled = true;
				status.textContent = 'Starting\u2026';
				totalProcessed = 0;
				step();
			});
		})();
		</script>
		<?php
	}

	/* --------------------------------------------------------------------
	 * Lifecycle
	 * ------------------------------------------------------------------ */

	public static function activate() {
		add_option( self::OPTION, self::defaults() );
		add_option( self::TOTAL_OPT, 0, '', false );
	}

	public static function uninstall() {
		delete_option( self::OPTION );
		delete_option( self::TOTAL_OPT );
		delete_post_meta_by_key( self::META_FLAG );
		delete_post_meta_by_key( self::META_SAVED );
	}
}

register_activation_hook( __FILE__, array( 'Simple_Image_Optimizer', 'activate' ) );
register_uninstall_hook( __FILE__, array( 'Simple_Image_Optimizer', 'uninstall' ) );

Simple_Image_Optimizer::instance();

endif; // class_exists guard.
