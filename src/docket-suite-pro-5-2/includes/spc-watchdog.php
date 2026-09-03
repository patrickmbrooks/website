<?php
/**
 * Brooks Law Essentials — Super Page Cache disk-cache watchdog.
 *
 * Site policy: Cloudflare edge is the ONLY cache layer; Super Page Cache's
 * local disk cache must stay OFF. SPC has repeatedly re-enabled its disk
 * cache after plugin/site updates. This watchdog checks the known SPC
 * option shapes and shows a red admin notice when disk cache is on.
 * Detection only — it never changes SPC's settings.
 *
 * @package Brooks_Essentials
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Best-effort detection of SPC's disk ("fallback") cache being enabled.
 * Checks the option shapes used by Super Page Cache for Cloudflare across
 * versions. Returns true only on a confident positive.
 *
 * @return bool
 */
function brooks_ess_spc_plugin_active() {
	$active = (array) get_option( 'active_plugins', array() );
	if ( is_multisite() ) {
		$active = array_merge( $active, array_keys( (array) get_site_option( 'active_sitewide_plugins', array() ) ) );
	}
	foreach ( $active as $path ) {
		if ( false !== stripos( (string) $path, 'super-page-cache' ) || false !== stripos( (string) $path, 'wp-cloudflare-page-cache' ) ) {
			return true;
		}
	}
	return false;
}

function brooks_ess_spc_disk_cache_on() {
	// Stand down entirely when Super Page Cache is not an active plugin —
	// its leftover option rows would otherwise trigger false alarms.
	if ( ! brooks_ess_spc_plugin_active() ) {
		return false;
	}

	// Main config array used by the SPC plugin family.
	foreach ( array( 'swcfpc_config', 'spc_config' ) as $opt_name ) {
		$cfg = get_option( $opt_name );
		if ( is_array( $cfg ) ) {
			foreach ( array( 'cf_fallback_cache', 'fallback_cache', 'cf_disk_cache' ) as $key ) {
				if ( isset( $cfg[ $key ] ) && 1 === (int) $cfg[ $key ] ) {
					return true;
				}
			}
		}
	}

	// Standalone flags some versions use.
	foreach ( array( 'swcfpc_fallback_cache', 'spc_fallback_cache' ) as $opt_name ) {
		$val = get_option( $opt_name, null );
		if ( null !== $val && 1 === (int) $val ) {
			return true;
		}
	}

	return false;
}

/**
 * Admin notice when the disk cache is detected ON.
 */
function brooks_ess_spc_watchdog_notice() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	if ( ! brooks_ess_spc_disk_cache_on() ) {
		return;
	}
	?>
	<div class="notice notice-error">
		<p>
			<strong>Brooks Essentials watchdog:</strong>
			Super Page Cache's <em>disk cache</em> appears to be <strong>ON</strong> again
			(it tends to re-enable itself after updates). Site policy is Cloudflare-edge-only.
			Open Super Page Cache's settings, turn the disk/fallback cache OFF, then purge Cloudflare.
			Leaving it on double-caches pages and hurts PageSpeed.
		</p>
	</div>
	<?php
}
add_action( 'admin_notices', 'brooks_ess_spc_watchdog_notice' );

/**
 * v3 cross-guard: the Brooks Law 3.0 theme ships its own page cache.
 * Site policy is Cloudflare-edge-only, so warn if BOTH the theme cache
 * and Super Page Cache/Cloudflare are in play at once.
 */
function brooks_ess_theme_cache_watchdog() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	if ( ! function_exists( 'brooks_law_cache_purge' ) ) {
		return; // 3.0 theme not active.
	}
	if ( ! get_theme_mod( 'perf_page_cache', false ) ) {
		return;
	}
	if ( ! brooks_ess_spc_plugin_active() ) {
		return;
	}
	?>
	<div class="notice notice-error">
		<p>
			<strong>Brooks Essentials watchdog:</strong>
			the Brooks Law theme&#8217;s <em>page cache</em> is ON while Super Page Cache /
			Cloudflare is active. Site policy is Cloudflare-edge-only &mdash; never two page
			caches. Turn it off under Customize &rarr; Brooks Law Firm &rarr; Performance
			(the theme&#8217;s minify and preload switches are fine to keep on).
		</p>
	</div>
	<?php
}
add_action( 'admin_notices', 'brooks_ess_theme_cache_watchdog' );
