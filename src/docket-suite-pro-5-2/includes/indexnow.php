<?php
/**
 * IndexNow submission.
 *
 * WHY THIS EXISTS
 * ---------------
 * Yoast Premium pings IndexNow whenever content is published or updated
 * (its `_yoast_indexnow_last_ping` post meta is visible across this site).
 * Retiring Yoast without replacing that would quietly remove the one
 * push-based discovery channel the site has.
 *
 * IndexNow is a shared protocol: a single submission notifies Bing, Yandex,
 * Seznam and Naver at once. That matters more than it sounds for this site —
 * Bing's index is what Copilot, DuckDuckGo and several AI assistants read
 * from, so IndexNow is effectively the fastest path from "page published" to
 * "AI assistant can cite it".
 *
 * Google does NOT participate in IndexNow. Google discovery stays with the
 * sitemap and Search Console, exactly as it is today. Nothing here changes
 * Google's behaviour in either direction.
 *
 * HOW IT WORKS
 * ------------
 *   - A random 32-character key is generated once and stored.
 *   - The key is served at /<key>.txt as plain text, which is how the
 *     protocol verifies that whoever submits URLs controls the domain.
 *     No file is written to disk — it is answered from a rewrite rule, so
 *     nothing to clean up later and nothing for a file scanner to flag.
 *   - On publish or update of a public post or page, that single URL is
 *     submitted. Submissions are deduplicated per URL with a short
 *     transient so a burst of saves does not become a burst of pings.
 *   - Requests are non-blocking: a slow or unreachable endpoint can never
 *     delay saving a post.
 *   - Drafts, private posts, revisions, autosaves, attachments and anything
 *     marked noindex are never submitted.
 *
 * Off by default. Enable it on the Docket SEO settings screen after Yoast is
 * deactivated — running both at once would double-submit.
 *
 * @package Docket_Suite
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Option holding the generated key. */
if ( ! defined( 'DOCKET_INDEXNOW_KEY' ) ) {
	define( 'DOCKET_INDEXNOW_KEY', 'docket_indexnow_key' );
}

/** Endpoint. Any participating engine forwards to the others. */
if ( ! defined( 'DOCKET_INDEXNOW_ENDPOINT' ) ) {
	define( 'DOCKET_INDEXNOW_ENDPOINT', 'https://api.indexnow.org/indexnow' );
}

/**
 * Is the feature switched on?
 *
 * Also returns false whenever another SEO plugin is active, so that enabling
 * it early — or forgetting to disable Yoast — cannot produce double pings.
 *
 * @return bool
 */
function docket_indexnow_enabled() {
	if ( defined( 'WPSEO_VERSION' ) || defined( 'RANK_MATH_VERSION' ) || defined( 'AIOSEO_VERSION' ) ) {
		return false;
	}

	$opts = (array) get_option( 'docket_seo_options', array() );

	return ! empty( $opts['indexnow'] );
}

/**
 * Get the site's IndexNow key, generating one on first use.
 *
 * @return string 32 hex characters.
 */
function docket_indexnow_key() {
	$key = (string) get_option( DOCKET_INDEXNOW_KEY, '' );

	if ( 32 !== strlen( $key ) || ! ctype_xdigit( $key ) ) {
		$key = bin2hex( random_bytes( 16 ) );
		update_option( DOCKET_INDEXNOW_KEY, $key, false );
	}

	return $key;
}

/**
 * Serve the key verification file at /<key>.txt without writing to disk.
 */
function docket_indexnow_serve_key_file() {
	if ( ! docket_indexnow_enabled() ) {
		return;
	}

	$path = wp_parse_url( isset( $_SERVER['REQUEST_URI'] ) ? esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '', PHP_URL_PATH );
	$path = trim( (string) $path, '/' );

	if ( '' === $path || '.txt' !== substr( $path, -4 ) ) {
		return;
	}

	$key = docket_indexnow_key();

	if ( $key . '.txt' !== $path ) {
		return;
	}

	if ( ! headers_sent() ) {
		status_header( 200 );
		nocache_headers();
		header( 'Content-Type: text/plain; charset=UTF-8' );
		header( 'X-Robots-Tag: noindex' );
	}
	echo esc_html( $key );
	exit;
}
add_action( 'template_redirect', 'docket_indexnow_serve_key_file', 0 );

/**
 * Submit a single URL to IndexNow.
 *
 * @param string $url Absolute URL on this host.
 * @return bool True when a request was dispatched.
 */
function docket_indexnow_submit( $url ) {
	$url = esc_url_raw( (string) $url );

	if ( '' === $url ) {
		return false;
	}

	$host = wp_parse_url( home_url( '/' ), PHP_URL_HOST );
	if ( wp_parse_url( $url, PHP_URL_HOST ) !== $host ) {
		return false; // Never submit a URL on a host we do not control.
	}

	// Debounce: one submission per URL per hour is plenty.
	$guard = 'docket_indexnow_' . md5( $url );
	if ( get_transient( $guard ) ) {
		return false;
	}
	set_transient( $guard, 1, HOUR_IN_SECONDS );

	$body = wp_json_encode(
		array(
			'host'        => $host,
			'key'         => docket_indexnow_key(),
			'keyLocation' => home_url( '/' . docket_indexnow_key() . '.txt' ),
			'urlList'     => array( $url ),
		)
	);

	wp_remote_post(
		DOCKET_INDEXNOW_ENDPOINT,
		array(
			'blocking' => false, // Never delay a post save on a network call.
			'timeout'  => 5,
			'headers'  => array( 'Content-Type' => 'application/json; charset=utf-8' ),
			'body'     => $body,
		)
	);

	update_option( 'docket_indexnow_last', current_time( 'mysql' ), false );

	return true;
}

/**
 * Submit on publish or update of a public page or post.
 *
 * @param int     $post_id Post ID.
 * @param WP_Post $post    Post object.
 */
function docket_indexnow_on_save( $post_id, $post ) {
	if ( ! docket_indexnow_enabled() ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( wp_is_post_revision( $post_id ) ) {
		return;
	}
	if ( ! $post instanceof WP_Post ) {
		return;
	}
	if ( 'publish' !== $post->post_status ) {
		return;
	}
	if ( ! in_array( $post->post_type, array( 'page', 'post' ), true ) ) {
		return;
	}
	if ( '' !== $post->post_password ) {
		return;
	}

	// Respect noindex, whether set on the Docket field or inherited from Yoast.
	if ( class_exists( 'Docket_SEO' ) && Docket_SEO::instance()->is_noindexed( $post_id ) ) {
		return;
	}

	$url = get_permalink( $post );
	if ( $url ) {
		docket_indexnow_submit( $url );
	}
}
add_action( 'save_post', 'docket_indexnow_on_save', 20, 2 );
