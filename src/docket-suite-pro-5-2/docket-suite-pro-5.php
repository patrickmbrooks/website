<?php
/**
 * Plugin Name:       Docket Suite Pro 5
 * Plugin URI:        https://patrickbrookslaw.com/
 * Description:       All-in-one site operations + SEO for law firm sites. Operational half (fallback 301 redirects with a cleaned rule set plus an early exact-match pass, 404 log, managed robots.txt, editable /llms.txt, upload-time image optimization, Super Page Cache disk-cache watchdog, firm-info shortcodes, hardening) runs always. SEO half (per-page titles/meta, canonical, OG/Twitter, XML sitemap, attachment 301s) stands down automatically when Yoast, Rank Math or AIOSEO is active. Part of the Docket suite.
 * Version:           5.2.3
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            Patrick Brooks
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       docket-suite
 * Update URI:        false
 *
 * @package Docket_Suite
 *
 * ---------------------------------------------------------------------------
 * DOCKET SUITE PRO 5 (5.0.0) — REBUILT ON BROOKS LAW ESSENTIALS 3.0.2
 *
 * Supersedes the never-deployed Docket Suite 1.0.0 prototype. This is the
 * first build intended for a live site.
 * ---------------------------------------------------------------------------
 * The 1.0.0 prototype was assembled from Essentials 2.0.0 modules. Installing
 * it over a 3.0.2 site would have silently rolled the operational half back:
 * no core.php, no early-redirects.php, a 76-line crawlers.php with no
 * robots.txt manager, and 98 baked rules instead of 133.
 *
 * This build takes the Essentials 3.0.2 modules verbatim and adds the SEO
 * half beside them. Nothing operational is older than what is already live.
 *
 * Two collisions found in the 1.0.0 pairing and resolved here:
 *
 *   1. robots.txt had two writers. crawlers.php writes and heals a physical
 *      robots.txt (filter priority 99) including its own "Sitemap:" line;
 *      seo.php registered a second robots_txt filter at priority 10 adding
 *      another. The seo.php hook is removed — crawlers.php is the single
 *      authority — and crawlers.php now recognises DOCKET_SUITE_SEO_ACTIVE
 *      so the advertised sitemap follows whichever engine is live:
 *        Yoast active  -> /sitemap_index.xml
 *        Suite SEO live -> /sitemap.xml
 *
 *   2. Sitemap ownership. seo.php disables core's wp_sitemaps and claims
 *      /sitemap.xml only when no other SEO plugin is active, and ships 301s
 *      from the legacy Yoast sitemap URLs, so nothing 404s after a cutover.
 *
 * Deliberately NOT duplicated: schema. The theme's inc/schema-graph.php emits
 * the full @graph (LegalService, Attorney/Person, WebSite, WebPage,
 * BreadcrumbList) and inc/faq-schema.php the FAQ blocks. seo.php contains no
 * JSON-LD at all, so there is no second LegalService entity. Leave schema to
 * the theme.
 * ---------------------------------------------------------------------------
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*
 * -------------------------------------------------------------------------
 * SELF-HANDOFF BOOTSTRAP
 *
 * The Suite carries the same operational modules as Brooks Law Essentials and
 * therefore declares the same brooks_ess_* functions. Running both at once
 * would be a "cannot redeclare" fatal, so:
 *
 *   1. On activation, every other active plugin whose path contains
 *      "brooks-law-essentials" is silently deactivated. Settings, redirect
 *      rules, the 404 log and the llms.txt body all live in shared options
 *      and are NOT touched by deactivation — they carry straight over.
 *   2. If an Essentials copy is already loaded in this same request, this
 *      file stays dormant for that one request and takes over on the next
 *      page load.
 *
 * Helpers use the unique docket_suite_ prefix so they cannot collide.
 * -------------------------------------------------------------------------
 */

/**
 * Find every active plugin that ships the shared brooks_ess_* modules.
 *
 * Detection is by symbol: any active plugin whose main file or
 * includes/core.php declares brooks_ess_defaults() is a copy of Brooks Law
 * Essentials, whatever the directory is called. Folder names only decide
 * WHICH files are worth opening, never whether a plugin is a match — an
 * earlier version short-circuited on a folder-name substring, and one of the
 * substrings was generic enough ("site-essentials") to match an unrelated
 * third-party plugin and silently deactivate it.
 *
 * The scan reads plugin source from disk, so the result is cached against a
 * fingerprint of the active-plugin list. In steady state this is one
 * autoloaded option read per request and no filesystem access.
 *
 * @return string[] Plugin basenames, e.g. brooks-law-essentials/plugin.php.
 */
