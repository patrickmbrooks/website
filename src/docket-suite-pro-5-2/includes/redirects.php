<?php
/**
 * Brooks Essentials — fallback redirects and 404 logging.
 *
 * Design note: redirects fire ONLY when WordPress has already decided the
 * request is a 404. That makes this a pure safety net — it can never shadow a
 * live page, and it can never fight Yoast Premium's redirect manager, because
 * anything Yoast handles never reaches a 404 in the first place.
 *
 * @package Brooks_Essentials
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Parse the redirect textarea into a map.
 *
 * Accepted per line:
 *   /old-path/ => /new-path/
 *   /old-path/ , /new-path/
 *   /old-path/   /new-path/
 * Lines starting with # are comments.
 *
 * @return array Normalized source path => destination (path or URL).
 */
function brooks_ess_redirect_map() {
	$raw = (string) brooks_ess_get( 'redirects' );
	if ( '' === trim( $raw ) ) {
		return array();
	}

	$map = array();

	foreach ( preg_split( '/\r\n|\r|\n/', $raw ) as $line ) {
		$line = trim( $line );

		if ( '' === $line || '#' === substr( $line, 0, 1 ) ) {
			continue;
		}

		$parts = preg_split( '/\s*(?:=>|,|\s)\s*/', $line, 2 );

		if ( ! is_array( $parts ) || count( $parts ) < 2 ) {
			continue;
		}

		$from = brooks_ess_normalize_path( $parts[0] );
		$to   = trim( (string) $parts[1] );

		if ( '/' === $from || '' === $to ) {
			continue;
		}

		$map[ $from ] = $to;
	}

	return $map;
}

/**
 * Resolve a destination to an absolute URL.
 *
 * @param string $to Destination path or absolute URL.
 * @return string
 */
function brooks_ess_destination_url( $to ) {
	if ( preg_match( '#^https?://#i', $to ) ) {
		return esc_url_raw( $to );
	}

	return home_url( '/' . ltrim( $to, '/' ) );
}

/**
 * Fire a 301 when a 404'd path is in the map.
 */
function brooks_ess_maybe_redirect() {
	if ( ! is_404() || is_admin() ) {
		return;
	}

	$map = brooks_ess_redirect_map();
	if ( empty( $map ) ) {
		brooks_ess_log_404();
		return;
	}

	$request = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
	$path    = brooks_ess_normalize_path( $request );

	if ( ! isset( $map[ $path ] ) ) {
		brooks_ess_log_404();
		return;
	}

	$target = brooks_ess_destination_url( $map[ $path ] );

	// Preserve any query string the visitor arrived with.
	$query = wp_parse_url( (string) $request, PHP_URL_QUERY );
	if ( $query ) {
		$target = add_query_arg( wp_parse_args( $query ), $target );
	}

	// Never redirect a path to itself.
	if ( brooks_ess_normalize_path( $target ) === $path ) {
		return;
	}

	wp_safe_redirect( $target, 301, 'Brooks Essentials' );
	exit;
}
add_action( 'template_redirect', 'brooks_ess_maybe_redirect', 1 );

/**
 * Paths not worth logging: vulnerability scanners and asset probes make up
 * most 404 traffic and would otherwise flood the log.
 *
 * @param string $path Normalized path.
 * @return bool
 */
function brooks_ess_is_noise( $path ) {
	$noise_fragments = array(
		'wp-admin', 'wp-login', 'wp-content', 'wp-includes', 'xmlrpc',
		'.php', '.env', '.git', '.aspx', '.asp', '.jsp', '.cgi',
		'.sql', '.zip', '.tar', '.bak', '.log', '.ini', '.yml',
		'phpmyadmin', 'vendor/', 'node_modules', 'autodiscover',
		'.well-known', 'favicon', 'apple-touch-icon', 'robots.txt',
		'sitemap', 'ads.txt', 'feed',
	);

	foreach ( $noise_fragments as $fragment ) {
		if ( false !== strpos( $path, $fragment ) ) {
			return true;
		}
	}

	// Absurdly long paths are always scanners.
	if ( strlen( $path ) > 180 ) {
		return true;
	}

	return false;
}

/**
 * Record a 404 so there is a shortlist of redirects worth creating.
 *
 * Capped at 50 entries. Stored non-autoloaded so it never rides along on
 * every page load.
 */
function brooks_ess_log_404() {
	if ( ! brooks_ess_get( 'log_404' ) ) {
		return;
	}

	$request = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
	$path    = brooks_ess_normalize_path( $request );

	if ( '/' === $path || brooks_ess_is_noise( $path ) ) {
		return;
	}

	$log = get_option( BROOKS_ESS_404_LOG, array() );
	if ( ! is_array( $log ) ) {
		$log = array();
	}

	if ( isset( $log[ $path ] ) && is_array( $log[ $path ] ) ) {
		$log[ $path ]['hits'] = (int) $log[ $path ]['hits'] + 1;
		$log[ $path ]['last'] = time();
	} else {
		// Only start tracking a new path if there is room.
		if ( count( $log ) >= 50 ) {
			return;
		}
		$log[ $path ] = array(
			'hits'    => 1,
			'last'    => time(),
			'referer' => isset( $_SERVER['HTTP_REFERER'] ) ? esc_url_raw( wp_unslash( $_SERVER['HTTP_REFERER'] ) ) : '',
		);
	}

	update_option( BROOKS_ESS_404_LOG, $log, false );
}

/**
 * Trust the hosts an administrator actually typed into the redirect box.
 *
 * wp_safe_redirect() refuses off-site destinations by default, which is the
 * right default — it stops a crafted request from turning the site into an
 * open redirect. But a destination in the settings box was entered by an
 * administrator, so allow those specific hosts and nothing else.
 *
 * @param array $hosts Allowed hosts.
 * @return array
 */
function brooks_ess_allowed_redirect_hosts( $hosts ) {
	foreach ( brooks_ess_redirect_map() as $target ) {
		$host = wp_parse_url( $target, PHP_URL_HOST );
		if ( $host ) {
			$hosts[] = $host;
		}
	}

	return array_unique( $hosts );
}
add_filter( 'allowed_redirect_hosts', 'brooks_ess_allowed_redirect_hosts' );

/**
 * Handle the "clear log" button.
 */
function brooks_ess_handle_clear_log() {
	if ( ! isset( $_POST['brooks_ess_clear_log'] ) ) {
		return;
	}

	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'You do not have permission to do that.', 'docket-suite' ) );
	}

	check_admin_referer( 'brooks_ess_clear_log' );

	delete_option( BROOKS_ESS_404_LOG );

	wp_safe_redirect( add_query_arg( 'brooks_ess_cleared', '1', admin_url( 'options-general.php?page=brooks-essentials' ) ) );
	exit;
}
add_action( 'admin_init', 'brooks_ess_handle_clear_log' );
