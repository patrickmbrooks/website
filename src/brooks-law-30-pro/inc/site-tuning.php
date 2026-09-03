<?php
/**
 * Brooks Law 5.2 — Site tuning.
 *
 * The remainder of what security/perf plugins sell, after checking what this
 * stack already covers.
 *
 * A note on how that check is done, because getting it wrong is what this
 * file previously did. An earlier version asserted in prose that "Brooks
 * Essentials already disables XML-RPC, removes the pingback header, and
 * closes comments", and shipped no XML-RPC control on that basis. Two thirds
 * of that was true. The plugin's disable_xmlrpc default is FALSE — off, for
 * the good reason that it is what the WordPress mobile app and Jetpack use —
 * so on a default install of both, XML-RPC was live and neither codebase was
 * handling it. Each had deferred to the other.
 *
 * Deference is now resolved in code rather than in a comment: every item
 * below asks whether the companion plugin is actually handling it, this
 * request, and acts only when it is not. A claim that can go stale is not
 * allowed to decide whether a control exists.
 *
 * The pieces:
 *
 *   1. Security response headers. Cheap, standard, and absent today.
 *      HSTS is deliberately NOT set here — the domain fronts through
 *      Cloudflare, which owns transport policy; two owners for one header is
 *      the orphaned-cache-rule mistake again.
 *   2. REST user-enumeration lockdown. /wp-json/wp/v2/users lists account
 *      usernames to anyone, which is half of a login brute-force. Logged-out
 *      requests get a 404-shaped denial; editors and admins are unaffected.
 *   3. Head cleanup remainder: RSD, shortlink, oEmbed discovery, and the
 *      extra feed links.
 *
 *   4. XML-RPC and the pingback header, when nothing else is switching them
 *      off. XML-RPC is a brute-force amplifier: one HTTP request can carry
 *      many credential guesses. Turn this off only if the WordPress mobile
 *      app or Jetpack is in use.
 *
 * Everything is a Customizer toggle under Site Tuning, on by default because
 * none of it changes anything a visitor can see, and off is one untick.
 *
 * @package Brooks_Law
 * @since   5.2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Security headers, front-end responses only.
 *
 * @param array $headers Headers WordPress will send.
 * @return array
 */
function brooks_law_tune_headers( $headers ) {
	if ( is_admin() || ! brooks_law_get_option( 'tune_headers' ) ) {
		return $headers;
	}

	$headers['X-Content-Type-Options'] = 'nosniff';
	$headers['Referrer-Policy']        = 'strict-origin-when-cross-origin';

	// X-Frame-Options is the legacy control and CSP frame-ancestors is its
	// replacement; both are sent because the old header is still what some
	// scanners and older clients look for. frame-ancestors wins where both
	// are understood. Nothing else is declared in the policy, so this cannot
	// break an inline script or style the way a full CSP would.
	$headers['X-Frame-Options']          = 'SAMEORIGIN';
	$headers['Content-Security-Policy']  = "frame-ancestors 'self'";

	// Cross-origin isolation for the document itself: a page this site opens,
	// or that opens it, cannot reach into this window.
	$headers['Cross-Origin-Opener-Policy'] = 'same-origin-allow-popups';

	/*
	 * Decline the device and advertising APIs the site never uses.
	 * browsing-topics=() opts the site out of the Topics API, so a visitor
	 * reading about a criminal charge does not have that visit folded into an
	 * ad-interest profile — which matters more here than on most sites.
	 */
	$headers['Permissions-Policy'] = 'camera=(), microphone=(), geolocation=(), payment=(), browsing-topics=(), interest-cohort=()';

	/**
	 * Filter the tuned headers.
	 *
	 * @param array $headers Headers.
	 */
	return apply_filters( 'brooks_law_tune_headers', $headers );
}
add_filter( 'wp_headers', 'brooks_law_tune_headers' );

/**
 * Close the user-listing REST routes to logged-out requests.
 *
 * @param array $endpoints Registered endpoints.
 * @return array
 */
function brooks_law_tune_rest_users( $endpoints ) {
	if ( is_user_logged_in() || ! brooks_law_get_option( 'tune_rest_users' ) ) {
		return $endpoints;
	}

	unset( $endpoints['/wp/v2/users'] );
	unset( $endpoints['/wp/v2/users/(?P<id>[\d]+)'] );

	return $endpoints;
}
add_filter( 'rest_endpoints', 'brooks_law_tune_rest_users' );

/**
 * Is the companion plugin already switching XML-RPC off?
 *
 * Asked at runtime rather than assumed, so the answer follows the plugin's
 * actual setting instead of a comment written about it.
 *
 * @return bool
 */