function docket_suite_conflicting_plugins() {
	static $memo = null;
	if ( null !== $memo ) {
		return $memo;
	}

	$self   = plugin_basename( __FILE__ );
	$active = (array) get_option( 'active_plugins', array() );
	$others = array_values( array_diff( $active, array( $self ) ) );

	$fingerprint = md5( wp_json_encode( $others ) );
	$cache       = get_option( 'docket_suite_conflict_cache', array() );
	if ( is_array( $cache ) && isset( $cache['key'], $cache['hits'] ) && $cache['key'] === $fingerprint ) {
		$memo = (array) $cache['hits'];
		return $memo;
	}

	$plugin_dir = defined( 'WP_PLUGIN_DIR' ) ? WP_PLUGIN_DIR : '';
	$hits       = array();

	foreach ( $others as $path ) {
		$path = (string) $path;

		if ( '' === $plugin_dir ) {
			continue;
		}

		/*
		 * The folder names Essentials has shipped under are checked first
		 * only to order the work: an exact folder match is confirmed by the
		 * same symbol check as anything else, never accepted on its own.
		 */
		$folder     = dirname( $path );
		$candidates = array( $plugin_dir . '/' . $path );
		if ( '.' !== $folder ) {
			$candidates[] = $plugin_dir . '/' . $folder . '/includes/core.php';
			$candidates[] = $plugin_dir . '/' . $folder . '/inc/core.php';
		}

		foreach ( $candidates as $file ) {
			if ( ! is_readable( $file ) ) {
				continue;
			}
			// Reading a sibling plugin's own source; WP_Filesystem is not warranted here.
			$source = file_get_contents( $file, false, null, 0, 262144 ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
			if ( is_string( $source ) && false !== strpos( $source, 'function brooks_ess_defaults' ) ) {
				$hits[] = $path;
				break;
			}
		}
	}

	$memo = array_values( array_unique( $hits ) );
	update_option( 'docket_suite_conflict_cache', array( 'key' => $fingerprint, 'hits' => $memo ), true );

	return $memo;
}

/**
 * Deactivate any conflicting copy of Brooks Law Essentials.
 *
 * Silent deactivation: no hooks fire and no options are touched, so the
 * shared settings, redirect rules, 404 log and llms.txt body carry over.
 */
function docket_suite_takeover_on_activation() {
	if ( ! function_exists( 'deactivate_plugins' ) ) {
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
	}

	$others = docket_suite_conflicting_plugins();
	if ( ! $others ) {
		return;
	}

	deactivate_plugins( $others, true );
	delete_option( 'docket_suite_conflict_cache' );
	update_option( 'docket_suite_handoff_done', implode( ', ', $others ), false );
}

/**
 * Self-heal on admin requests.
 *
 * Covers the cases the activation hook cannot: a conflicting copy re-enabled
 * by hand, a renamed folder, or an activation whose hook never fired. Gated
 * on the cached conflict list, so it is a single option read when clean.
 */
function docket_suite_takeover_self_heal() {
	if ( ! is_admin() || ! current_user_can( 'activate_plugins' ) ) {
		return;
	}
	if ( docket_suite_conflicting_plugins() ) {
		docket_suite_takeover_on_activation();
	}
}
add_action( 'admin_init', 'docket_suite_takeover_self_heal', 1 );

register_activation_hook( __FILE__, 'docket_suite_takeover_on_activation' );

/**
 * One-time notice confirming the handoff from Essentials.
 */
function docket_suite_handoff_notice() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$done = get_option( 'docket_suite_handoff_done' );
	if ( ! $done ) {
		return;
	}
	delete_option( 'docket_suite_handoff_done' );

	echo '<div class="notice notice-success is-dismissible"><p><strong>Docket Suite Pro 5:</strong> took over from ' . esc_html( $done ) . ', which was deactivated. Settings, redirect rules, the 404 log and your llms.txt carried over unchanged. Leave the old plugin deactivated as a rollback — do NOT use the WordPress &ldquo;Delete&rdquo; link on it, as that would erase the shared settings.</p></div>';
}
add_action( 'admin_notices', 'docket_suite_handoff_notice' );

