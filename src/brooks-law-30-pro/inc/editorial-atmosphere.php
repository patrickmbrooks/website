<?php
/**
 * Brooks Law 4.8 — Atmosphere.
 *
 * The mocha ground with the cloud scene drifting behind it, made available to
 * any page as a switch rather than as pasted markup. The scene cross-fades
 * from storm to clear as the visitor scrolls, which is the behaviour the
 * editorial pages already had.
 *
 * Distinct from the editorial layout: that restyles content and expects its
 * own markup. This only changes the ground the page sits on, so ordinary
 * Gutenberg content works on top of it unchanged.
 *
 * @package Brooks_Law
 * @since   4.8.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Is the atmosphere on for this request?
 *
 * Never on a page that already carries the editorial layout — that has its own
 * scene, and two would stack.
 *
 * @return bool
 */
function brooks_law_atmos_active() {
	static $active = null;

	if ( null !== $active ) {
		return $active;
	}

	$active = false;

	if ( is_singular() && brooks_law_get_option( 'atmos_enable' ) ) {
		$post = get_post();

		if ( $post instanceof WP_Post
			&& 'on' === get_post_meta( $post->ID, '_br_atmos', true )
			&& ! ( function_exists( 'brooks_law_edpage_has_own_layout' ) && brooks_law_edpage_has_own_layout( $post ) )
		) {
			$active = true;
		}
	}

	/**
	 * Filter whether the atmosphere renders.
	 *
	 * @param bool $active Result.
	 */
	return (bool) apply_filters( 'brooks_law_atmos_active', $active );
}

/**
 * Body class.
 *
 * @param string[] $classes Classes.
 * @return string[]
 */
function brooks_law_atmos_body_class( $classes ) {
	if ( brooks_law_atmos_active() ) {
		$classes[] = 'blf-atmos';
	}

	return $classes;
}
add_filter( 'body_class', 'brooks_law_atmos_body_class' );

/**
 * Print the scene behind everything.
 */
function brooks_law_atmos_render() {
	if ( ! brooks_law_atmos_active() ) {
		return;
	}
	if ( ! function_exists( 'brooks_law_edpage_scene' ) ) {
		return;
	}

	$scene = brooks_law_edpage_scene();

	// Reuse only the weather pair; the river layers belong to the editorial
	// layout's own sticky sky and would sit oddly on an ordinary page.
	if ( preg_match( '/<div class="wx"[^>]*>.*?<\/div><\/div>/s', $scene, $m ) ) {
		$inner = preg_replace( '/^<div class="wx"[^>]*><div class="scene">|<\/div><\/div>$/', '', $m[0] );

		echo '<div class="blf-atmos-scene" aria-hidden="true">' . $inner . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput -- static SVG from the theme.
	}
}
add_action( 'wp_body_open', 'brooks_law_atmos_render', 5 );

/**
 * Script that drives the storm-to-clear fade.
 */
function brooks_law_atmos_enqueue_scroll_driver() {
	$path = get_template_directory() . '/assets/js/atmosphere.js';

	wp_enqueue_script(
		'brooks-law-atmosphere',
		get_template_directory_uri() . '/assets/js/atmosphere.js',
		array(),
		file_exists( $path ) ? (string) filemtime( $path ) : BROOKS_LAW_VERSION,
		array(
			'in_footer' => true,
			'strategy'  => 'defer',
		)
	);
}

/**
 * Enqueue it for the per-page Atmosphere option.
 *
 * The editorial homepage style needs the same script and used to enqueue it
 * again under the same handle from inc/home-style.php. WordPress silently
 * ignores the second registration, so it worked — but the two argument lists
 * could drift apart with no error, and whichever ran first would win. There
 * is one registration function now, called from both places.
 */
function brooks_law_atmos_assets() {
	if ( ! brooks_law_atmos_active() ) {
		return;
	}

	brooks_law_atmos_enqueue_scroll_driver();
}
add_action( 'wp_enqueue_scripts', 'brooks_law_atmos_assets', 20 );

/**
 * Customizer switch.
 *
 * @param WP_Customize_Manager $wp_customize Manager.
 */
function brooks_law_atmos_customize( $wp_customize ) {
	$wp_customize->add_setting(
		'atmos_enable',
		array(
			'default'           => true,
			'sanitize_callback' => 'brooks_law_sanitize_checkbox',
		)
	);
	$wp_customize->add_control(
		'atmos_enable',
		array(
			'section'     => 'brooks_law_edpage',
			'label'       => __( 'Allow the atmosphere option', 'brooks-law-30-pro' ),
			'description' => __( 'Lets individual pages switch on the mocha ground with the drifting cloud scene. Turn each page on in the Atmosphere box on its edit screen.', 'brooks-law-30-pro' ),
			'type'        => 'checkbox',
		)
	);
}
add_action( 'customize_register', 'brooks_law_atmos_customize', 28 );

/**
 * Per-page box.
 */
function brooks_law_atmos_meta() {
	foreach ( array( 'page', 'post' ) as $type ) {
		add_meta_box( 'brooks_atmos', __( 'Atmosphere', 'brooks-law-30-pro' ), 'brooks_law_atmos_meta_html', $type, 'side' );
	}
}
add_action( 'add_meta_boxes', 'brooks_law_atmos_meta' );

/**
 * Render the box.
 *
 * @param WP_Post $post Post.
 */
function brooks_law_atmos_meta_html( $post ) {
	wp_nonce_field( 'brooks_atmos_save', 'brooks_atmos_nonce' );

	if ( function_exists( 'brooks_law_edpage_has_own_layout' ) && brooks_law_edpage_has_own_layout( $post ) ) {
		echo '<p>' . esc_html__( 'This page already has its own background scene, so the atmosphere is not offered here.', 'brooks-law-30-pro' ) . '</p>';
		return;
	}

	$on = ( 'on' === get_post_meta( $post->ID, '_br_atmos', true ) );

	echo '<p><label><input type="checkbox" name="br_atmos" value="on" ' . checked( true, $on, false ) . '> ';
	echo esc_html__( 'Mocha ground with drifting clouds', 'brooks-law-30-pro' ) . '</label></p>';
	echo '<p class="description">' . esc_html__( 'The scene clears from storm to sun as the visitor scrolls. Pair it with the Editorial block styles for the floating boxes.', 'brooks-law-30-pro' ) . '</p>';
}

/**
 * Save.
 *
 * @param int $post_id Post.
 */
function brooks_law_atmos_save( $post_id ) {
	if ( ! isset( $_POST['brooks_atmos_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['brooks_atmos_nonce'] ), 'brooks_atmos_save' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	if ( isset( $_POST['br_atmos'] ) && 'on' === $_POST['br_atmos'] ) {
		update_post_meta( $post_id, '_br_atmos', 'on' );
	} else {
		delete_post_meta( $post_id, '_br_atmos' );
	}
}
add_action( 'save_post', 'brooks_law_atmos_save' );
