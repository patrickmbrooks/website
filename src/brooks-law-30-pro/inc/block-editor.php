<?php
/**
 * Block editor — full visual page building (v3.0).
 *
 * Three things this file adds:
 *
 *   1. THEME SUPPORTS — wide/full alignment, editor styles, block
 *      spacing/padding controls, custom line heights, responsive
 *      embeds. Combined with theme.json, every core styling control
 *      (colors, typography, spacing, borders, shadows, duotone) is
 *      switched ON in the editor, using the firm's own palette.
 *
 *   2. EDITOR STYLES — the front-end look inside the editor, so
 *      what you see while editing is what visitors see.
 *
 *   3. BLOCK PATTERNS — one-click, pre-designed sections in the
 *      firm's design language: CTA band, practice-area card grid,
 *      attorney profile, FAQ (schema-ready), consultation banner,
 *      two-column feature. Insert, edit text, done — full layout
 *      freedom with zero page-builder plugin, zero bloat.
 *
 * @package brooks-law
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* -------------------------------------------------------------------------
 * 1. Supports
 * ---------------------------------------------------------------------- */
function brooks_law_block_supports() {
	add_theme_support( 'align-wide' );
	add_theme_support( 'editor-styles' );
	add_theme_support( 'custom-spacing' );
	add_theme_support( 'custom-line-height' );
	add_theme_support( 'custom-units', 'px', 'rem', 'em', '%', 'vw' );
	add_theme_support( 'appearance-tools' );
	add_editor_style( array( 'style.css', 'assets/css/editorial.css', 'assets/css/editor.css' ) );
}
add_action( 'after_setup_theme', 'brooks_law_block_supports', 11 );

/* -------------------------------------------------------------------------
 * 3. Patterns
 * ---------------------------------------------------------------------- */