/*
 * Dormancy guard: if an Essentials copy is loaded in this same request,
 * define nothing further. The takeover above has already deactivated it, so
 * the next request belongs to this copy alone.
 */
if ( function_exists( 'brooks_ess_defaults' )
	|| function_exists( 'brooks_ess_get' )
	|| defined( 'BROOKS_LLMS_OPTION' )
	|| ( defined( 'BROOKS_ESS_VERSION' ) && ! defined( 'DOCKET_SUITE_VERSION' ) ) ) {
	return;
}

/*
 * Load-order guard. The check above only catches an Essentials copy that has
 * already been loaded this request. When this plugin's folder sorts first,
 * Essentials loads *after* us and it is Essentials that hits the redeclare
 * fatal — a fatal WordPress attributes to whichever plugin is being
 * activated. So: if a conflicting copy is still on the active list at all,
 * stay dormant for this one request. docket_suite_takeover_self_heal() runs
 * on admin_init and stands the other copy down, and the next page load
 * belongs to this plugin alone.
 */
if ( docket_suite_conflicting_plugins() ) {
	return;
}

define( 'DOCKET_SUITE_VERSION', '5.2.3' );

/*
 * ---------------------------------------------------------------------------
 * Backward-compatible constants.
 *
 * The operational modules were written for Brooks Law Essentials and
 * reference BROOKS_ESS_* constants. Defining the same names here lets those
 * modules drop in unchanged, and — because the option keys are identical —
 * a site upgrading from Essentials keeps its settings, rules and log with no
 * migration step at all.
 * ---------------------------------------------------------------------------
 */
if ( ! defined( 'BROOKS_ESS_VERSION' ) ) {
	define( 'BROOKS_ESS_VERSION', DOCKET_SUITE_VERSION );
}
if ( ! defined( 'BROOKS_ESS_FILE' ) ) {
	define( 'BROOKS_ESS_FILE', __FILE__ );
}
if ( ! defined( 'BROOKS_ESS_DIR' ) ) {
	define( 'BROOKS_ESS_DIR', plugin_dir_path( __FILE__ ) );
}
if ( ! defined( 'BROOKS_ESS_OPTION' ) ) {
	define( 'BROOKS_ESS_OPTION', 'brooks_ess_options' );   // Unchanged: keeps existing settings.
}
if ( ! defined( 'BROOKS_ESS_404_LOG' ) ) {
	define( 'BROOKS_ESS_404_LOG', 'brooks_ess_404_log' );  // Unchanged: keeps existing log.
}

require_once BROOKS_ESS_DIR . 'includes/core.php';

/*
 * ---------------------------------------------------------------------------
 * SEO half lifecycle.
 *
 * seo.php ships register_*_hook calls bound to its own file in the standalone
 * plugin; as a module those must be re-bound to this file. The rewrite rule
 * for /sitemap.xml is also self-healing on admin_init, so a Suite installed
 * without a manual activation still claims the URL once — and only when no
 * other SEO plugin is active.
 * ---------------------------------------------------------------------------
 */
if ( class_exists( 'Docket_SEO' ) ) {
	register_activation_hook( __FILE__, array( 'Docket_SEO', 'activate' ) );
	register_deactivation_hook( __FILE__, array( 'Docket_SEO', 'deactivate' ) );
}

/**
 * One-shot rewrite flush so /sitemap.xml resolves without re-activating.
 */
function docket_suite_maybe_flush_rewrites() {
	if ( get_option( 'docket_suite_rw_flushed_v50' ) ) {
		return;
	}
	if ( defined( 'WPSEO_VERSION' ) || defined( 'RANK_MATH_VERSION' ) || defined( 'AIOSEO_VERSION' ) ) {
		return; // Another SEO plugin owns sitemaps; do not claim the URL yet.
	}
	flush_rewrite_rules();
	update_option( 'docket_suite_rw_flushed_v50', 1, false );
}
add_action( 'admin_init', 'docket_suite_maybe_flush_rewrites', 99 );
