<?php
/**
 * Search-engine site verification tags.
 *
 * Bing, Google, Yandex and Pinterest all verify ownership the same three ways:
 * a file at the web root, a DNS record, or a meta tag in the head. The meta tag
 * is the one worth having in a plugin, because the other two mean either giving
 * something filesystem access or leaving the answer in a DNS panel nobody
 * remembers to check.
 *
 * This deliberately does NOT stand down when Yoast, Rank Math or AIOSEO is
 * active, unlike the SEO half in seo.php. Verification tags are not page-level
 * markup and do not conflict: a second, identical meta tag would be redundant
 * but harmless, and an empty field emits nothing at all. Standing down here
 * would instead mean losing verification the moment another plugin is switched
 * off — which is exactly how this site lost its Bing field.
 *
 * @package Docket_Suite
 * @since   5.2.4
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The services this supports, keyed by option name.
 *
 * @return array<string,array{0:string,1:string,2:string}> key => [ label, meta name, help ].
 */
function docket_verify_services() {
	return array(
		'verify_bing'      => array(
			__( 'Bing Webmaster Tools', 'docket-suite' ),
			'msvalidate.01',
			__( 'Bing offers an XML file, a DNS record, or this meta tag. Use this and you never have to put a file in the web root.', 'docket-suite' ),
		),
		'verify_google'    => array(
			__( 'Google Search Console', 'docket-suite' ),
			'google-site-verification',
			__( 'Only needed for the HTML-tag method. If Search Console is already verified by DNS or by your Google account, leave this blank.', 'docket-suite' ),
		),
		'verify_yandex'    => array(
			__( 'Yandex Webmaster', 'docket-suite' ),
			'yandex-verification',
			__( 'Optional. Yandex is one of the engines IndexNow already submits to.', 'docket-suite' ),
		),
		'verify_pinterest' => array(
			__( 'Pinterest', 'docket-suite' ),
			'p:domain_verify',
			__( 'Optional.', 'docket-suite' ),
		),
	);
}

/**
 * Reduce whatever was pasted to the bare token.
 *
 * People paste the whole tag more often than they paste the value, because the
 * whole tag is what every one of these services shows on screen. Accepting both
 * is two lines and removes the most likely way this gets set wrong — a field
 * that silently contains markup and emits a tag inside a tag.
 *
 * @param string $value Raw field value.
 * @return string Bare token.
 */
function docket_verify_sanitize( $value ) {
	$value = trim( (string) $value );

	if ( '' === $value ) {
		return '';
	}

	// A full <meta ... content="TOKEN"> was pasted: take the content attribute.
	if ( false !== stripos( $value, '<meta' ) && preg_match( '/content=["\']([^"\']+)["\']/i', $value, $m ) ) {
		$value = $m[1];
	}

	// Tokens from all four services are URL-safe ASCII. Anything else is a
	// paste accident, and stripping it is better than emitting broken markup.
	$value = preg_replace( '/[^A-Za-z0-9_\-\.:=\/+]/', '', $value );

	return (string) $value;
}

/**
 * Print the tags.
 *
 * Site-wide rather than front-page-only. Every one of these services documents
 * the homepage as the check target, but several have been observed re-checking
 * other URLs later, and the whole set costs well under 300 bytes.
 */
function docket_verify_render() {
	if ( is_feed() || is_robots() ) {
		return;
	}

	foreach ( docket_verify_services() as $key => $service ) {
		$token = docket_verify_sanitize( (string) brooks_ess_get( $key ) );

		if ( '' === $token ) {
			continue;
		}

		printf(
			'<meta name="%1$s" content="%2$s">' . "\n",
			esc_attr( $service[1] ),
			esc_attr( $token )
		);
	}
}
add_action( 'wp_head', 'docket_verify_render', 1 );
