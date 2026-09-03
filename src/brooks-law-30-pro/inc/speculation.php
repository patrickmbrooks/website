<?php
/**
 * Brooks Law 5.0 — Speculation Rules.
 *
 * Tells the browser to prefetch a same-origin page when the visitor's pointer
 * settles on a link, so the next page is usually already in memory when they
 * click. On a site where people move from a practice-area page to a cost page
 * to a contact page, this is the cheapest perceived-speed improvement
 * available: no build step, no service worker, roughly fifteen lines.
 *
 * Deliberately conservative:
 *
 *   - "prefetch", not "prerender". Prerendering executes scripts and fires
 *     analytics on pages the visitor may never open, which would corrupt
 *     Search Console and GA numbers for a site whose traffic reporting
 *     matters.
 *   - "moderate" eagerness, so nothing is fetched until intent is shown.
 *   - wp-admin, wp-login, feeds, and anything with a query string are
 *     excluded, so no logged-in or one-shot URL is ever fetched early.
 *
 * WordPress 6.8 ships its own speculative loading. If that is present this
 * stands down rather than emitting a second, competing rule set.
 *
 * @package Brooks_Law
 * @since   5.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Should the theme emit its own rules?
 *
 * @return bool
 */
function brooks_law_speculation_active() {
	if ( is_admin() || is_user_logged_in() ) {
		return false;
	}
	if ( ! brooks_law_get_option( 'speculation' ) ) {
		return false;
	}

	// Core handles this from 6.8; do not emit a competing rule set.
	if ( function_exists( 'wp_get_speculation_rules' ) ) {
		return false;
	}

	return true;
}

/**
 * Print the rules.
 */
function brooks_law_speculation_render() {
	if ( ! brooks_law_speculation_active() ) {
		return;
	}

	$rules = array(
		'prefetch' => array(
			array(
				'source'    => 'document',
				'where'     => array(
					'and' => array(
						array( 'href_matches' => '/*' ),
						array( 'not' => array( 'href_matches' => '/wp-admin/*' ) ),
						array( 'not' => array( 'href_matches' => '/wp-login.php*' ) ),
						array( 'not' => array( 'href_matches' => '/*\\?*' ) ),
						array( 'not' => array( 'href_matches' => '/feed/*' ) ),
						array( 'not' => array( 'selector_matches' => '.no-prefetch' ) ),
						array( 'not' => array( 'selector_matches' => '[download]' ) ),
					),
				),
				'eagerness' => 'moderate',
			),
		),
	);

	/**
	 * Filter the speculation rules.
	 *
	 * @param array $rules Rule set.
	 */
	$rules = apply_filters( 'brooks_law_speculation_rules', $rules );

	echo '<script type="speculationrules">' . wp_json_encode( $rules ) . '</script>' . "\n";
}
add_action( 'wp_footer', 'brooks_law_speculation_render', 5 );

/**
 * Customizer switch.
 *
 * @param WP_Customize_Manager $wp_customize Manager.
 */
function brooks_law_speculation_customize( $wp_customize ) {
	$wp_customize->add_setting(
		'speculation',
		array(
			'default'           => true,
			'sanitize_callback' => 'brooks_law_sanitize_checkbox',
		)
	);
	$wp_customize->add_control(
		'speculation',
		array(
			'section'     => 'brooks_law_content_extras',
			'label'       => __( 'Prefetch links on hover', 'brooks-law-30-pro' ),
			'description' => __( 'Loads the next page in the background once a visitor hovers a link, so it appears immediately when clicked. Prefetch only — pages are never executed in advance, so analytics stay accurate. Ignored automatically if WordPress is handling this itself.', 'brooks-law-30-pro' ),
			'type'        => 'checkbox',
		)
	);
}
add_action( 'customize_register', 'brooks_law_speculation_customize', 31 );
