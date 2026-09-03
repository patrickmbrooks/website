<?php
/**
 * Brooks Law 5.0 — Component asset loader.
 *
 * The editorial component stylesheets used to load on every request. Together
 * that was ~22KB on pages that used none of it — most of the site. This works
 * out whether a request can actually show a component and skips the download
 * when it cannot.
 *
 * The test is deliberately generous. Every component class this theme adds
 * begins "is-style-brooks-" or is one of a small set of wrapper classes, so a
 * single substring check over the rendered content catches all of them,
 * including blocks arriving from a reusable block or a pattern. When in any
 * doubt the answer is yes — an unnecessary 22KB is a minor cost, an unstyled
 * component is a visible bug.
 *
 * @package Brooks_Law
 * @since   5.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Enqueue the shared token sheet once.
 */
function brooks_law_enqueue_tokens() {
	if ( wp_style_is( 'brooks-law-tokens', 'enqueued' ) ) {
		return;
	}

	$path = get_template_directory() . '/assets/css/tokens.css';

	wp_enqueue_style(
		'brooks-law-tokens',
		get_template_directory_uri() . '/assets/css/tokens.css',
		array( 'brooks-law-style' ),
		file_exists( $path ) ? (string) filemtime( $path ) : BROOKS_LAW_VERSION
	);
}

/**
 * Could this request display an editorial component?
 *
 * @return bool
 */
function brooks_law_components_needed() {
	static $needed = null;

	if ( null !== $needed ) {
		return $needed;
	}

	$needed = false;

	// Anything with the editorial layer, atmosphere, or editorial homepage
	// active will use the palette and components.
	if ( function_exists( 'brooks_law_edpage_active' ) && brooks_law_edpage_active() ) {
		$needed = true;
	} elseif ( function_exists( 'brooks_law_atmos_active' ) && brooks_law_atmos_active() ) {
		$needed = true;
	} elseif ( function_exists( 'brooks_law_home_style_editorial' ) && brooks_law_home_style_editorial() ) {
		$needed = true;
	} elseif ( brooks_law_get_option( 'post_nav' ) && is_singular( 'post' ) ) {
		$needed = true;
	} elseif ( brooks_law_get_option( 'back_to_top' ) ) {
		$needed = true;
	} elseif ( is_singular() ) {
		$post = get_post();

		if ( $post instanceof WP_Post ) {
			$markers = array( 'is-style-brooks-', 'brooks-meta', 'brooks-arrow', 'blf-', 'pb-' );

			foreach ( $markers as $marker ) {
				if ( false !== strpos( (string) $post->post_content, $marker ) ) {
					$needed = true;
					break;
				}
			}
		}
	}

	/**
	 * Filter whether component styles load for this request.
	 *
	 * @param bool $needed Result.
	 */
	return $needed = (bool) apply_filters( 'brooks_law_components_needed', $needed );
}
