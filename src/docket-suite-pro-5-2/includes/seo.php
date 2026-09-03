<?php
/**
 * Docket Suite — SEO module.
 *
 * The former standalone Docket SEO plugin, as a module. Per-page titles &
 * meta, canonical, OG/Twitter, XML sitemap, attachment 301s, author noindex.
 *
 * IMPORTANT: this module STANDS DOWN when Yoast/Rank Math/AIOSEO is active
 * (duplicate-tag safety). The rest of the suite (redirects, 404 log,
 * llms.txt, image optimizer, SPC watchdog, hardening) runs regardless — it
 * is wired separately in the main plugin file, so those never sleep.
 *
 * @package Docket_Suite
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Docket_SEO {

	const OPTION     = 'docket_seo_options';
	const META_TITLE = '_docket_seo_title';
	const META_DESC  = '_docket_seo_desc';
	const META_NOIDX = '_docket_seo_noindex';

	/** @var Docket_SEO|null */
	private static $instance = null;

	/** @var bool True when another SEO plugin owns the front end. */
	private $standing_down = false;

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		add_action( 'plugins_loaded', array( $this, 'boot' ), 20 );

		// Editing UI + settings load regardless, so data entry works even
		// while another SEO plugin is active (e.g. pre-migration).
		add_action( 'add_meta_boxes', array( $this, 'add_meta_box' ) );
		add_action( 'save_post', array( $this, 'save_meta_box' ) );
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'action_links' ) );
	}

	/**
	 * Front-end hooks. If Yoast (or Rank Math / AIOSEO) is active, stand
	 * down completely to avoid duplicate tags and show an admin notice.
	 */
	public function boot() {
		$this->standing_down = ( defined( 'WPSEO_VERSION' ) || defined( 'RANK_MATH_VERSION' ) || defined( 'AIOSEO_VERSION' ) );

		if ( $this->standing_down ) {
			add_action( 'admin_notices', array( $this, 'standing_down_notice' ) );
			// If a stale /sitemap.xml rewrite survives from an earlier
			// activation, answer it with a real 404 rather than letting
			// WordPress soft-serve the homepage at that URL.
			add_filter( 'query_vars', array( $this, 'add_query_var' ) );
			add_action( 'template_redirect', array( $this, 'maybe_render_sitemap' ), 0 );
			return;
		}

		// Flag consumed by crawlers.php so robots.txt advertises the right
		// sitemap URL the moment this half of the Suite goes live.
		if ( ! defined( 'DOCKET_SUITE_SEO_ACTIVE' ) ) {
			define( 'DOCKET_SUITE_SEO_ACTIVE', true );
		}

		// Titles.
		add_filter( 'pre_get_document_title', array( $this, 'filter_title' ), 15 );
		add_filter( 'document_title_separator', array( $this, 'filter_separator' ) );

		// Head tags. Core's canonical is replaced with ours.
		remove_action( 'wp_head', 'rel_canonical' );
		add_action( 'wp_head', array( $this, 'output_head' ), 1 );

		// Robots.
		add_filter( 'wp_robots', array( $this, 'filter_robots' ) );

		// Attachment pages redirect to their parent (or the file itself).
		add_action( 'template_redirect', array( $this, 'attachment_redirect' ), 1 );

		// Sitemap.
		if ( $this->opt( 'sitemap' ) ) {
			add_filter( 'wp_sitemaps_enabled', '__return_false' ); // No duplicate core sitemap.
			add_action( 'init', array( $this, 'add_sitemap_rewrite' ) );
			add_filter( 'query_vars', array( $this, 'add_query_var' ) );
			add_action( 'template_redirect', array( $this, 'legacy_sitemap_redirects' ), 0 );
			add_action( 'template_redirect', array( $this, 'maybe_render_sitemap' ), 0 );
			// NOTE (Suite Pro 5): the robots_txt filter that used to live here
			// is intentionally removed. crawlers.php writes and heals a
			// physical robots.txt at priority 99 and emits its own
			// "Sitemap:" line, so registering a second writer here produced
			// duplicate/conflicting Sitemap directives. crawlers.php is the
			// single robots.txt authority; it detects the flag defined below
			// and points at /sitemap.xml automatically.
		}
	}

	public function standing_down_notice() {
		echo '<div class="notice notice-warning"><p><strong>Docket SEO</strong> is standing down because another SEO plugin is active, to avoid duplicate titles and meta tags. Deactivate the other SEO plugin to let Docket SEO take over. Your Docket SEO fields are still editable and saved in the meantime.</p></div>';
	}

	/* --------------------------------------------------------------------
	 * Options
	 * ------------------------------------------------------------------ */

	public static function defaults() {
		return array(
			'separator'       => '|',
			'home_desc'       => '',
			'default_image'   => '',
			'sitemap'         => 1,
			'sitemap_categories' => 1,
			'indexnow'        => 0,
			'noindex_author'  => 1,
			'attachment_301'  => 1,
		);
	}

	private function opt( $key ) {
		$opts = wp_parse_args( (array) get_option( self::OPTION, array() ), self::defaults() );
		return isset( $opts[ $key ] ) ? $opts[ $key ] : null;
	}

	/* --------------------------------------------------------------------
	 * Per-post data (with silent Yoast fallback for drop-in takeovers)
	 * ------------------------------------------------------------------ */

	/**
	 * Custom SEO title for a post: Docket field first, then a literal
	 * Yoast title if one exists (skipping Yoast %%template%% values).
	 */
	public function seo_title_for( $post_id ) {
		$title = trim( (string) get_post_meta( $post_id, self::META_TITLE, true ) );
		if ( '' === $title ) {
			$yoast = trim( (string) get_post_meta( $post_id, '_yoast_wpseo_title', true ) );
			if ( '' !== $yoast && false === strpos( $yoast, '%%' ) ) {
				$title = $yoast;
			}
		}
		return $title;
	}

	/**
	 * Meta description for a post: Docket field, literal Yoast field,
	 * manual excerpt, then an automatic trim of the content — so every
	 * page ships with a description even if the client fills nothing.
	 */
	public function seo_desc_for( $post_id ) {
		$desc = trim( (string) get_post_meta( $post_id, self::META_DESC, true ) );
		if ( '' === $desc ) {
			$yoast = trim( (string) get_post_meta( $post_id, '_yoast_wpseo_metadesc', true ) );
			if ( '' !== $yoast && false === strpos( $yoast, '%%' ) ) {
				$desc = $yoast;
			}
		}
		if ( '' === $desc ) {
			$post = get_post( $post_id );
			if ( $post ) {
				if ( '' !== trim( (string) $post->post_excerpt ) ) {
					$desc = self::trim_desc( $post->post_excerpt );
				} else {
					$desc = self::trim_desc( $post->post_content );
				}
			}
		}
		return $desc;
	}

	public function is_noindexed( $post_id ) {
		if ( (bool) get_post_meta( $post_id, self::META_NOIDX, true ) ) {
			return true;
		}
		// Migration fallback: honor a noindex set under Yoast ('1' = noindex).
		return '1' === (string) get_post_meta( $post_id, '_yoast_wpseo_meta-robots-noindex', true );
	}

	/**
	 * Reduce arbitrary post content to a clean single-line description.
	 * Pure helper (testable): strips tags/shortcodes, collapses
	 * whitespace, cuts at a word boundary.
	 */
	public static function trim_desc( $text, $max = 160 ) {
		$text = strip_shortcodes( (string) $text );
		$text = str_replace( '<', ' <', $text ); // Keep words apart when block tags are stripped.
		$text = wp_strip_all_tags( $text );
		$text = preg_replace( '/\s+/u', ' ', $text );
		$text = trim( (string) $text );
		if ( function_exists( 'mb_strlen' ) ? mb_strlen( $text ) > $max : strlen( $text ) > $max ) {
			$cut  = function_exists( 'mb_substr' ) ? mb_substr( $text, 0, $max ) : substr( $text, 0, $max );
			$pos  = function_exists( 'mb_strrpos' ) ? mb_strrpos( $cut, ' ' ) : strrpos( $cut, ' ' );
			$text = ( false !== $pos && $pos > 40 ) ? ( function_exists( 'mb_substr' ) ? mb_substr( $cut, 0, $pos ) : substr( $cut, 0, $pos ) ) : $cut;
			$text = rtrim( $text, " \t.,;:!-&" ) . '&hellip;';
			$text = str_replace( '&hellip;', "\u{2026}", $text );
		}
		return $text;
	}

	/* --------------------------------------------------------------------
	 * Titles
	 * ------------------------------------------------------------------ */

	public function filter_title( $title ) {
		if ( is_singular() ) {
			$custom = $this->seo_title_for( get_queried_object_id() );
			if ( '' !== $custom ) {
				return wp_strip_all_tags( $custom );
			}
		}
		return $title; // Empty string lets WordPress build its default.
	}

	public function filter_separator( $sep ) {
		$s = trim( (string) $this->opt( 'separator' ) );
		return '' !== $s ? $s : $sep;
	}

	/* --------------------------------------------------------------------
	 * Head output: description, canonical, Open Graph, Twitter
	 * ------------------------------------------------------------------ */

	public function output_head() {
		if ( is_feed() || is_404() || is_search() ) {
			return;
		}

		$desc      = '';
		$canonical = '';
		$og_type   = 'website';
		$image     = trim( (string) $this->opt( 'default_image' ) );

		if ( is_front_page() ) {
			$desc = trim( (string) $this->opt( 'home_desc' ) );
			if ( '' === $desc && is_singular() ) {
				$desc = $this->seo_desc_for( get_queried_object_id() );
			}
			$canonical = home_url( '/' );
		} elseif ( is_singular() ) {
			$post_id   = get_queried_object_id();
			$desc      = $this->seo_desc_for( $post_id );
			$canonical = wp_get_canonical_url( $post_id );
			if ( is_singular( 'post' ) ) {
				$og_type = 'article';
			}
			$thumb = get_the_post_thumbnail_url( $post_id, 'full' );
			if ( $thumb ) {
				$image = $thumb;
			}
		} elseif ( is_home() ) {
			$page_for_posts = (int) get_option( 'page_for_posts' );
			if ( $page_for_posts ) {
				$desc      = $this->seo_desc_for( $page_for_posts );
				$canonical = get_permalink( $page_for_posts );
			}
		}

		if ( '' !== $desc ) {
			echo '<meta name="description" content="' . esc_attr( $desc ) . '" />' . "\n";
		}
		if ( $canonical ) {
			echo '<link rel="canonical" href="' . esc_url( $canonical ) . '" />' . "\n";
		}

		// Social tags.
		$og_title = wp_get_document_title();
		echo '<meta property="og:type" content="' . esc_attr( $og_type ) . '" />' . "\n";
		echo '<meta property="og:title" content="' . esc_attr( $og_title ) . '" />' . "\n";
		if ( '' !== $desc ) {
			echo '<meta property="og:description" content="' . esc_attr( $desc ) . '" />' . "\n";
		}
		if ( $canonical ) {
			echo '<meta property="og:url" content="' . esc_url( $canonical ) . '" />' . "\n";
		}
		echo '<meta property="og:site_name" content="' . esc_attr( get_bloginfo( 'name' ) ) . '" />' . "\n";
		if ( '' !== $image ) {
			echo '<meta property="og:image" content="' . esc_url( $image ) . '" />' . "\n";
			echo '<meta name="twitter:card" content="summary_large_image" />' . "\n";
		} else {
			echo '<meta name="twitter:card" content="summary" />' . "\n";
		}
		echo '<meta name="twitter:title" content="' . esc_attr( $og_title ) . '" />' . "\n";
		if ( '' !== $desc ) {
			echo '<meta name="twitter:description" content="' . esc_attr( $desc ) . '" />' . "\n";
		}
		if ( '' !== $image ) {
			echo '<meta name="twitter:image" content="' . esc_url( $image ) . '" />' . "\n";
		}
		if ( 'article' === $og_type ) {
			$modified = get_post_modified_time( 'c', true, get_queried_object_id() );
			if ( $modified ) {
				echo '<meta property="article:modified_time" content="' . esc_attr( $modified ) . '" />' . "\n";
			}
		}
	}

	/* --------------------------------------------------------------------
	 * Robots
	 * ------------------------------------------------------------------ */

	public function filter_robots( $robots ) {
		$noindex = false;

		if ( is_singular() && $this->is_noindexed( get_queried_object_id() ) ) {
			$noindex = true;
		}
		if ( is_author() && $this->opt( 'noindex_author' ) ) {
			$noindex = true;
		}

		if ( $noindex ) {
			$robots['noindex'] = true;
			$robots['follow']  = true;
			unset( $robots['max-image-preview'] );
		}
		return $robots;
	}

	/* --------------------------------------------------------------------
	 * Attachment pages
	 * ------------------------------------------------------------------ */

	/**
	 * Attachment pages are thin, compete with real pages, and pollute
	 * sitemaps and slugs. 301 them to the parent page, or the file itself
	 * when orphaned.
	 */
	public function attachment_redirect() {
		if ( ! is_attachment() || ! $this->opt( 'attachment_301' ) ) {
			return;
		}
		$post   = get_queried_object();
		$target = '';
		if ( $post && ! empty( $post->post_parent ) && 'publish' === get_post_status( $post->post_parent ) ) {
			$target = get_permalink( $post->post_parent );
		}
		if ( ! $target && $post ) {
			$target = wp_get_attachment_url( $post->ID );
		}
		if ( $target ) {
			wp_safe_redirect( $target, 301 );
			exit;
		}
	}

	/* --------------------------------------------------------------------
	 * Sitemap
	 * ------------------------------------------------------------------ */

	public function add_sitemap_rewrite() {
		add_rewrite_rule( '^sitemap\.xml$', 'index.php?docket_sitemap=1', 'top' );
	}

	public function add_query_var( $vars ) {
		$vars[] = 'docket_sitemap';
		return $vars;
	}

	public function robots_txt( $output, $public ) {
		if ( $public ) {
			$output .= "\nSitemap: " . esc_url( home_url( '/sitemap.xml' ) ) . "\n";
		}
		return $output;
	}

	/**
	 * When this plugin owns the sitemap, answer legacy SEO-plugin sitemap
	 * URLs (Yoast's sitemap_index.xml, page-sitemap.xml, post-sitemap.xml,
	 * etc.) with a 301 to /sitemap.xml — so sitemaps already submitted in
	 * Search Console keep resolving after a migration instead of erroring.
	 */
	public function legacy_sitemap_redirects() {
		if ( is_admin() ) {
			return;
		}
		$request = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
		$path    = strtolower( (string) wp_parse_url( $request, PHP_URL_PATH ) );
		if ( '' === $path ) {
			return;
		}
		if ( preg_match( '#/(sitemap_index|[a-z0-9_\-]+-sitemap[0-9]*)\.xml$#', $path ) ) {
			wp_safe_redirect( home_url( '/sitemap.xml' ), 301 );
			exit;
		}
	}

	public function maybe_render_sitemap() {
		if ( ! get_query_var( 'docket_sitemap' ) ) {
			return;
		}

		if ( $this->standing_down || ! $this->opt( 'sitemap' ) ) {
			global $wp_query;
			$wp_query->set_404();
			status_header( 404 );
			nocache_headers();
			return;
		}

		$entries = array();

		// Front page first.
		$entries[ home_url( '/' ) ] = array(
			'loc'     => home_url( '/' ),
			'lastmod' => get_lastpostmodified( 'gmt' ),
		);

		$posts = get_posts(
			array(
				'post_type'        => array( 'page', 'post' ),
				'post_status'      => 'publish',
				'numberposts'      => -1,
				'orderby'          => 'ID',
				'order'            => 'ASC',
				'has_password'     => false,
				'suppress_filters' => false,
			)
		);

		foreach ( $posts as $post ) {
			if ( $this->is_noindexed( $post->ID ) ) {
				continue;
			}
			$loc = get_permalink( $post );
			if ( ! $loc ) {
				continue;
			}
			$entries[ $loc ] = array( // Keyed by URL: static front page dedupes itself.
				'loc'     => $loc,
				'lastmod' => $post->post_modified_gmt,
			);
		}

		/*
		 * Category archives. Yoast lists these today and they are indexable
		 * by design on this site, so omitting them would quietly drop live
		 * URLs from the sitemap at cutover. Empty terms are skipped: an
		 * archive with no posts is a thin page, not a destination.
		 */
		if ( $this->opt( 'sitemap_categories' ) ) {
			$terms = get_terms(
				array(
					'taxonomy'   => 'category',
					'hide_empty' => true,
				)
			);
			if ( ! is_wp_error( $terms ) ) {
				foreach ( $terms as $term ) {
					$loc = get_term_link( $term );
					if ( is_wp_error( $loc ) || ! $loc ) {
						continue;
					}
					$recent = get_posts(
						array(
							'post_type'        => 'post',
							'post_status'      => 'publish',
							'numberposts'      => 1,
							'orderby'          => 'modified',
							'order'            => 'DESC',
							'fields'           => 'ids',
							'category'         => $term->term_id,
							'suppress_filters' => false,
						)
					);
					$lastmod = '';
					if ( ! empty( $recent[0] ) ) {
						$lastmod = get_post_field( 'post_modified_gmt', $recent[0] );
					}
					$entries[ $loc ] = array(
						'loc'     => $loc,
						'lastmod' => $lastmod,
					);
				}
			}
		}

		if ( ! headers_sent() ) {
			status_header( 200 );
			nocache_headers(); // Sitemaps must always be live truth — never edge-cached.
			header( 'Content-Type: application/xml; charset=UTF-8' );
			header( 'X-Robots-Tag: noindex' ); // The sitemap itself shouldn't rank.
		}
		echo self::sitemap_xml( array_values( $entries ) ); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped in sitemap_xml().
		exit;
	}

	/**
	 * Render sitemap XML from entries. Pure helper (testable).
	 *
	 * @param array $entries Each: ['loc' => url, 'lastmod' => 'Y-m-d H:i:s' GMT or ''].
	 */
	public static function sitemap_xml( array $entries ) {
		$xml  = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
		$xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
		foreach ( $entries as $entry ) {
			if ( empty( $entry['loc'] ) ) {
				continue;
			}
			$xml .= "\t<url>\n";
			$xml .= "\t\t<loc>" . htmlspecialchars( $entry['loc'], ENT_QUOTES, 'UTF-8' ) . "</loc>\n";
			if ( ! empty( $entry['lastmod'] ) ) {
				$ts = strtotime( $entry['lastmod'] . ' UTC' );
				if ( ! $ts ) {
					$ts = strtotime( $entry['lastmod'] );
				}
				if ( $ts ) {
					$xml .= "\t\t<lastmod>" . gmdate( 'Y-m-d\TH:i:sP', $ts ) . "</lastmod>\n";
				}
			}
			$xml .= "\t</url>\n";
		}
		$xml .= '</urlset>' . "\n";
		return $xml;
	}

	/* --------------------------------------------------------------------
	 * Meta box
	 * ------------------------------------------------------------------ */

	public function add_meta_box() {
		foreach ( array( 'page', 'post' ) as $type ) {
			add_meta_box( 'docket-seo', 'Docket SEO', array( $this, 'render_meta_box' ), $type, 'normal', 'default' );
		}
	}

	public function render_meta_box( $post ) {
		wp_nonce_field( 'docket_seo_save', 'docket_seo_nonce' );
		$title   = get_post_meta( $post->ID, self::META_TITLE, true );
		$desc    = get_post_meta( $post->ID, self::META_DESC, true );
		$noindex = (bool) get_post_meta( $post->ID, self::META_NOIDX, true );
		?>
		<p>
			<label for="docket-seo-title"><strong>SEO title</strong> <span class="description">(shown in Google; leave blank to use the page title)</span></label><br />
			<input type="text" id="docket-seo-title" name="docket_seo_title" value="<?php echo esc_attr( $title ); ?>" class="widefat" maxlength="200" />
			<span class="description"><span id="docket-seo-title-count">0</span>/60 characters recommended</span>
		</p>
		<p>
			<label for="docket-seo-desc"><strong>Meta description</strong> <span class="description">(the snippet under your title in Google)</span></label><br />
			<textarea id="docket-seo-desc" name="docket_seo_desc" class="widefat" rows="3" maxlength="400"><?php echo esc_textarea( $desc ); ?></textarea>
			<span class="description"><span id="docket-seo-desc-count">0</span>/160 characters recommended</span>
		</p>
		<p>
			<label>
				<input type="checkbox" name="docket_seo_noindex" value="1" <?php checked( $noindex ); ?> />
				Hide this page from search engines (noindex — also removes it from the sitemap)
			</label>
		</p>
		<script>
		(function () {
			function bind(inputId, countId, limit) {
				var el = document.getElementById(inputId);
				var out = document.getElementById(countId);
				if (!el || !out) { return; }
				function update() {
					out.textContent = el.value.length;
					out.style.color = el.value.length > limit ? '#d63638' : '';
				}
				el.addEventListener('input', update);
				update();
			}
			bind('docket-seo-title', 'docket-seo-title-count', 60);
			bind('docket-seo-desc', 'docket-seo-desc-count', 160);
		})();
		</script>
		<?php
	}

	public function save_meta_box( $post_id ) {
		if ( ! isset( $_POST['docket_seo_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['docket_seo_nonce'] ), 'docket_seo_save' ) ) {
			return;
		}
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		$title = isset( $_POST['docket_seo_title'] ) ? sanitize_text_field( wp_unslash( $_POST['docket_seo_title'] ) ) : '';
		$desc  = isset( $_POST['docket_seo_desc'] ) ? sanitize_textarea_field( wp_unslash( $_POST['docket_seo_desc'] ) ) : '';

		if ( '' !== $title ) {
			update_post_meta( $post_id, self::META_TITLE, $title );
		} else {
			delete_post_meta( $post_id, self::META_TITLE );
		}
		if ( '' !== $desc ) {
			update_post_meta( $post_id, self::META_DESC, $desc );
		} else {
			delete_post_meta( $post_id, self::META_DESC );
		}
		if ( ! empty( $_POST['docket_seo_noindex'] ) ) {
			update_post_meta( $post_id, self::META_NOIDX, 1 );
		} else {
			delete_post_meta( $post_id, self::META_NOIDX );
		}
	}

	/* --------------------------------------------------------------------
	 * Settings
	 * ------------------------------------------------------------------ */

	public function action_links( $links ) {
		$url = admin_url( 'options-general.php?page=docket-seo' );
		array_unshift( $links, '<a href="' . esc_url( $url ) . '">Settings</a>' );
		return $links;
	}

	public function admin_menu() {
		add_options_page( 'Docket SEO', 'Docket SEO', 'manage_options', 'docket-seo', array( $this, 'render_page' ) );
	}

	public function register_settings() {
		register_setting(
			'docket_seo_group',
			self::OPTION,
			array(
				'type'              => 'array',
				'sanitize_callback' => array( $this, 'sanitize' ),
				'default'           => self::defaults(),
			)
		);
	}

	public function sanitize( $input ) {
		$out   = self::defaults();
		$input = (array) $input;

		$out['separator']      = isset( $input['separator'] ) ? sanitize_text_field( $input['separator'] ) : $out['separator'];
		$out['home_desc']      = isset( $input['home_desc'] ) ? sanitize_textarea_field( $input['home_desc'] ) : '';
		$out['default_image']  = isset( $input['default_image'] ) ? esc_url_raw( trim( (string) $input['default_image'] ) ) : '';
		$out['sitemap']        = empty( $input['sitemap'] ) ? 0 : 1;
		$out['sitemap_categories'] = empty( $input['sitemap_categories'] ) ? 0 : 1;
		$out['indexnow']       = empty( $input['indexnow'] ) ? 0 : 1;
		$out['noindex_author'] = empty( $input['noindex_author'] ) ? 0 : 1;
		$out['attachment_301'] = empty( $input['attachment_301'] ) ? 0 : 1;

		// The sitemap rewrite may have been toggled. Re-register the rule in
		// this same request when enabled (init already ran with the old
		// value), then refresh rules so the change takes effect immediately.
		if ( ! empty( $out['sitemap'] ) && ! $this->standing_down ) {
			add_rewrite_rule( '^sitemap\.xml$', 'index.php?docket_sitemap=1', 'top' );
		}
		flush_rewrite_rules();

		return $out;
	}

	public function render_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		$sep      = (string) $this->opt( 'separator' );
		$homedesc = (string) $this->opt( 'home_desc' );
		$img      = (string) $this->opt( 'default_image' );
		$sitemap  = (int) $this->opt( 'sitemap' );
		$sitecats = (int) $this->opt( 'sitemap_categories' );
		$idxnow   = (int) $this->opt( 'indexnow' );
		$noauthor = (int) $this->opt( 'noindex_author' );
		$att301   = (int) $this->opt( 'attachment_301' );
		?>
		<div class="wrap">
			<h1>Docket SEO</h1>
			<?php if ( isset( $_GET['settings-updated'] ) ) : // phpcs:ignore WordPress.Security.NonceVerification.Recommended ?>
				<div class="notice notice-info"><p><strong>Reminder:</strong> if this site runs a page cache or CDN (Super Page Cache, Cloudflare, etc.), purge it now — visitors and crawlers keep receiving the previously cached tags until you do.</p></div>
			<?php endif; ?>
			<p>Per-page titles and descriptions live in the <strong>Docket SEO</strong> box on each page and post. Site-wide settings are below.</p>
			<?php if ( $this->standing_down ) : ?>
				<div class="notice notice-warning inline"><p>Another SEO plugin is active, so Docket SEO&#8217;s front-end output is currently paused.</p></div>
			<?php elseif ( $sitemap ) : ?>
				<p><strong>Sitemap:</strong> <a href="<?php echo esc_url( home_url( '/sitemap.xml' ) ); ?>" target="_blank" rel="noopener"><?php echo esc_html( home_url( '/sitemap.xml' ) ); ?></a> — submit this once in Google Search Console.</p>
			<?php endif; ?>

			<form method="post" action="options.php">
				<?php settings_fields( 'docket_seo_group' ); ?>
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row"><label for="docket-sep">Title separator</label></th>
						<td>
							<input name="<?php echo esc_attr( self::OPTION ); ?>[separator]" type="text" id="docket-sep" value="<?php echo esc_attr( $sep ); ?>" class="small-text" maxlength="5" />
							<p class="description">Between the page title and the site name, e.g. &#8220;DUI Defense <?php echo esc_html( $sep ); ?> Smith Law&#8221;.</p>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="docket-homedesc">Homepage meta description</label></th>
						<td>
							<textarea name="<?php echo esc_attr( self::OPTION ); ?>[home_desc]" id="docket-homedesc" class="large-text" rows="3" maxlength="400"><?php echo esc_textarea( $homedesc ); ?></textarea>
							<p class="description">Aim for ~155 characters: who the firm is, where, and what they handle.</p>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="docket-img">Default social share image URL</label></th>
						<td>
							<input name="<?php echo esc_attr( self::OPTION ); ?>[default_image]" type="url" id="docket-img" value="<?php echo esc_attr( $img ); ?>" class="large-text" />
							<p class="description">Shown when a page is shared and has no featured image. 1200&#215;630px works best. Paste a URL from the Media Library.</p>
						</td>
					</tr>
					<tr>
						<th scope="row">XML sitemap</th>
						<td>
							<label><input name="<?php echo esc_attr( self::OPTION ); ?>[sitemap]" type="checkbox" value="1" <?php checked( $sitemap, 1 ); ?> /> Serve a sitemap at /sitemap.xml (replaces the WordPress default)</label><br />
							<label><input name="<?php echo esc_attr( self::OPTION ); ?>[sitemap_categories]" type="checkbox" value="1" <?php checked( $sitecats, 1 ); ?> /> Include category archives that have posts</label>
							<p class="description">Leave this on if category archives are indexable. Empty categories are always skipped.</p>
						</td>
					</tr>
					<tr>
						<th scope="row">IndexNow (Bing, Yandex, Seznam, Naver)</th>
						<td>
							<label><input name="<?php echo esc_attr( self::OPTION ); ?>[indexnow]" type="checkbox" value="1" <?php checked( $idxnow, 1 ); ?> /> Notify participating engines the moment a page is published or updated</label>
							<p class="description">
								Replaces the ping Yoast Premium was sending. One submission reaches Bing, Yandex, Seznam and Naver — which matters because Copilot and DuckDuckGo read Bing's index. Google does not participate in IndexNow; Google discovery stays with the sitemap and Search Console.
								Automatically inert while another SEO plugin is active, so it cannot double-submit. Turn it on after Yoast is deactivated.
							</p>
						</td>
					</tr>
					<tr>
						<th scope="row">Clean-up defaults</th>
						<td>
							<label><input name="<?php echo esc_attr( self::OPTION ); ?>[noindex_author]" type="checkbox" value="1" <?php checked( $noauthor, 1 ); ?> /> Keep author archive pages out of search results</label><br />
							<label><input name="<?php echo esc_attr( self::OPTION ); ?>[attachment_301]" type="checkbox" value="1" <?php checked( $att301, 1 ); ?> /> Redirect attachment pages to their parent page (recommended)</label>
							<p class="description">Attachment pages are thin duplicate content and can hijack slugs you want for real pages.</p>
						</td>
					</tr>
				</table>
				<?php submit_button(); ?>
			</form>
			<?php
			// Suite Pro 5: optional one-shot import of Yoast's stored fields.
			if ( function_exists( 'docket_seo_migrate_panel' ) ) {
				docket_seo_migrate_panel();
			}
			?>
		</div>
		<?php
	}

	/* --------------------------------------------------------------------
	 * Lifecycle
	 * ------------------------------------------------------------------ */

	public static function activate() {
		add_option( self::OPTION, self::defaults() );
		// Only claim /sitemap.xml when no other SEO plugin owns the site's
		// sitemaps — activating alongside Yoast must not hijack the URL.
		if ( ! defined( 'WPSEO_VERSION' ) && ! defined( 'RANK_MATH_VERSION' ) && ! defined( 'AIOSEO_VERSION' ) ) {
			add_rewrite_rule( '^sitemap\.xml$', 'index.php?docket_sitemap=1', 'top' );
		}
		flush_rewrite_rules();
	}

	public static function deactivate() {
		flush_rewrite_rules();
	}

	/**
	 * Options are removed on uninstall; per-page titles and descriptions
	 * are intentionally kept — deleting a client's SEO copy on uninstall
	 * would be data loss.
	 */
	public static function uninstall() {
		delete_option( self::OPTION );
	}
}



/**
 * Self-healing rewrite flush for the sitemap endpoint. The suite may be
 * activated without this module's own activation hook firing, so ensure the
 * /sitemap.xml rewrite exists on first admin load, once.
 */
function docket_seo_module_maybe_flush() {
	if ( get_option( 'docket_seo_rw_flushed_v11' ) ) {
		return;
	}
	$inst = Docket_SEO::instance();
	if ( method_exists( $inst, 'add_sitemap_rewrite' ) ) {
		$inst->add_sitemap_rewrite();
	}
	flush_rewrite_rules( false );
	update_option( 'docket_seo_rw_flushed_v11', 1, false );
}
add_action( 'admin_init', 'docket_seo_module_maybe_flush', 5 );

Docket_SEO::instance();