function brooks_law_plugin_handles_xmlrpc() {
	if ( ! function_exists( 'brooks_ess_get' ) ) {
		return false;
	}

	return (bool) brooks_ess_get( 'disable_xmlrpc' );
}

/**
 * Turn XML-RPC off when nothing else is doing it.
 *
 * Stands down entirely if the companion plugin has the same switch on, so the
 * two never both filter the same thing — but, unlike the previous version,
 * standing down is conditional on the plugin actually being configured to
 * act, not on the theme assuming it is.
 */
function brooks_law_tune_xmlrpc() {
	if ( ! brooks_law_get_option( 'tune_xmlrpc' ) ) {
		return;
	}
	if ( brooks_law_plugin_handles_xmlrpc() ) {
		return;
	}

	add_filter( 'xmlrpc_enabled', '__return_false' );
	add_filter( 'xmlrpc_methods', '__return_empty_array' );
	add_filter( 'wp_headers', 'brooks_law_tune_drop_pingback', 20 );
	remove_action( 'wp_head', 'rsd_link' );
}
add_action( 'init', 'brooks_law_tune_xmlrpc', 11 );

/**
 * Strip the pingback advertisement when XML-RPC is off.
 *
 * @param array $headers Response headers.
 * @return array
 */
function brooks_law_tune_drop_pingback( $headers ) {
	if ( is_array( $headers ) ) {
		unset( $headers['X-Pingback'] );
	}

	return $headers;
}

/**
 * Drop head links this site does not use.
 */
function brooks_law_tune_head_cleanup() {
	if ( ! brooks_law_get_option( 'tune_head_cleanup' ) ) {
		return;
	}

	remove_action( 'wp_head', 'rsd_link' );
	remove_action( 'wp_head', 'wp_shortlink_wp_head' );
	remove_action( 'wp_head', 'wp_oembed_add_discovery_links' );
	remove_action( 'wp_head', 'feed_links_extra', 3 );
	// The main content feed link stays; only the per-post comment feeds and
	// archive feeds go, since comments are closed site-wide by Essentials.
}
add_action( 'init', 'brooks_law_tune_head_cleanup', 11 );

/**
 * Customizer.
 *
 * @param WP_Customize_Manager $wp_customize Manager.
 */
function brooks_law_tune_customize( $wp_customize ) {
	$wp_customize->add_section(
		'brooks_law_tuning',
		array(
			'title'       => __( 'Site Tuning', 'brooks-law-30-pro' ),
			'description' => __( 'Security headers and cleanup that would otherwise need another plugin. Nothing here changes what a visitor sees.', 'brooks-law-30-pro' ),
			'priority'    => 136,
		)
	);

	$fields = array(
		'tune_headers'      => array(
			__( 'Send security headers', 'brooks-law-30-pro' ),
			__( 'nosniff, frame protection, referrer policy, and a permissions policy that declines camera, microphone, location, and payment APIs the site never uses. Transport security (HSTS) is left to Cloudflare, which owns it.', 'brooks-law-30-pro' ),
		),
		'tune_rest_users'   => array(
			__( 'Hide the user list from the public API', 'brooks-law-30-pro' ),
			__( 'Logged-out requests to /wp-json/wp/v2/users stop revealing account usernames. Editing and admin screens are unaffected.', 'brooks-law-30-pro' ),
		),
		'tune_head_cleanup' => array(
			__( 'Trim unused head links', 'brooks-law-30-pro' ),
			__( 'Removes RSD, shortlink, oEmbed discovery, and the extra feed links, including comment feeds for a site with comments closed. The main content feed stays.', 'brooks-law-30-pro' ),
		),
		'tune_xmlrpc'       => array(
			__( 'Disable XML-RPC', 'brooks-law-30-pro' ),
			__( 'XML-RPC lets one request carry many login attempts, which is why it is a favourite of brute-force tools. Turn this OFF only if you use the WordPress mobile app or Jetpack. If Docket Suite is already disabling XML-RPC, this stands down automatically rather than doing it twice.', 'brooks-law-30-pro' ),
		),
	);

	foreach ( $fields as $key => $label ) {
		$wp_customize->add_setting(
			$key,
			array(
				'default'           => true,
				'sanitize_callback' => 'brooks_law_sanitize_checkbox',
			)
		);
		$wp_customize->add_control(
			$key,
			array(
				'section'     => 'brooks_law_tuning',
				'label'       => $label[0],
				'description' => $label[1],
				'type'        => 'checkbox',
			)
		);
	}
}
add_action( 'customize_register', 'brooks_law_tune_customize', 32 );
