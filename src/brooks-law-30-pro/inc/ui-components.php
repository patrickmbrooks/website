<?php
/**
 * Brooks Law 4.12 — UI components.
 *
 * Four patterns the federal design system uses that this theme did not have:
 *
 *   - a numbered process list, for the sequential "what happens next"
 *     explanations that run through most of these pages;
 *   - a callout box, so a deadline or statutory warning does not read as an
 *     ordinary paragraph;
 *   - previous/next post navigation, which adds two contextual internal links
 *     to every post and costs nothing to maintain;
 *   - a return-to-top control, since these pages are long.
 *
 * The first two are block styles, so they are applied from the editor's Styles
 * panel. The last two are template output, off by default and switched on in
 * Customizer > Content Extras — nothing about the site changes on upload.
 *
 * @package Brooks_Law
 * @since   4.12.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register the two block styles.
 *
 * Named brooks_law_register_ui_styles to avoid the theme's existing
 * brooks_law_register_block_styles() in inc/editorial-blocks.php.
 */
function brooks_law_register_ui_styles() {
	if ( ! function_exists( 'register_block_style' ) ) {
		return;
	}

	$map = array(
		'core/list'      => array( array( 'brooks-steps', __( 'Numbered steps', 'brooks-law-30-pro' ) ) ),
		'core/group'     => array(
			array( 'brooks-steps', __( 'Numbered steps', 'brooks-law-30-pro' ) ),
			array( 'brooks-callout', __( 'Callout', 'brooks-law-30-pro' ) ),
		),
		'core/paragraph' => array( array( 'brooks-callout', __( 'Callout', 'brooks-law-30-pro' ) ) ),
	);

	foreach ( $map as $block => $styles ) {
		foreach ( $styles as $style ) {
			register_block_style(
				$block,
				array(
					'name'  => $style[0],
					'label' => $style[1],
				)
			);
		}
	}
}
add_action( 'init', 'brooks_law_register_ui_styles' );

/**
 * Front-end and editor stylesheet.
 */
function brooks_law_ui_assets() {
	if ( ! brooks_law_components_needed() ) {
		return;
	}

	brooks_law_enqueue_tokens();

	$path = get_template_directory() . '/assets/css/ui-components.css';

	wp_enqueue_style(
		'brooks-law-ui',
		get_template_directory_uri() . '/assets/css/ui-components.css',
		array( 'brooks-law-style', 'brooks-law-tokens' ),
		file_exists( $path ) ? (string) filemtime( $path ) : BROOKS_LAW_VERSION
	);
}
add_action( 'wp_enqueue_scripts', 'brooks_law_ui_assets', 17 );

/**
 * Same stylesheet in the editor.
 */
function brooks_law_ui_editor_styles() {
	add_editor_style( 'assets/css/ui-components.css' );
}
add_action( 'after_setup_theme', 'brooks_law_ui_editor_styles', 12 );

/**
 * Previous / next post navigation.
 *
 * Posts only, and only when both the option is on and neighbours exist, so a
 * lone post never renders an empty block.
 */
function brooks_law_post_nav_render() {
	if ( ! is_singular( 'post' ) || ! brooks_law_get_option( 'post_nav' ) ) {
		return;
	}

	$prev = get_previous_post();
	$next = get_next_post();

	if ( ! $prev && ! $next ) {
		return;
	}

	echo '<nav class="blf-postnav" aria-label="' . esc_attr__( 'More reading', 'brooks-law-30-pro' ) . '"><ul>';

	if ( $prev instanceof WP_Post ) {
		echo '<li class="blf-postnav-prev"><span class="blf-postnav-label">' . esc_html__( 'Previous', 'brooks-law-30-pro' ) . '</span>';
		echo '<a href="' . esc_url( get_permalink( $prev ) ) . '" rel="prev">' . esc_html( get_the_title( $prev ) ) . '</a></li>';
	}

	if ( $next instanceof WP_Post ) {
		echo '<li class="blf-postnav-next"><span class="blf-postnav-label">' . esc_html__( 'Next', 'brooks-law-30-pro' ) . '</span>';
		echo '<a href="' . esc_url( get_permalink( $next ) ) . '" rel="next">' . esc_html( get_the_title( $next ) ) . '</a></li>';
	}

	echo '</ul></nav>';
}

/**
 * Return-to-top control.
 *
 * A plain anchor to the existing #content target, so it works with JavaScript
 * disabled; the script only handles the show-on-scroll behaviour.
 */
function brooks_law_backtotop_render() {
	if ( ! brooks_law_get_option( 'back_to_top' ) || is_admin() ) {
		return;
	}

	echo '<a class="blf-backtotop" href="#top" data-blf-top>';
	echo '<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path fill="currentColor" d="M12 5l7 7-1.4 1.4L13 8.8V20h-2V8.8l-4.6 4.6L5 12z"/></svg>';
	echo '<span>' . esc_html__( 'Top', 'brooks-law-30-pro' ) . '</span>';
	echo '</a>';
}
add_action( 'wp_footer', 'brooks_law_backtotop_render', 20 );

/**
 * Script for the return-to-top control.
 */
function brooks_law_backtotop_assets() {
	if ( ! brooks_law_get_option( 'back_to_top' ) ) {
		return;
	}

	$path = get_template_directory() . '/assets/js/backtotop.js';

	wp_enqueue_script(
		'brooks-law-backtotop',
		get_template_directory_uri() . '/assets/js/backtotop.js',
		array(),
		file_exists( $path ) ? (string) filemtime( $path ) : BROOKS_LAW_VERSION,
		array(
			'in_footer' => true,
			'strategy'  => 'defer',
		)
	);
}
add_action( 'wp_enqueue_scripts', 'brooks_law_backtotop_assets', 22 );

/**
 * Customizer switches.
 *
 * @param WP_Customize_Manager $wp_customize Manager.
 */
function brooks_law_ui_customize( $wp_customize ) {
	$wp_customize->add_section(
		'brooks_law_content_extras',
		array(
			'title'       => __( 'Content Extras', 'brooks-law-30-pro' ),
			'description' => __( 'Two small additions borrowed from federal agency sites. Both are off until switched on here.', 'brooks-law-30-pro' ),
			'priority'    => 135,
		)
	);

	$wp_customize->add_setting(
		'post_nav',
		array(
			'default'           => false,
			'sanitize_callback' => 'brooks_law_sanitize_checkbox',
		)
	);
	$wp_customize->add_control(
		'post_nav',
		array(
			'section'     => 'brooks_law_content_extras',
			'label'       => __( 'Previous / next links on posts', 'brooks-law-30-pro' ),
			'description' => __( 'Adds two contextual internal links to the bottom of every post. Pages are unaffected.', 'brooks-law-30-pro' ),
			'type'        => 'checkbox',
		)
	);

	$wp_customize->add_setting(
		'back_to_top',
		array(
			'default'           => false,
			'sanitize_callback' => 'brooks_law_sanitize_checkbox',
		)
	);
	$wp_customize->add_control(
		'back_to_top',
		array(
			'section'     => 'brooks_law_content_extras',
			'label'       => __( 'Return-to-top button', 'brooks-law-30-pro' ),
			'description' => __( 'Appears after the visitor scrolls. Sits above the sticky contact bar on phones.', 'brooks-law-30-pro' ),
			'type'        => 'checkbox',
		)
	);
}
add_action( 'customize_register', 'brooks_law_ui_customize', 30 );