function brooks_law_register_patterns() {
	if ( ! function_exists( 'register_block_pattern' ) ) {
		return;
	}

	register_block_pattern_category( 'brooks-law', array( 'label' => __( 'Brooks Law Firm', 'brooks-law-30-pro' ) ) );

	$phone      = brooks_law_get_option( 'firm_phone' );
	$phone_link = brooks_law_tel( brooks_law_get_option( 'firm_phone_link', $phone ) );

	/* ---- Call-to-action band ---- */
	/* translators: %s: office phone number as displayed. */
	$cta_call_label = sprintf( __( 'Call %s', 'brooks-law-30-pro' ), $phone );

	register_block_pattern( 'brooks-law/cta-band', array(
		'title'       => __( 'Call-to-action band', 'brooks-law-30-pro' ),
		'categories'  => array( 'brooks-law' ),
		'description' => __( 'Full-width dark band with heading, line, and call button.', 'brooks-law-30-pro' ),
		'content'     => '<!-- wp:group {"align":"full","style":{"color":{"background":"#12202e"},"spacing":{"padding":{"top":"var:preset|spacing|40","bottom":"var:preset|spacing|40","left":"var:preset|spacing|30","right":"var:preset|spacing|30"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull has-background" style="background-color:#12202e;padding-top:var(--wp--preset--spacing--40);padding-right:var(--wp--preset--spacing--30);padding-bottom:var(--wp--preset--spacing--40);padding-left:var(--wp--preset--spacing--30)">
<!-- wp:heading {"textAlign":"center","style":{"color":{"text":"#ffffff"}}} -->
<h2 class="wp-block-heading has-text-align-center has-text-color" style="color:#ffffff">' . esc_html__( 'Charged in Shelby County? Talk to a lawyer first.', 'brooks-law-30-pro' ) . '</h2>
<!-- /wp:heading -->
<!-- wp:paragraph {"align":"center","style":{"color":{"text":"#d9b45c"}}} -->
<p class="has-text-align-center has-text-color" style="color:#d9b45c">' . esc_html__( 'Free initial consultation · Se habla Español', 'brooks-law-30-pro' ) . '</p>
<!-- /wp:paragraph -->
<!-- wp:buttons {"layout":{"type":"flex","justifyContent":"center"}} -->
<div class="wp-block-buttons"><!-- wp:button {"style":{"color":{"background":"#c8a04a","text":"#222933"}}} -->
<div class="wp-block-button"><a class="wp-block-button__link has-text-color has-background wp-element-button" style="color:#222933;background-color:#c8a04a" href="tel:' . esc_attr( $phone_link ) . '">' . esc_html( $cta_call_label ) . '</a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></div>
<!-- /wp:group -->',
	) );

	/* ---- Practice-area card grid ---- */
	$card = '<!-- wp:column {"style":{"color":{"background":"#f5f2ea"},"spacing":{"padding":{"top":"var:preset|spacing|30","bottom":"var:preset|spacing|30","left":"var:preset|spacing|20","right":"var:preset|spacing|20"}},"border":{"radius":"3px"}}} -->
<div class="wp-block-column has-background" style="border-radius:3px;background-color:#f5f2ea;padding-top:var(--wp--preset--spacing--30);padding-right:var(--wp--preset--spacing--20);padding-bottom:var(--wp--preset--spacing--30);padding-left:var(--wp--preset--spacing--20)">
<!-- wp:heading {"level":3} --><h3 class="wp-block-heading">%1$s</h3><!-- /wp:heading -->
<!-- wp:paragraph --><p>%2$s</p><!-- /wp:paragraph -->
<!-- wp:paragraph --><p><a href="%3$s">%4$s</a></p><!-- /wp:paragraph --></div>
<!-- /wp:column -->';

	register_block_pattern( 'brooks-law/practice-grid', array(
		'title'      => __( 'Practice-area card grid', 'brooks-law-30-pro' ),
		'categories' => array( 'brooks-law' ),
		'content'    => '<!-- wp:columns {"align":"wide"} --><div class="wp-block-columns alignwide">'
			. sprintf( $card, esc_html__( 'DUI Defense', 'brooks-law-30-pro' ), esc_html__( 'First-offense through felony DUI, implied consent, and license reinstatement.', 'brooks-law-30-pro' ), esc_url( home_url( '/dui/' ) ), esc_html__( 'Learn more →', 'brooks-law-30-pro' ) )
			. sprintf( $card, esc_html__( 'Drug Charges', 'brooks-law-30-pro' ), esc_html__( 'Possession, casual exchange, and felony drug offenses in Shelby County.', 'brooks-law-30-pro' ), esc_url( home_url( '/drug-offense/' ) ), esc_html__( 'Learn more →', 'brooks-law-30-pro' ) )
			. sprintf( $card, esc_html__( 'Expungement', 'brooks-law-30-pro' ), esc_html__( 'Clearing eligible Tennessee charges from your record.', 'brooks-law-30-pro' ), esc_url( home_url( '/expungement/' ) ), esc_html__( 'Learn more →', 'brooks-law-30-pro' ) )
			. '</div><!-- /wp:columns -->',
	) );

	/* ---- Attorney profile ---- */
	register_block_pattern( 'brooks-law/attorney-profile', array(
		'title'      => __( 'Attorney profile (photo + bio)', 'brooks-law-30-pro' ),
		'categories' => array( 'brooks-law' ),
		'content'    => '<!-- wp:media-text {"mediaWidth":35} --><div class="wp-block-media-text is-stacked-on-mobile" style="grid-template-columns:35% auto"><figure class="wp-block-media-text__media"></figure><div class="wp-block-media-text__content">
<!-- wp:paragraph {"style":{"color":{"text":"#7c2d2d"},"typography":{"textTransform":"uppercase","letterSpacing":"0.08em","fontSize":"0.85rem"}}} --><p class="has-text-color" style="color:#7c2d2d;font-size:0.85rem;letter-spacing:0.08em;text-transform:uppercase">' . esc_html__( 'Attorney', 'brooks-law-30-pro' ) . '</p><!-- /wp:paragraph -->
<!-- wp:heading --><h2 class="wp-block-heading">' . esc_html__( 'Attorney Name', 'brooks-law-30-pro' ) . '</h2><!-- /wp:heading -->
<!-- wp:paragraph --><p>' . esc_html__( 'Two or three sentences: courts practiced in, years of experience, approach to client work. Replace the photo on the left with a portrait.', 'brooks-law-30-pro' ) . '</p><!-- /wp:paragraph --></div></div><!-- /wp:media-text -->',
	) );

	/* ---- FAQ (pairs with FAQPage schema content) ---- */
	register_block_pattern( 'brooks-law/faq', array(
		'title'      => __( 'FAQ section', 'brooks-law-30-pro' ),
		'categories' => array( 'brooks-law' ),
		'content'    => '<!-- wp:heading --><h2 class="wp-block-heading">' . esc_html__( 'Frequently Asked Questions', 'brooks-law-30-pro' ) . '</h2><!-- /wp:heading -->
<!-- wp:details --><details class="wp-block-details"><summary>' . esc_html__( 'What should I do after an arrest in Shelby County?', 'brooks-law-30-pro' ) . '</summary><!-- wp:paragraph --><p>' . esc_html__( 'Answer here. Keep it direct and specific to Tennessee practice.', 'brooks-law-30-pro' ) . '</p><!-- /wp:paragraph --></details><!-- /wp:details -->
<!-- wp:details --><details class="wp-block-details"><summary>' . esc_html__( 'How much does a criminal defense lawyer cost?', 'brooks-law-30-pro' ) . '</summary><!-- wp:paragraph --><p>' . esc_html__( 'Answer here.', 'brooks-law-30-pro' ) . '</p><!-- /wp:paragraph --></details><!-- /wp:details -->',
	) );

	/* ---- Consultation banner ---- */
	/* translators: %s: office phone number as displayed. */
	$consult_line = sprintf( __( 'Call or text the criminal line, or call the office at %s.', 'brooks-law-30-pro' ), $phone );

	register_block_pattern( 'brooks-law/consult-banner', array(
		'title'      => __( 'Free consultation banner', 'brooks-law-30-pro' ),
		'categories' => array( 'brooks-law' ),
		'content'    => '<!-- wp:group {"style":{"border":{"left":{"color":"#b08d3e","width":"4px"}},"color":{"background":"#f5f2ea"},"spacing":{"padding":{"top":"var:preset|spacing|20","bottom":"var:preset|spacing|20","left":"var:preset|spacing|20","right":"var:preset|spacing|20"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group has-background" style="border-left-color:#b08d3e;border-left-width:4px;background-color:#f5f2ea;padding-top:var(--wp--preset--spacing--20);padding-right:var(--wp--preset--spacing--20);padding-bottom:var(--wp--preset--spacing--20);padding-left:var(--wp--preset--spacing--20)">
<!-- wp:paragraph --><p><strong>' . esc_html__( 'Free initial consultation.', 'brooks-law-30-pro' ) . '</strong> ' . esc_html( $consult_line ) . '</p><!-- /wp:paragraph --></div>
<!-- /wp:group -->',
	) );
}
add_action( 'init', 'brooks_law_register_patterns' );
