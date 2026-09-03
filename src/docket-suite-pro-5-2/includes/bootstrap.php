<?php
/**
 * Docket Suite — self-handoff bootstrap.
 *
 * WHY THIS IS AN INCLUDE AND NOT PART OF THE MAIN PLUGIN FILE
 *
 * PHP early-binds unconditional top-level function declarations when a file is
 * compiled, which happens before any statement in that file runs. A `return`
 * placed above a declaration therefore does not prevent it — the function is
 * already bound. includes/core.php has always documented this rule for the
 * shared brooks_ess_* modules; the takeover helpers below used to sit in the
 * main plugin file and break it.
 *
 * The consequence was a white screen, in exactly the situation this bootstrap
 * exists to make safe. Two copies of the Suite installed under different folder
 * names — which is what happens when an update is uploaded as a zip whose
 * folder does not match the installed one — both declared
 * docket_suite_boot_conflicts() at compile time, and the second one to
 * load fataled with "Cannot redeclare" before its dormancy guard could run.
 * The guard was checking for brooks_ess_* symbols, which live in a runtime
 * include and so were correctly absent; it never got the chance to matter.
 *
 * Moving these declarations behind a runtime require_once, with a
 * function_exists() return in front of it, is what makes the guard effective.
 *
 * @package Docket_Suite
 * @since   5.2.3
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Read a plugin's Version header straight from its file.
 *
 * get_plugin_data() is not available this early, and loading wp-admin includes
 * during plugin load to get it would be worse than reading 8 KB of the file.
 *
 * @param string $file Absolute path to a plugin's main file.
 * @return string Version string, or '0' when it cannot be read.
 */
function docket_suite_boot_read_version( $file ) {
	if ( ! is_readable( $file ) ) {
		return '0';
	}

	// Reading a plugin header; WP_Filesystem is not warranted or available here.
	$head = file_get_contents( $file, false, null, 0, 8192 ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents

	if ( ! is_string( $head ) || ! preg_match( '/^[ \t\/*#@]*Version:\s*(.+)$/mi', $head, $m ) ) {
		return '0';
	}

	return trim( $m[1] );
}

/**
 * This copy's own version.
 *
 * @return string
 */
function docket_suite_boot_self_version() {
	static $version = null;

	if ( null === $version ) {
		$version = defined( 'DOCKET_SUITE_MAIN_FILE' ) ? docket_suite_boot_read_version( DOCKET_SUITE_MAIN_FILE ) : '0';
	}

	return $version;
}

/**
 * Every other active plugin that ships the shared brooks_ess_* modules.
 *
 * Detection is by symbol. Any active plugin whose main file or includes/core.php
 * declares brooks_ess_defaults() is a copy of these modules — Brooks Law
 * Essentials, or another build of this Suite — whatever its directory is
 * called. Folder names are never sufficient on their own: an earlier version
 * short-circuited on a folder-name substring, and one of the substrings was
 * generic enough ("site-essentials") to match an unrelated third-party plugin
 * and silently deactivate it.
 *
 * The scan reads plugin source from disk, so the result is cached against a
 * fingerprint of the active-plugin list. In steady state this is one autoloaded
 * option read per request and no filesystem access.
 *
 * @return array<string,string> Plugin basename => version.
 */
function docket_suite_boot_conflicts() {
	static $memo = null;

	if ( null !== $memo ) {
		return $memo;
	}

	$self   = defined( 'DOCKET_SUITE_MAIN_FILE' ) ? plugin_basename( DOCKET_SUITE_MAIN_FILE ) : '';
	$active = (array) get_option( 'active_plugins', array() );
	$others = array_values( array_diff( $active, array( $self ) ) );

	$fingerprint = md5( wp_json_encode( $others ) . '|' . docket_suite_boot_self_version() );
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

		$folder     = dirname( $path );
		$main       = $plugin_dir . '/' . $path;
		$candidates = array( $main );

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
				$hits[ $path ] = docket_suite_boot_read_version( $main );
				break;
			}
		}
	}

	$memo = $hits;
	update_option( 'docket_suite_conflict_cache', array( 'key' => $fingerprint, 'hits' => $memo ), true );

	return $memo;
}

