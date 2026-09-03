<?php
/**
 * Brooks Law 4.10 — Editorial block patterns.
 *
 * The three pieces that need structure rather than a single class: the
 * ghost-letter card, the drag-scroll carousel of them, and the photo with the
 * serif caption. Registered as patterns so they appear in the inserter under
 * "Brooks Law" and drop in as ordinary, fully editable core blocks.
 *
 * @package Brooks_Law
 * @since   4.10.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Category + patterns.
 */
function brooks_law_editorial_patterns() {
	if ( ! function_exists( 'register_block_pattern' ) ) {
		return;
	}

	// The 'brooks-law' pattern category is registered by inc/block-editor.php;
	// these patterns file into it.
	$ghost_card = static function ( $letters, $alt, $title, $meta, $body, $link_text ) {
		$alt_class = $alt ? ' is-alt' : '';

		return '<!-- wp:group {"className":"is-style-brooks-ghostcard' . $alt_class . '"} -->
<div class="wp-block-group is-style-brooks-ghostcard' . $alt_class . '"><!-- wp:paragraph {"className":"ghost-letters"} -->
<p class="ghost-letters">' . $letters . '</p>
<!-- /wp:paragraph --><!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading">' . $title . '</h3>
<!-- /wp:heading --><!-- wp:paragraph {"className":"brooks-meta"} -->
<p class="brooks-meta">' . $meta . '</p>
<!-- /wp:paragraph --><!-- wp:paragraph -->
<p>' . $body . '</p>
<!-- /wp:paragraph --><!-- wp:paragraph {"className":"brooks-arrow"} -->
<p class="brooks-arrow"><a href="#">' . $link_text . '</a></p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->';
	};

	register_block_pattern(
		'brooks-law/ghost-card',
		array(
			'title'      => __( 'Editorial ghost card', 'brooks-law-30-pro' ),
			'categories' => array( 'brooks-law' ),
			'content'    => $ghost_card(
				'SUB',
				false,
				__( 'Suburban courts: Germantown, Bartlett, Collierville', 'brooks-law-30-pro' ),
				__( 'Municipal courts · compliance stings', 'brooks-law-30-pro' ),
				__( 'How these cases actually run in the three suburban courts, and what a first offense costs there.', 'brooks-law-30-pro' ),
				__( 'Suburb guide', 'brooks-law-30-pro' )
			),
		)
	);

	register_block_pattern(
		'brooks-law/card-carousel',
		array(
			'title'      => __( 'Editorial card carousel', 'brooks-law-30-pro' ),
			'categories' => array( 'brooks-law' ),
			'content'    => '<!-- wp:group {"className":"is-style-brooks-carousel"} -->
<div class="wp-block-group is-style-brooks-carousel">'
				. $ghost_card( 'SUB', false, __( 'Suburban courts', 'brooks-law-30-pro' ), __( 'Municipal courts · stings', 'brooks-law-30-pro' ), __( 'How these cases run in Germantown, Bartlett, and Collierville.', 'brooks-law-30-pro' ), __( 'Suburb guide', 'brooks-law-30-pro' ) )
				. $ghost_card( 'BRD', true, __( 'Beer board &amp; liquor license', 'brooks-law-30-pro' ), __( 'Local beer board · ABC · civil penalties', 'brooks-law-30-pro' ), __( 'The other track: outcomes and penalties when the permit itself is at stake.', 'brooks-law-30-pro' ), __( 'Licensing defense', 'brooks-law-30-pro' ) )
				. $ghost_card( 'EXP', false, __( 'Clearing the record', 'brooks-law-30-pro' ), __( 'Diversion · expungement', 'brooks-law-30-pro' ), __( 'What can come off a record after a dismissal or diversion, and how long it takes.', 'brooks-law-30-pro' ), __( 'Expungement guide', 'brooks-law-30-pro' ) )
				. '</div>
<!-- /wp:group -->',
		)
	);

	register_block_pattern(
		'brooks-law/photo-caption',
		array(
			'title'      => __( 'Editorial photo with caption', 'brooks-law-30-pro' ),
			'categories' => array( 'brooks-law' ),
			'content'    => '<!-- wp:cover {"dimRatio":0,"minHeight":360,"className":"is-style-brooks-photocap"} -->
<div class="wp-block-cover is-style-brooks-photocap" style="min-height:360px"><span aria-hidden="true" class="wp-block-cover__background has-background-dim-0 has-background-dim"></span><div class="wp-block-cover__inner-container"><!-- wp:paragraph {"className":"brooks-meta"} -->
<p class="brooks-meta">' . esc_html__( 'Compliance checks', 'brooks-law-30-pro' ) . '</p>
<!-- /wp:paragraph --><!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading">' . esc_html__( 'One sale. Two separate cases.', 'brooks-law-30-pro' ) . '</h3>
<!-- /wp:heading --></div></div>
<!-- /wp:cover -->',
		)
	);
}
add_action( 'init', 'brooks_law_editorial_patterns' );

/**
 * Mouse-drag for the carousel, only where one exists.
 */
function brooks_law_editorial_patterns_assets() {
	if ( ! is_singular() ) {
		return;
	}

	$post = get_post();

	if ( ! $post instanceof WP_Post || false === strpos( (string) $post->post_content, 'is-style-brooks-carousel' ) ) {
		return;
	}

	$path = get_template_directory() . '/assets/js/carousel-drag.js';

	wp_enqueue_script(
		'brooks-law-carousel',
		get_template_directory_uri() . '/assets/js/carousel-drag.js',
		array(),
		file_exists( $path ) ? (string) filemtime( $path ) : BROOKS_LAW_VERSION,
		array(
			'in_footer' => true,
			'strategy'  => 'defer',
		)
	);
}
add_action( 'wp_enqueue_scripts', 'brooks_law_editorial_patterns_assets', 20 );
