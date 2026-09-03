<?php
/**
 * Performance layer — page cache, minification, resource hints.
 *
 * Four independent, individually-toggleable features
 * (Customizer → Brooks Law Firm → Performance):
 *
 *   1. PAGE CACHE — full-HTML transient cache for anonymous GET
 *      requests. It hooks `template_redirect`, so WordPress and its
 *      plugins have already booted and the main query has already run
 *      by the time a hit is served; what the cache saves is template
 *      rendering, the theme's own queries, and every filter that would
 *      run during output. That is a real saving and a worthwhile one,
 *      but it is NOT an edge cache and does not claim to be — a CDN or
 *      a drop-in advanced-cache.php is what avoids the bootstrap.
 *
 *      Invalidation is generational: every cache key carries a counter
 *      that content, menu, Customizer, and plugin changes increment, so
 *      a purge is one option write and takes effect on the next request
 *      with no scan and no object-cache flush. Superseded rows are then
 *      swept in bounded batches.
 *
 *   2. ASSET MINIFY — the theme's own CSS/JS are minified once, written
 *      to /uploads/brooks-law-cache/, and served with the content hash
 *      in the filename so they can be cached indefinitely. Rebuilt when
 *      a source file changes; superseded builds are pruned.
 *
 *   3. HTML MINIFY — collapses whitespace in text nodes and strips
 *      comments. <pre>, <textarea>, <script> and <style> are lifted out
 *      first, and attribute values are never rewritten, so deliberate
 *      spacing in alt/title/aria-label and JSON in data- attributes
 *      survive byte-for-byte.
 *
 *   4. RESOURCE HINTS — the LCP image gets a <link rel="preload"> with
 *      its srcset, so the browser starts fetching it before CSS
 *      finishes parsing. A hero video, when set, is the LCP instead and
 *      suppresses the image hint rather than competing with it.
 *
 * All defaults ON except page cache. Leave it off if a caching plugin
 * or a CDN already owns that layer — never run two page caches.
 *
 * @package brooks-law
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* -------------------------------------------------------------------------
 * Settings
 * ---------------------------------------------------------------------- */

/**
 * Register the Performance section.
 *
 * @param WP_Customize_Manager $wp_customize Manager.
 */
function brooks_law_perf_customize( $wp_customize ) {
	$wp_customize->add_section(
		'brooks_law_perf',
		array(
			'title'       => __( 'Performance', 'brooks-law-30-pro' ),
			'panel'       => 'brooks_law',
			'description' => __( 'Built-in speed layer. If you also run a caching plugin (WP Rocket, LiteSpeed, Super Page Cache) or a CDN that caches HTML, leave Page cache OFF here and let that layer handle it — never run two page caches at once.', 'brooks-law-30-pro' ),
		)
	);

	$toggles = array(
		'perf_page_cache'    => array( __( 'Page cache (anonymous visitors)', 'brooks-law-30-pro' ), false ),
		'perf_minify_assets' => array( __( 'Minify theme CSS & JS', 'brooks-law-30-pro' ), true ),
		'perf_minify_html'   => array( __( 'Minify HTML output', 'brooks-law-30-pro' ), true ),
		'perf_preload_hero'  => array( __( 'Preload the LCP image', 'brooks-law-30-pro' ), true ),
	);

	foreach ( $toggles as $key => $def ) {
		$wp_customize->add_setting(
			$key,
			array(
				'default'           => $def[1],
				'sanitize_callback' => 'wp_validate_boolean',
			)
		);
		$wp_customize->add_control(
			$key,
			array(
				'label'   => $def[0],
				'section' => 'brooks_law_perf',
				'type'    => 'checkbox',
			)
		);
	}

	$wp_customize->add_setting(
		'perf_cache_ttl',
		array(
			'default'           => 12,
			'sanitize_callback' => 'absint',
		)
	);
	$wp_customize->add_control(
		'perf_cache_ttl',
		array(
			'label'       => __( 'Page cache lifetime (hours)', 'brooks-law-30-pro' ),
			'section'     => 'brooks_law_perf',
			'type'        => 'number',
			'input_attrs' => array(
				'min'  => 1,
				'max'  => 168,
				'step' => 1,
			),
			'description' => __( 'Safety net only — every content, menu, or Customizer change invalidates the cache immediately.', 'brooks-law-30-pro' ),
		)
	);
}
add_action( 'customize_register', 'brooks_law_perf_customize' );

