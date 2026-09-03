<?php
/**
 * Brooks Law 4.7 — Editorial block styles.
 *
 * The dark umber panels, cards, and buttons that make the editorial pages
 * distinctive, registered as block styles so they can be applied to any block
 * on any page from the editor's Styles panel.
 *
 * This is deliberately not the .blfE wrapper. That wrapper restyles a whole
 * page and expects hand-written markup underneath it; these are components a
 * person selects on a Group, a Quote, or a Button while writing normally. The
 * palette is declared on the style classes themselves, so they work on a page
 * that has no editorial layout at all.
 *
 * @package Brooks_Law
 * @since   4.7.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The styles to register, keyed by block.
 *
 * @return array[] block => array of name/label.
 */
function brooks_law_block_styles() {
	return apply_filters(
		'brooks_law_block_styles',
		array(
			'core/group'   => array(
				array( 'brooks-panel', __( 'Editorial panel', 'brooks-law-30-pro' ) ),
				array( 'brooks-card', __( 'Editorial card', 'brooks-law-30-pro' ) ),
				array( 'brooks-statement', __( 'Editorial statement', 'brooks-law-30-pro' ) ),
			),
			'core/cover'   => array(
				array( 'brooks-statement', __( 'Editorial statement', 'brooks-law-30-pro' ) ),
			),
			'core/column'  => array(
				array( 'brooks-card', __( 'Editorial card', 'brooks-law-30-pro' ) ),
			),
			'core/columns' => array(
				array( 'brooks-panel', __( 'Editorial panel', 'brooks-law-30-pro' ) ),
			),
			'core/quote'   => array(
				array( 'brooks-quote', __( 'Editorial quote', 'brooks-law-30-pro' ) ),
			),
			'core/button'  => array(
				array( 'brooks-button', __( 'Editorial button', 'brooks-law-30-pro' ) ),
			),
			'core/paragraph' => array(
				array( 'brooks-filebar', __( 'Case file bar', 'brooks-law-30-pro' ) ),
			),
			'core/heading' => array(
				array( 'brooks-heading', __( 'Editorial heading', 'brooks-law-30-pro' ) ),
			),
			'core/media-text' => array(
				array( 'brooks-panel', __( 'Editorial panel', 'brooks-law-30-pro' ) ),
			),
		)
	);
}

/**
 * Register them.
 */
function brooks_law_register_block_styles() {
	if ( ! function_exists( 'register_block_style' ) ) {
		return;
	}

	foreach ( brooks_law_block_styles() as $block => $styles ) {
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
add_action( 'init', 'brooks_law_register_block_styles' );

/**
 * Front-end stylesheet.
 *
 * Small enough, and used widely enough once these are in play, that loading it
 * everywhere is cheaper than working out per-page whether it is needed.
 */
function brooks_law_block_styles_assets() {
	if ( ! brooks_law_components_needed() ) {
		return;
	}

	brooks_law_enqueue_tokens();

	$path = get_template_directory() . '/assets/css/editorial-blocks.css';

	wp_enqueue_style(
		'brooks-law-blocks',
		get_template_directory_uri() . '/assets/css/editorial-blocks.css',
		array( 'brooks-law-tokens' ),
		file_exists( $path ) ? (string) filemtime( $path ) : BROOKS_LAW_VERSION
	);
}
add_action( 'wp_enqueue_scripts', 'brooks_law_block_styles_assets', 16 );

/**
 * Same stylesheet inside the editor, so the boxes look right while writing.
 */
function brooks_law_block_styles_editor() {
	add_editor_style( 'assets/css/editorial-blocks.css' );
}
add_action( 'after_setup_theme', 'brooks_law_block_styles_editor', 11 );