/**
 * Is a strictly newer build of this suite also installed and active?
 *
 * @return bool
 */
function docket_suite_boot_newer_exists() {
	$mine = docket_suite_boot_self_version();

	foreach ( docket_suite_boot_conflicts() as $version ) {
		if ( version_compare( $version, $mine, '>' ) ) {
			return true;
		}
	}

	return false;
}

/**
 * Resolve a duplicate installation down to exactly one active copy.
 *
 * The rule is by version, not by load order, because load order is decided by
 * a string sort of folder names and would otherwise let an older build in a
 * folder that happens to sort first defeat a newer one.
 *
 *   - A strictly newer copy exists  -> this copy retires itself.
 *   - Otherwise                     -> this copy retires the older ones.
 *
 * Deactivation is silent: no hooks fire and no options are touched, so the
 * shared settings, redirect rules, the 404 log and the llms.txt body carry
 * straight over. Nothing is ever deleted.
 */
function docket_suite_boot_resolve() {
	if ( ! function_exists( 'deactivate_plugins' ) ) {
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
	}

	$others = docket_suite_boot_conflicts();

	if ( ! $others ) {
		return;
	}

	$self = defined( 'DOCKET_SUITE_MAIN_FILE' ) ? plugin_basename( DOCKET_SUITE_MAIN_FILE ) : '';

	if ( docket_suite_boot_newer_exists() && '' !== $self ) {
		// A newer build is installed. Stand down rather than fight it.
		deactivate_plugins( array( $self ), true );
		delete_option( 'docket_suite_conflict_cache' );
		return;
	}

	deactivate_plugins( array_keys( $others ), true );
	delete_option( 'docket_suite_conflict_cache' );
	update_option( 'docket_suite_handoff_done', implode( ', ', array_keys( $others ) ), false );
}

/**
 * Self-heal on admin requests.
 *
 * Covers what the activation hook cannot: a conflicting copy re-enabled by
 * hand, a renamed folder, or an activation whose hook never fired. Gated on the
 * cached conflict list, so it is a single option read when clean.
 *
 * Registered even by a copy that then goes dormant, which is what lets a
 * duplicate installation resolve itself on the very next admin page load.
 */
function docket_suite_boot_self_heal() {
	if ( ! is_admin() || ! current_user_can( 'activate_plugins' ) ) {
		return;
	}

	if ( docket_suite_boot_conflicts() ) {
		docket_suite_boot_resolve();
	}
}

/**
 * One-time notice confirming the handoff.
 */
function docket_suite_boot_notice() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$done = get_option( 'docket_suite_handoff_done' );

	if ( ! $done ) {
		return;
	}

	delete_option( 'docket_suite_handoff_done' );

	echo '<div class="notice notice-success is-dismissible"><p><strong>Docket Suite Pro 5:</strong> took over from ' . esc_html( $done ) . ', which was deactivated. Settings, redirect rules, the 404 log and your llms.txt carried over unchanged. Leave the old copy deactivated as a rollback &mdash; do NOT use the WordPress &ldquo;Delete&rdquo; link on it, as that would erase the shared settings.</p></div>';
}

/**
 * One-shot rewrite flush so /sitemap.xml resolves without re-activating.
 *
 * Declared here rather than in the main plugin file for the same early-binding
 * reason as everything else above.
 */
function docket_suite_boot_flush_rewrites() {
	if ( get_option( 'docket_suite_rw_flushed_v50' ) ) {
		return;
	}
	if ( defined( 'WPSEO_VERSION' ) || defined( 'RANK_MATH_VERSION' ) || defined( 'AIOSEO_VERSION' ) ) {
		return; // Another SEO plugin owns sitemaps; do not claim the URL yet.
	}
	flush_rewrite_rules();
	update_option( 'docket_suite_rw_flushed_v50', 1, false );
}
