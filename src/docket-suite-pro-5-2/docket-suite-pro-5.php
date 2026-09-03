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
 * therefore declares the same brooks_ess_* functions. More than one copy
 * running at once would be a "cannot redeclare" fatal, so:
 *
 *   1. If ANY copy of this bootstrap, or of Essentials, has already loaded in
 *      this request, this file declares nothing at all and returns. That
 *      dormancy check has to come before the require below, because PHP
 *      early-binds top-level function declarations at compile time: a `return`
 *      cannot protect a function declared later in the same file, which is
 *      why the takeover helpers live in includes/bootstrap.php and not here.
 *   2. Whichever copy does load resolves the duplicate by VERSION on the next
 *      admin request — the newest survives, the rest are silently deactivated.
 *      Settings, redirect rules, the 404 log and the llms.txt body all live in
 *      folder-independent options and are never touched by deactivation, so
 *      they carry over whatever the winning folder is called.
 *
 * Helpers use a docket_suite_boot_ prefix, distinct from the docket_suite_
 * names a 5.2.2-or-earlier main file declares unconditionally at compile time.
 * That separation is load-bearing: those older declarations cannot be guarded
 * from here, so the only way this build can be loaded alongside one without a
 * "cannot redeclare" fatal is to not share their names.
 * -------------------------------------------------------------------------
 */

/*
 * Hard dormancy. Any of these means another copy of the suite, or of
 * Essentials, owns this request.
 */
if ( function_exists( 'docket_suite_boot_conflicts' )      // another copy of THIS build
	|| function_exists( 'docket_suite_conflicting_plugins' ) // a 5.2.2-or-earlier build
	|| function_exists( 'brooks_ess_defaults' )
	|| function_exists( 'brooks_ess_get' )
	|| defined( 'BROOKS_LLMS_OPTION' )
	|| ( defined( 'BROOKS_ESS_VERSION' ) && ! defined( 'DOCKET_SUITE_VERSION' ) ) ) {
	return;
}

/** Absolute path to this plugin's main file, for the bootstrap helpers. */
define( 'DOCKET_SUITE_MAIN_FILE', __FILE__ );

require_once plugin_dir_path( __FILE__ ) . 'includes/bootstrap.php';

/*
 * Registered before the load-order guard below, so that even a copy which
 * then goes dormant still schedules the self-heal that resolves the
 * duplicate. Without this a two-copy install could sit unresolved.
 */
add_action( 'admin_init', 'docket_suite_boot_self_heal', 1 );
add_action( 'admin_notices', 'docket_suite_boot_notice' );
register_activation_hook( __FILE__, 'docket_suite_boot_resolve' );

/*
 * Load-order guard: another copy is still on the active list. Stand down for
 * this one request; the self-heal above stands the loser down for good, and
 * the next page load belongs to a single copy.
 */
if ( docket_suite_boot_conflicts() ) {
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

// docket_suite_boot_flush_rewrites() lives in includes/bootstrap.php: like the
// takeover helpers it must not be early-bound in this file, or a second copy
// of the plugin would fatal on it before the dormancy guard above could run.
add_action( 'admin_init', 'docket_suite_boot_flush_rewrites', 99 );