/**
 * Is a performance feature switched on?
 *
 * @param string $key     Theme mod key.
 * @param bool   $default Default when unset.
 * @return bool
 */
function brooks_law_perf_on( $key, $default = true ) {
	return (bool) get_theme_mod( $key, $default );
}

/* -------------------------------------------------------------------------
 * 1. Page cache
 * ---------------------------------------------------------------------- */

/**
 * Cache generation counter.
 *
 * Every key is namespaced by this number, so invalidating the whole cache
 * costs one option write instead of a table scan — and, critically, needs no
 * wp_cache_flush(), which on a persistent object cache would discard core's
 * and every other plugin's data along with ours.
 *
 * @return int
 */
function brooks_law_cache_generation() {
	$gen = get_option( 'brooks_law_cache_gen' );

	if ( ! is_numeric( $gen ) ) {
		$gen = 1;
		add_option( 'brooks_law_cache_gen', $gen, '', 'yes' );
	}

	return (int) $gen;
}

/**
 * Is this request cacheable at all?
 *
 * @return bool
 */
function brooks_law_cacheable() {
	if ( ! brooks_law_perf_on( 'perf_page_cache', false ) ) {
		return false;
	}
	if ( is_admin() || is_user_logged_in() || is_customize_preview() ) {
		return false;
	}
	// The convention every caching plugin honours; respect it so a plugin
	// can opt a page out without knowing anything about this theme.
	if ( defined( 'DONOTCACHEPAGE' ) && DONOTCACHEPAGE ) {
		return false;
	}
	if ( ! isset( $_SERVER['REQUEST_METHOD'] ) || 'GET' !== $_SERVER['REQUEST_METHOD'] ) {
		return false;
	}
	if ( ! empty( $_GET ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Presence check only; query strings are never cached.
		return false;
	}
	if ( is_search() || is_404() || is_feed() || is_preview() || is_robots() || is_favicon() ) {
		return false;
	}
	// Non-HTML endpoints (core sitemaps, etc.) must never be cached as pages.
	if ( '' !== (string) get_query_var( 'sitemap' ) ) {
		return false;
	}
	// Never cache for commenters, password-protected views, or sessions.
	foreach ( array_keys( (array) $_COOKIE ) as $cookie ) {
		if ( preg_match( '/^(wp-postpass|wordpress_logged_in|comment_author|woocommerce_)/', (string) $cookie ) ) {
			return false;
		}
	}

	/**
	 * Filter whether this request may be cached.
	 *
	 * @since 5.3.0
	 *
	 * @param bool $cacheable Result.
	 */
	return (bool) apply_filters( 'brooks_law_cacheable', true );
}

/**
 * Cache key for the current request.
 *
 * The host comes from the site's own configured home URL, never the
 * client-supplied Host header: a spoofed HTTP_HOST would otherwise let a
 * visitor write to, and read from, an arbitrary cache bucket.
 *
 * @return string
 */
function brooks_law_cache_key() {
	$host = (string) wp_parse_url( home_url(), PHP_URL_HOST );

	$uri = isset( $_SERVER['REQUEST_URI'] ) ? (string) wp_unslash( $_SERVER['REQUEST_URI'] ) : '/';
	$uri = (string) strtok( $uri, '#' );

	return 'blf_page_' . md5( brooks_law_cache_generation() . '|' . $host . '|' . $uri );
}

/**
 * Cache lifetime in seconds.
 *
 * @return int
 */
function brooks_law_cache_ttl() {
	return max( 1, absint( get_theme_mod( 'perf_cache_ttl', 12 ) ) ) * HOUR_IN_SECONDS;
}

/**
 * Send the caching headers that belong on a cacheable HTML response.
 *
 * Vary on Accept-Encoding so a shared cache in front of the site keeps
 * compressed and uncompressed copies apart, and on Cookie so it never hands
 * a logged-in visitor a body rendered for an anonymous one.
 *
 * @param string $state HIT or MISS.
 */
function brooks_law_cache_headers( $state ) {
	if ( headers_sent() ) {
		return;
	}

	header( 'X-Brooks-Cache: ' . ( 'HIT' === $state ? 'HIT' : 'MISS' ) );
	header( 'Vary: Accept-Encoding, Cookie' );
	header( 'Cache-Control: public, max-age=0, s-maxage=' . brooks_law_cache_ttl() . ', stale-while-revalidate=60' );
}

/**
 * Serve from cache, or start capturing.
 */
function brooks_law_cache_start() {
	if ( ! brooks_law_cacheable() ) {
		return;
	}

	$hit = get_transient( brooks_law_cache_key() );

	if ( is_string( $hit ) && '' !== $hit ) {
		brooks_law_cache_headers( 'HIT' );
		echo $hit; // phpcs:ignore WordPress.Security.EscapeOutput -- Verbatim HTML this code previously rendered and stored.
		exit;
	}

	brooks_law_cache_headers( 'MISS' );
	ob_start( 'brooks_law_cache_store' );
}
add_action( 'template_redirect', 'brooks_law_cache_start', 1 );

/**
 * Output-buffer callback: minify (if enabled) and store the page.
 *
 * @param string $html Final page HTML.
 * @return string
 */
function brooks_law_cache_store( $html ) {
	if ( brooks_law_perf_on( 'perf_minify_html' ) ) {
		$html = brooks_law_minify_html( $html );
	}

	// Only cache complete, successful HTML pages.
	$code = http_response_code();

	if ( false !== stripos( $html, '</html>' ) && ( false === $code || 200 === $code ) ) {
		set_transient( brooks_law_cache_key(), $html, brooks_law_cache_ttl() );
	}

	return $html;
}

/**
 * HTML minifier for requests the page cache is not handling.
 */
function brooks_law_html_min_start() {
	if ( brooks_law_cacheable() ) {
		return; // The cache path already minifies.
	}
	if ( is_admin() || is_customize_preview() || is_feed() || is_robots() ) {
		return;
	}
	if ( ! brooks_law_perf_on( 'perf_minify_html' ) ) {
		return;
	}

	ob_start( 'brooks_law_minify_html' );
}
add_action( 'template_redirect', 'brooks_law_html_min_start', 2 );

/**
 * Conservative HTML minification.
 *
 * Three properties this routine guarantees, each of which the naive version
 * of it does not:
 *
 *   1. The placeholder that stands in for a lifted <pre>/<script>/<style>/
 *      <textarea> block carries a random per-request suffix, so page content
 *      cannot forge one and have another element's contents substituted into
 *      it on restore.
 *   2. Whitespace is collapsed in text nodes ONLY. Tags are matched with a
 *      quote-aware pattern and passed through untouched, so attribute values
 *      — deliberate spacing in alt/title/aria-label, JSON parked in a data-
 *      attribute — survive byte-for-byte.
 *   3. Every regex result is checked; on a PCRE failure the original string
 *      is returned rather than a partially-processed one.
 *
 * @param string $html HTML.
 * @return string
 */
function brooks_law_minify_html( $html ) {
	if ( ! is_string( $html ) || '' === $html ) {
		return $html;
	}

	$original = $html;

	// Unforgeable for this request: content cannot collide with it.
	$marker = 'BLFP' . wp_generate_password( 16, false, false );

	$protected = array();
	$html      = preg_replace_callback(
		'#<(pre|textarea|script|style)\b[^>]*>.*?</\1\s*>#is',
		static function ( $matches ) use ( &$protected, $marker ) {
			$token               = '<!--' . $marker . count( $protected ) . '-->';
			$protected[ $token ] = $matches[0];
			return $token;
		},
		$html
	);
	if ( ! is_string( $html ) ) {
		return $original;
	}

	// Comments, keeping IE conditionals and our own placeholders.
	$html = preg_replace( '/<!--(?!' . $marker . ')(?!\[if)[\s\S]*?-->/', '', $html );
	if ( ! is_string( $html ) ) {
		return $original;
	}

	/*
	 * Split into tags and text. The tag pattern is an unrolled loop — a run of
	 * "not a quote or a close bracket", then any number of quoted attribute
	 * values each followed by another such run — which matches a tag whose
	 * attribute value legitimately contains ">" and cannot backtrack
	 * catastrophically, because no two branches can match the same character.
	 */
	$parts = preg_split(
		'/(<[a-zA-Z\/!][^>"\']*(?:"[^"]*"[^>"\']*|\'[^\']*\'[^>"\']*)*>)/',
		$html,
		-1,
		PREG_SPLIT_DELIM_CAPTURE
	);
	if ( ! is_array( $parts ) ) {
		return $original;
	}

	$out = '';

	foreach ( $parts as $index => $part ) {
		// Odd indices are the captured tags. Never touched.
		if ( 1 === $index % 2 ) {
			$out .= $part;
			continue;
		}

		$collapsed = preg_replace( '/\s+/', ' ', $part );
		$out      .= is_string( $collapsed ) ? $collapsed : $part;
	}

	return strtr( $out, $protected );
}

/**
 * Invalidate every cached page.
 *
 * Bumping the generation is the invalidation: it is one option write, it is
 * correct against a persistent object cache without touching anything this
 * theme did not write, and it takes effect on the very next request. The
 * bounded sweep afterwards keeps superseded rows from accumulating.
 *
 * Deliberately absent: wp_cache_flush(). It was here, and on any site with
 * Redis or Memcached it discarded core's caches and every plugin's along with
 * this theme's, turning a routine content edit into a site-wide cold start.
 */
function brooks_law_cache_purge() {
	update_option( 'brooks_law_cache_gen', brooks_law_cache_generation() + 1, true );
	brooks_law_cache_sweep();
}

/**
 * Remove superseded cache rows, a bounded batch at a time.
 *
 * delete_transient() rather than a raw DELETE: it clears the object-cache
 * entry along with the row, which a direct query does not, and it is why the
 * old code needed a flush to stay correct.
 *
 * @param int $limit Maximum rows to remove in one pass.
 */
function brooks_law_cache_sweep( $limit = 200 ) {
	global $wpdb;

	$names = $wpdb->get_col(
		$wpdb->prepare(
			"SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE %s LIMIT %d",
			$wpdb->esc_like( '_transient_blf_page_' ) . '%',
			(int) $limit
		)
	); // phpcs:ignore WordPress.DB.DirectDatabaseQuery -- Transient sweep; there is no core API for "every transient with this prefix".

	foreach ( (array) $names as $name ) {
		delete_transient( substr( (string) $name, strlen( '_transient_' ) ) );
	}
}

/*
 * Purge on everything that can change what a page renders.
 */
foreach ( array( 'save_post', 'deleted_post', 'wp_update_nav_menu', 'customize_save_after', 'switch_theme', 'update_option_permalink_structure', 'activated_plugin', 'deactivated_plugin' ) as $blf_purge_hook ) {
	add_action( $blf_purge_hook, 'brooks_law_cache_purge' );
}
unset( $blf_purge_hook );

/* -------------------------------------------------------------------------
 * 2. Asset minification
 * ---------------------------------------------------------------------- */

/**
 * Minify CSS.
 *
 * Quoted strings and unquoted url() payloads are lifted out before any
 * whitespace pass, so punctuation inside them is never mistaken for syntax:
 * `content: "Note: one, two"` keeps its spaces, and an escape sequence that
 * depends on a trailing space to terminate — `content: "\00D7 800"` — keeps
 * working. `/*! *​/` licence comments are preserved.
 *
 * @param string $css Stylesheet source.
 * @return string
 */
function brooks_law_minify_css( $css ) {
	if ( ! is_string( $css ) || '' === $css ) {
		return $css;
	}

	$original = $css;
	$strings  = array();

	/*
	 * Unrolled-loop form: a run of "neither quote nor backslash", then any
	 * number of (escape + another such run). The naive (?:[^"\\]|\\.)* spelling
	 * is equivalent but lets the engine try both branches at every character,
	 * which exhausts the PCRE JIT stack on a stylesheet this size — the
	 * function then returned the input unminified, silently, on exactly the
	 * two largest sheets.
	 */
	$css = preg_replace_callback(
		'/"[^"\\\\]*(?:\\\\.[^"\\\\]*)*"|\'[^\'\\\\]*(?:\\\\.[^\'\\\\]*)*\'|url\([^)\'"]*\)/s',
		static function ( $matches ) use ( &$strings ) {
			$key             = "\0BLFCSS" . count( $strings ) . "\0";
			$strings[ $key ] = $matches[0];
			return $key;
		},
		$css
	);
	if ( ! is_string( $css ) ) {
		return $original;
	}

	$css = preg_replace( '#/\*(?!!).*?\*/#s', '', $css );
	$css = preg_replace( '/\s+/', ' ', $css );
	$css = preg_replace( '/\s*([{};:,>+~])\s*/', '$1', $css );
	if ( ! is_string( $css ) ) {
		return $original;
	}

	$css = str_replace( ';}', '}', $css );

	return trim( strtr( $css, $strings ) );
}

/**
 * Minify JS conservatively: strip full-line comments and leading indentation.
 *
 * No tokenising, no renaming, and nothing that depends on understanding the
 * grammar — which is why it is safe to run over any file without a build
 * step. Anything more aggressive belongs in a real toolchain.
 *
 * @param string $js Script source.
 * @return string
 */
function brooks_law_minify_js( $js ) {
	if ( ! is_string( $js ) || '' === $js ) {
		return $js;
	}

	$original = $js;

	$js = preg_replace( '#^\s*/\*.*?\*/#ms', '', $js );   // Block comments at line starts.
	$js = preg_replace( '#^\s*//[^\n]*$#m', '', $js );    // Whole-line // comments.
	$js = preg_replace( "/\n{2,}/", "\n", $js );
	$js = preg_replace( '/^[ \t]+/m', '', $js );

	if ( ! is_string( $js ) ) {
		return $original;
	}

	return trim( $js );
}

/**
 * The directory minified assets are written to.
 *
 * Created with an index.php so a mis-configured server cannot list it.
 *
 * @return string|null Absolute path, or null when uploads are unusable.
 */
function brooks_law_min_dir() {
	$uploads = wp_upload_dir();

	if ( ! empty( $uploads['error'] ) ) {
		return null;
	}

	$dir = trailingslashit( $uploads['basedir'] ) . 'brooks-law-cache';

	if ( ! is_dir( $dir ) && ! wp_mkdir_p( $dir ) ) {
		return null;
	}

	$index = $dir . '/index.php';
	if ( ! file_exists( $index ) ) {
		file_put_contents( $index, "<?php\n// Silence is golden.\n" ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents -- Writing to the theme's own uploads subdirectory.
	}

	return $dir;
}

/**
 * Build (or reuse) a minified copy of a theme asset.
 *
 * @param string $rel  Path relative to the theme root.
 * @param string $type 'css' or 'js'.
 * @return array|null [ 'url' => ..., 'ver' => null ] or null on failure.
 */
function brooks_law_min_asset( $rel, $type ) {
	$src = get_template_directory() . '/' . $rel;

	if ( ! file_exists( $src ) ) {
		return null;
	}

	$dir = brooks_law_min_dir();
	if ( null === $dir ) {
		return null;
	}

	$uploads = wp_upload_dir();
	$base    = basename( $rel, '.' . $type );
	$hash    = substr( md5( $rel . filemtime( $src ) . BROOKS_LAW_VERSION ), 0, 10 );
	$name    = $base . '.' . $hash . '.min.' . $type;
	$dest    = $dir . '/' . $name;

	if ( ! file_exists( $dest ) ) {
		$raw = file_get_contents( $src ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- Reading the theme's own asset.

		if ( false === $raw ) {
			return null;
		}

		$min = ( 'css' === $type ) ? brooks_law_minify_css( $raw ) : brooks_law_minify_js( $raw );

		if ( '' === $min || false === file_put_contents( $dest, $min ) ) { // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents -- Writing to the theme's own uploads subdirectory.
			return null;
		}

		// A new build supersedes every older one of the same asset. Without
		// this the directory grew by one file per source edit, forever.
		brooks_law_min_prune( $dir, $base, $type, $name );
	}

	return array(
		'url' => trailingslashit( $uploads['baseurl'] ) . 'brooks-law-cache/' . $name,
		// null (not false) means WordPress appends no ?ver= at all — the hash
		// in the filename already makes the URL immutable.
		'ver' => null,
	);
}

/**
 * Delete superseded builds of one asset.
 *
 * @param string $dir  Cache directory.
 * @param string $base Asset basename without extension.
 * @param string $type 'css' or 'js'.
 * @param string $keep Filename to keep.
 */
function brooks_law_min_prune( $dir, $base, $type, $keep ) {
	$existing = glob( $dir . '/' . $base . '.*.min.' . $type );

	if ( ! is_array( $existing ) ) {
		return;
	}

	foreach ( $existing as $file ) {
		if ( basename( $file ) !== $keep ) {
			wp_delete_file( $file );
		}
	}
}

/**
 * Swap the enqueued theme assets for their minified copies.
 *
 * Runs late so it sees everything every module registered, and reads the
 * registries rather than guessing handles, so an asset that was not enqueued
 * on this request is simply skipped.
 */
function brooks_law_perf_swap_assets() {
	if ( ! brooks_law_perf_on( 'perf_minify_assets' ) || is_customize_preview() ) {
		return;
	}

	$styles = wp_styles();
	$style_map = array(
		'brooks-law-style'     => 'style.css',
		'brooks-law-tokens'    => 'assets/css/tokens.css',
		'brooks-law-editorial' => 'assets/css/editorial.css',
		'brooks-law-blocks'    => 'assets/css/editorial-blocks.css',
		'brooks-law-ui'        => 'assets/css/ui-components.css',
		'brooks-law-profile'   => 'assets/css/profile-pages.css',
		'brooks-law-home-editorial' => 'assets/css/home-editorial.css',
	);

	foreach ( $style_map as $handle => $rel ) {
		if ( ! isset( $styles->registered[ $handle ] ) ) {
			continue;
		}
		$min = brooks_law_min_asset( $rel, 'css' );
		if ( $min ) {
			$styles->registered[ $handle ]->src = $min['url'];
			$styles->registered[ $handle ]->ver = $min['ver'];
		}
	}

	$scripts = wp_scripts();
	$script_map = array(
		'brooks-law-navigation'     => 'assets/js/navigation.js',
		'brooks-law-editorial'      => 'assets/js/editorial.js',
		'brooks-law-contact-toggle' => 'assets/js/contact-toggle.js',
		'brooks-law-atmosphere'     => 'assets/js/atmosphere.js',
		'brooks-law-backtotop'      => 'assets/js/backtotop.js',
		'brooks-law-ribbon-art'     => 'assets/js/ribbon-art.js',
		'brooks-law-carousel'       => 'assets/js/carousel-drag.js',
	);

	foreach ( $script_map as $handle => $rel ) {
		if ( ! isset( $scripts->registered[ $handle ] ) ) {
			continue;
		}
		$min = brooks_law_min_asset( $rel, 'js' );
		if ( $min ) {
			$scripts->registered[ $handle ]->src = $min['url'];
			$scripts->registered[ $handle ]->ver = $min['ver'];
		}
	}
}
add_action( 'wp_enqueue_scripts', 'brooks_law_perf_swap_assets', 99 );

/* -------------------------------------------------------------------------
 * 4. Resource hints
 * ---------------------------------------------------------------------- */

/**
 * Preload the LCP image.
 *
 * When a hero video is configured it is the largest element instead, and the
 * poster image is not what the visitor waits for — so the hint stands down
 * rather than spending bandwidth racing the thing it is meant to help.
 */
function brooks_law_perf_preload() {
	if ( ! brooks_law_perf_on( 'perf_preload_hero' ) ) {
		return;
	}

	if ( is_front_page() && function_exists( 'brooks_law_hero_video_url' ) && '' !== brooks_law_hero_video_url() ) {
		return;
	}

	$id = 0;

	if ( is_front_page() ) {
		$id = absint( brooks_law_get_option( 'hero_image', 0 ) );
	} elseif ( is_singular() && has_post_thumbnail() ) {
		$id = (int) get_post_thumbnail_id();
	}

	if ( ! $id || ! wp_attachment_is_image( $id ) ) {
		return;
	}

	$src = wp_get_attachment_image_src( $id, 'full' );
	if ( ! $src ) {
		return;
	}

	$srcset = wp_get_attachment_image_srcset( $id, 'full' );
	$sizes  = wp_get_attachment_image_sizes( $id, 'full' );

	echo '<link rel="preload" as="image" href="' . esc_url( $src[0] ) . '"';
	if ( $srcset && $sizes ) {
		echo ' imagesrcset="' . esc_attr( $srcset ) . '" imagesizes="' . esc_attr( $sizes ) . '"';
	}
	echo ' fetchpriority="high">' . "\n";
}
add_action( 'wp_head', 'brooks_law_perf_preload', 2 );
