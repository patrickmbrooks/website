<?php
/**
 * Brooks Law Essentials — core bootstrap (moved out of the main file in
 * v2.2.1 so the self-handoff dormancy guard works: PHP binds top-level
 * function declarations at compile time, so shared functions must live in a
 * runtime-required include, never in the main plugin file).
 *
 * @package Brooks_Essentials
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Shipped defaults.
 *
 * The firm_* values are only a fallback. When the Brooks Law theme is active,
 * its Customizer values win — so there is one place to edit, not two.
 *
 * @return array
 */
function brooks_ess_defaults() {
	return array(
		// Redirects.
		'redirects'           => '',
		'log_404'             => true,

		// Robots.txt (v3).
		'robots_mode'         => 'managed', // managed | virtual | append | leave.
		'ai_crawlers'         => 'allow',   // allow | disallow | omit ('leave' = legacy omit).
		'sitemap_url'         => '',        // Blank = auto-detect.
		'robots_extra'        => '',
		'robots_takeover'     => false,     // Approve overwriting a non-managed physical file.
		'track_calls'         => false,     // GA4 call/text click events.

		// Cleanup / hardening.
		'disable_comments'    => true,
		'disable_file_edit'   => true,
		// Off by default: XML-RPC is what the WordPress mobile app and Jetpack
		// use. The Brooks Law theme's Site Tuning section carries the same
		// switch and checks THIS value at runtime, standing down when it is
		// on — so exactly one of the two acts, and turning it on here is
		// always safe.
		'disable_xmlrpc'      => false,

		// Firm info fallback (used only if the theme has no value).
		'firm_name'           => 'Brooks Law Firm',
		'firm_phone'          => '(901) 324-5000',
		'firm_phone_link'     => '+19013245000',
		'firm_cell'           => '901-412-2973',
		'firm_cell_link'      => '+19014122973',
		'firm_email'          => 'patrick@patrickbrookslaw.com',
		'firm_address'        => '2299 Union Avenue',
		'firm_city_state'     => 'Memphis, Tennessee 38104',
		'firm_hours'          => 'Monday – Friday, 8:00 a.m. – 5:30 p.m.',

		// Housekeeping.
		'delete_on_uninstall' => false,
	);
}

/**
 * Read a plugin setting.
 *
 * @param string $key Setting key.
 * @return mixed
 */
function brooks_ess_get( $key ) {
	$defaults = brooks_ess_defaults();
	$options  = get_option( BROOKS_ESS_OPTION, array() );

	if ( ! is_array( $options ) ) {
		$options = array();
	}

	if ( array_key_exists( $key, $options ) ) {
		return $options[ $key ];
	}

	return isset( $defaults[ $key ] ) ? $defaults[ $key ] : '';
}

/**
 * Read a piece of firm information.
 *
 * Order: active theme's Customizer value → this plugin's setting → default.
 * That way the Brooks Law theme stays the single source of truth while it is
 * active, and the shortcodes keep working after a theme change.
 *
 * @param string $key Firm info key, e.g. firm_phone.
 * @return string
 */
function brooks_ess_firm( $key ) {
	$from_theme = get_theme_mod( $key, '' );
	if ( is_string( $from_theme ) && '' !== trim( $from_theme ) ) {
		return $from_theme;
	}

	// The theme's own helper knows about its shipped defaults; prefer it.
	if ( function_exists( 'brooks_law_get_option' ) ) {
		$from_theme_default = brooks_law_get_option( $key, '' );
		if ( is_string( $from_theme_default ) && '' !== trim( $from_theme_default ) ) {
			return $from_theme_default;
		}
	}

	return (string) brooks_ess_get( $key );
}

/**
 * Normalize a path for comparison: leading slash, no trailing slash,
 * lowercase, no query string.
 *
 * @param string $path Raw path.
 * @return string
 */
function brooks_ess_normalize_path( $path ) {
	$path = (string) $path;

	// Strip any scheme/host if a full URL was pasted in.
	$parsed = wp_parse_url( $path );
	if ( isset( $parsed['path'] ) ) {
		$path = $parsed['path'];
	}

	$path = strtok( $path, '?' );
	$path = strtolower( trim( (string) $path ) );
	$path = '/' . trim( $path, '/' );

	return $path;
}

require_once BROOKS_ESS_DIR . 'includes/settings.php';
require_once BROOKS_ESS_DIR . 'includes/redirects.php';
require_once BROOKS_ESS_DIR . 'includes/early-redirects.php';
require_once BROOKS_ESS_DIR . 'includes/shortcodes.php';
require_once BROOKS_ESS_DIR . 'includes/crawlers.php';
require_once BROOKS_ESS_DIR . 'includes/cleanup.php';
require_once BROOKS_ESS_DIR . 'includes/baked-rules.php';
require_once BROOKS_ESS_DIR . 'includes/llms-auto.php';
require_once BROOKS_ESS_DIR . 'includes/llms-txt.php';
require_once BROOKS_ESS_DIR . 'includes/llms-bigsave.php';
require_once BROOKS_ESS_DIR . 'includes/spc-watchdog.php';
require_once BROOKS_ESS_DIR . 'includes/image-optimizer.php';

/*
 * SEO half (Suite only). Stands down automatically when Yoast, Rank Math or
 * AIOSEO is active, so it is safe to run alongside them during a cutover.
 */
require_once BROOKS_ESS_DIR . 'includes/seo.php';
require_once BROOKS_ESS_DIR . 'includes/seo-migrate.php';
require_once BROOKS_ESS_DIR . 'includes/indexnow.php';
