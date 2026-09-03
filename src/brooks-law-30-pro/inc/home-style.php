<?php
/**
 * Brooks Law 4.11 — Homepage style switcher.
 *
 * One Customizer select that reskins the front page between looks. Classic is
 * today's navy-and-limestone and remains the default, so activating this
 * version changes nothing until a choice is made. Editorial re-grounds the
 * homepage on paper cream with the faint cloud scene behind it and restyles
 * every section — hero, tiles, cards, bands — into the umber language, purely
 * with CSS on a body class. No template is edited, so switching back is
 * instant and lossless.
 *
 * The choices array is filterable so a third style can be added later without
 * touching this file.
 *
 * @package Brooks_Law
 * @since   4.11.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Available styles.
 *
 * @return array slug => label.
 */
function brooks_law_home_style_choices() {
	return apply_filters(
		'brooks_law_home_style_choices',
		array(
			'classic'   => __( 'Classic (navy and limestone)', 'brooks-law-30-pro' ),
			'editorial' => __( 'Editorial (paper, umber, cloud scene)', 'brooks-law-30-pro' ),
		)
	);
}

/**
 * Whitelist the choice.
 *
 * @param string $value Candidate.
 * @return string
 */
function brooks_law_sanitize_home_style( $value ) {
	return array_key_exists( $value, brooks_law_home_style_choices() ) ? $value : 'classic';
}

/**
 * Current style, only meaningful on the front page.
 *
 * @return string
 */
function brooks_law_home_style() {
	return brooks_law_sanitize_home_style( (string) brooks_law_get_option( 'home_style' ) );
}

/**
 * Is the editorial homepage active for this request?
 *
 * @return bool
 */
function brooks_law_home_style_editorial() {
	return is_front_page() && 'editorial' === brooks_law_home_style();
}

/**
 * Body class the stylesheet keys off.
 *
 * @param string[] $classes Classes.
 * @return string[]
 */
function brooks_law_home_style_class( $classes ) {
	if ( brooks_law_home_style_editorial() ) {
		$classes[] = 'blf-home-ed';
	}

	return $classes;
}
add_filter( 'body_class', 'brooks_law_home_style_class' );

/**
 * The faint scene behind everything.
 *
 * Reuses the weather pair from brooks_law_edpage_scene(), the same way the
 * per-page atmosphere does, so there is exactly one copy of the artwork in
 * the codebase.
 */
function brooks_law_home_style_scene() {
	if ( ! brooks_law_home_style_editorial() || ! function_exists( 'brooks_law_edpage_scene' ) ) {
		return;
	}

	if ( preg_match( '/<div class="wx"[^>]*>.*?<\/div><\/div>/s', brooks_law_edpage_scene(), $m ) ) {
		$inner = preg_replace( '/^<div class="wx"[^>]*><div class="scene">|<\/div><\/div>$/', '', $m[0] );

		echo '<div class="blf-atmos-scene" aria-hidden="true">' . $inner . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput -- static SVG from the theme.
	}
}
add_action( 'wp_body_open', 'brooks_law_home_style_scene', 5 );

/**
 * The page-level Atmosphere checkbox stands down here — the style already
 * prints the scene, and two copies would stack.
 *
 * @param bool $active Computed state.
 * @return bool
 */
function brooks_law_home_style_no_double_scene( $active ) {
	return brooks_law_home_style_editorial() ? false : $active;
}
add_filter( 'brooks_law_atmos_active', 'brooks_law_home_style_no_double_scene' );

/**
 * Stylesheet and the scroll driver, only when chosen.
 */
function brooks_law_home_style_assets() {
	if ( ! brooks_law_home_style_editorial() ) {
		return;
	}

	$css = get_template_directory() . '/assets/css/home-editorial.css';

	wp_enqueue_style(
		'brooks-law-home-editorial',
		get_template_directory_uri() . '/assets/css/home-editorial.css',
		array( 'brooks-law-blocks' ),
		file_exists( $css ) ? (string) filemtime( $css ) : BROOKS_LAW_VERSION
	);

	// Shared with the per-page Atmosphere option; one registration, two callers.
	brooks_law_atmos_enqueue_scroll_driver();
}
add_action( 'wp_enqueue_scripts', 'brooks_law_home_style_assets', 21 );

/**
 * Customizer.
 *
 * @param WP_Customize_Manager $wp_customize Manager.
 */
function brooks_law_home_style_customize( $wp_customize ) {
	$wp_customize->add_section(
		'brooks_law_home_style',
		array(
			'title'       => __( 'Homepage Style', 'brooks-law-30-pro' ),
			'description' => __( 'Switches the whole front page between looks. Purely visual: no content, template, or settings change, so moving between them is instant and lossless.', 'brooks-law-30-pro' ),
			'priority'    => 129,
		)
	);

	$wp_customize->add_setting(
		'home_style',
		array(
			'default'           => 'classic',
			'sanitize_callback' => 'brooks_law_sanitize_home_style',
		)
	);
	$wp_customize->add_control(
		'home_style',
		array(
			'section' => 'brooks_law_home_style',
			'label'   => __( 'Style', 'brooks-law-30-pro' ),
			'type'    => 'select',
			'choices' => brooks_law_home_style_choices(),
		)
	);
}
add_action( 'customize_register', 'brooks_law_home_style_customize', 29 );
