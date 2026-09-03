<?php
/**
 * Brooks Law v2 — theme setup and helpers.
 *
 * Same theme slug ("brooks-law") as v1 so menus, Customizer values, and
 * page-template assignments carry over on an in-place replacement.
 *
 * @package Brooks_Law
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'BROOKS_LAW_VERSION', '5.3.0' );

/* -------------------------------------------------------------------------
 * Setup
 * ---------------------------------------------------------------------- */
function brooks_law_setup() {
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'custom-logo', array(
		'height'      => 120,
		'width'       => 360,
		'flex-height' => true,
		'flex-width'  => true,
	) );
	add_theme_support( 'html5', array(
		'search-form',
		'comment-form',
		'comment-list',
		'gallery',
		'caption',
		'style',
		'script',
		'navigation-widgets',
	) );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'automatic-feed-links' );

	register_nav_menus( array(
		'primary' => __( 'Primary Menu', 'brooks-law-30-pro' ),
		'footer'  => __( 'Footer Menu', 'brooks-law-30-pro' ),
	) );
}
add_action( 'after_setup_theme', 'brooks_law_setup' );

function brooks_law_content_width() {
	$GLOBALS['content_width'] = 760;
}
add_action( 'after_setup_theme', 'brooks_law_content_width', 0 );

/* -------------------------------------------------------------------------
 * Assets
 * ---------------------------------------------------------------------- */
/**
 * Asset version string: file modification time when the file exists, theme
 * version otherwise. Using filemtime means an edited asset cache-busts itself
 * without waiting for a theme version bump.
 *
 * @param string $rel Path relative to the theme root, e.g. '/assets/js/navigation.js'.
 * @return string
 */
function brooks_law_asset_ver( $rel ) {
	$path = get_template_directory() . $rel;
	return file_exists( $path ) ? (string) filemtime( $path ) : BROOKS_LAW_VERSION;
}

function brooks_law_scripts() {
	wp_enqueue_style(
		'brooks-law-style',
		get_stylesheet_uri(),
		array(),
		brooks_law_asset_ver( '/style.css' )
	);

	wp_enqueue_script(
		'brooks-law-navigation',
		get_template_directory_uri() . '/assets/js/navigation.js',
		array(),
		brooks_law_asset_ver( '/assets/js/navigation.js' ),
		array(
			'strategy'  => 'defer',
			'in_footer' => true,
		)
	);

	if ( brooks_law_get_option( 'toggle_enable' ) ) {
		wp_enqueue_script(
			'brooks-law-contact-toggle',
			get_template_directory_uri() . '/assets/js/contact-toggle.js',
			array(),
			brooks_law_asset_ver( '/assets/js/contact-toggle.js' ),
			array(
				'strategy'  => 'defer',
				'in_footer' => true,
			)
		);
	}
}
add_action( 'wp_enqueue_scripts', 'brooks_law_scripts' );

/**
 * Small performance trims: emoji script/styles are dead weight for this site.
 */
function brooks_law_performance_trims() {
	remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
	remove_action( 'wp_print_styles', 'print_emoji_styles' );
	remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
	remove_action( 'admin_print_styles', 'print_emoji_styles' );
	remove_action( 'wp_head', 'wp_generator' );
}
add_action( 'init', 'brooks_law_performance_trims' );

/* -------------------------------------------------------------------------
 * Options: defaults + reader
 * ---------------------------------------------------------------------- */

/**
 * Central defaults. Every field falls back to real firm information so the
 * site never renders placeholder brackets or empty strings.
 *
 * @return array
 */
function brooks_law_defaults() {
	/**
	 * Filter the shipped defaults.
	 *
	 * Every value below is a fallback, not a setting: the Customizer
	 * overrides all of them. A child theme rebranding this for another firm
	 * replaces the identity block here in one filter rather than editing the
	 * array, so an update never clobbers the change.
	 *
	 * @since 5.3.0
	 *
	 * @param array $defaults Shipped defaults.
	 */
	return apply_filters( 'brooks_law_defaults', array(
		// Firm identity.
		'firm_shortname'        => 'Brooks Law Firm',
		'firm_tagline'          => 'Criminal Lawyers · Memphis, TN',
		'firm_phone'            => '(901) 324-5000',
		'firm_phone_link'       => '+19013245000',
		'firm_cell'             => '901-412-2973',
		'firm_cell_link'        => '+19014122973',
		'firm_email'            => 'patrick@patrickbrookslaw.com',
		'firm_address'          => '2299 Union Avenue',
		'firm_city_state'       => 'Memphis, Tennessee 38104',
		'firm_hours'            => 'Monday – Friday, 8:00 a.m. – 5:30 p.m.',
		'firm_facebook'         => 'https://www.facebook.com/PatrickBrooksLawFirm/',
		'topbar_note'           => 'Free initial consultation · Se habla Español',

		// Hero.
		'hero_kicker'           => 'Criminal Defense · Memphis & Shelby County',
		'hero_heading'          => 'Memphis Criminal Defense Attorneys',
		'hero_image'            => 0,
		'hero_overlay'          => 0.82,
		'hero_focus'            => 'center',
		'hero_lead'             => 'Arrested or charged in Shelby County? Traffic ticket or CDL defense? Talk to a lawyer before you talk to anyone else.',
		// Text / call toggle.
		'faq_schema_enable'     => true,
		'ribbon_art_enable'     => true,
		'ribbon_art_default'    => 'mbridge',
		'ribbon_art_opacity'    => 22,
		'ribbon_art_parallax'   => true,
		'edpage_enable'         => true,
		'edpage_type'           => 'editorial',
		'edpage_consolidate'    => true,
		'edpage_unify_scene'    => true,
		'atmos_enable'          => true,
		'home_style'            => 'classic',
		'post_nav'              => false,
		'back_to_top'           => false,
		'speculation'           => true,
		'tune_headers'          => true,
		'tune_rest_users'       => true,
		'tune_head_cleanup'     => true,
		'tune_xmlrpc'           => true,
		'toggle_enable'         => true,
		'toggle_on_pages'       => true,
		'toggle_text_number'    => '',
		'toggle_call_number'    => '',
		'toggle_ask'            => 'I need help with',
		'toggle_text_label'     => 'Text us — fastest',
		'toggle_call_label'     => 'Civil & criminal',
		'toggle_fine_print'     => 'Texts go straight to the attorney line.',
		'matter_1_label'        => 'A criminal charge',
		'matter_1_body'         => 'I have a criminal charge and need help. My name is:',
		'matter_2_label'        => 'A traffic ticket or CDL matter',
		'matter_2_body'         => 'I have a traffic ticket or CDL matter and need help. My name is:',
		'toggle_generic_body'   => 'I need help with a legal matter. My name is:',
		'matter_3_label'        => '',
		'matter_3_body'         => '',
		'callbar_text_label'    => 'Text',
		'callbar_call_label'    => 'Civil & criminal',

		// Civil pages: email Beth Brooks, or call the office.
		'firm_civil_email'      => 'beth.brooks@mbbrooks.com',
		'civil_contact_name'    => 'Beth Brooks',
		'civil_email_subject'   => 'Consultation request',
		'toggle_email_label'    => 'Email to schedule',
		'toggle_call_label_civil' => 'Civil matters',
		'civil_fee_note'        => 'Consultations are $350 an hour, in person at the office. Email or call to schedule.',
		'civil_nofee_note'      => 'No consultation fee for wrongful death and personal injury matters. Email or call to get started.',
		'callbar_email_label'   => 'Email',
		'callbar_civil_call_label' => 'Civil',

		'trust_point_1'         => 'Over 45 years of combined experience',
		'trust_point_2'         => 'Free initial consultation',
		'trust_point_3'         => 'Se habla Español',

		// Practice cards (defaults match the live hubs).
		'practice_1_title'      => 'Criminal Defense',
		'practice_1_desc'       => 'Representation for misdemeanor and felony charges in Shelby County, including DUI, assault, drug offenses, and theft — from preliminary hearing through trial.',
		'practice_1_url'        => '/criminal-defense-2/',
		'practice_2_title'      => 'Civil Litigation',
		'practice_2_desc'       => 'Contract, property, fraud, landlord-tenant, and estate disputes in the Circuit, Chancery, and General Sessions Courts of Shelby County.',
		'practice_2_url'        => '/civil-litigation/',
		'practice_3_title'      => 'Traffic Matters',
		'practice_3_desc'       => 'Reckless driving, driving on a revoked or suspended license, speeding, CDL matters, and related traffic offenses.',
		'practice_3_url'        => '/traffic/',

		// About.
		'about_heading'         => 'About the Firm',
		'about_text'            => "Brooks Law Firm is a Memphis-based practice serving clients across Shelby County and the surrounding area. The firm handles criminal defense, civil litigation, and traffic matters — with the preparation a Tennessee court expects and the personal attention a closely held client deserves.\n\nOur attorneys, Patrick Brooks and Beth Brooks, handle each matter personally. Clients work directly with their attorney, not through layers of intermediaries, and receive honest assessments of what the law does and does not allow. Spanish-language services are available.",
		'about_attorney_line'   => 'Attorneys — Patrick Brooks · Beth Brooks',

		// Testimonials.
		'testimonials_enable'   => false,
		'testimonials_heading'  => 'What Clients Say',
		'testimonial_1_quote'   => '',
		'testimonial_1_name'    => '',
		'testimonial_2_quote'   => '',
		'testimonial_2_name'    => '',
		'testimonial_3_quote'   => '',
		'testimonial_3_name'    => '',
		'testimonial_4_quote'   => '',
		'testimonial_4_name'    => '',
		'testimonials_note'     => 'Client experiences vary. Prior results and testimonials do not guarantee or predict a similar outcome in any future matter.',

		// Case results.
		'results_enable'        => false,
		'results_heading'       => 'Recent Results',
		'results_items'         => '',
		'results_note'          => 'Every case is different. Prior results do not guarantee or predict a similar outcome in any future matter.',

		// Contact.
		'contact_heading'       => 'Contact the Firm',
		'contact_intro'         => 'Consultations may be arranged in person at our Midtown Memphis office or by telephone.',
		'contact_note'          => 'Please do not send confidential or time-sensitive information through this website. Contacting the firm does not create an attorney-client relationship.',
		'contact_form_shortcode' => '',

		// Footer.
		'footer_disclaimer'     => 'Attorney Advertising Disclaimer. This website is an advertisement for legal services by Brooks Law Firm, Attorney Patrick Brooks, 2299 Union Avenue, Memphis, Tennessee 38104. Patrick Brooks is the attorney responsible for the content of this website. The information on this website is provided for general informational purposes only and does not constitute legal advice. Viewing or using this website does not create an attorney-client relationship; such a relationship is formed only by a written engagement agreement signed by both the client and the firm. Every legal matter is different, and prior results do not guarantee or predict a similar outcome in any future matter. The firm\'s attorneys are licensed to practice law in the State of Tennessee. No representation is made that the quality of the legal services to be performed is greater than the quality of legal services performed by other lawyers. Do not send confidential or time-sensitive information to the firm through this website.',

		// Identity used by the structured-data graph. These drive the
		// machine-readable copy of the firm; the human-readable copy comes
		// from the firm_* fields above, and both are edited in one place.
		'attorney_1_name'       => 'Patrick Brooks',
		'attorney_1_title'      => 'Attorney',
		'attorney_1_url'        => '',
		'attorney_1_alumni'     => '',
		'attorney_1_knows'      => 'Criminal Defense, DUI Defense, Drug Charges, Expungement, Veterans Treatment Court',
		'attorney_2_name'       => 'Beth Brooks',
		'attorney_2_title'      => 'Attorney',
		'attorney_2_url'        => '',
		'attorney_2_alumni'     => '',
		'attorney_2_knows'      => 'Criminal Defense, Civil Litigation, Traffic Matters',
		'service_area'          => 'Memphis, Shelby County, West Tennessee',
		'firm_languages'        => 'en, es',

		// Schema.
		'schema_enable'         => true,
	) );
}

/**
 * Read a theme option.
 *
 * Order: theme mod (v1 and v2 both store here for this slug) → legacy
 * "brooks_law_options" option array → shipped default. Empty strings fall
 * through to the default for fields that must never render blank.
 *
 * NOTE: passing $default explicitly REPLACES the shipped default rather than
 * falling back to it, so brooks_law_get_option( 'x', '' ) returns '' even when
 * defaults() defines a value for 'x'. Pass a default only when you mean to
 * override the shipped one; otherwise call with one argument.
 *
 * @param string $key     Option key.
 * @param mixed  $default Optional explicit default.
 * @return mixed
 */
function brooks_law_get_option( $key, $default = null ) {
	$defaults = brooks_law_defaults();
	if ( null === $default ) {
		$default = isset( $defaults[ $key ] ) ? $defaults[ $key ] : '';
	}

	$value = get_theme_mod( $key, null );

	if ( null === $value || '' === $value ) {
		$legacy = get_option( 'brooks_law_options' );
		if ( is_array( $legacy ) && isset( $legacy[ $key ] ) && '' !== $legacy[ $key ] ) {
			$value = $legacy[ $key ];
		}
	}

	if ( null === $value || '' === $value ) {
		return $default;
	}

	return $value;
}

/**
 * Digits-and-plus tel: href from a display number.
 *
 * @param string $number Display number.
 * @return string
 */
function brooks_law_tel( $number ) {
	$clean = preg_replace( '/[^0-9+]/', '', (string) $number );
	if ( '' === $clean ) {
		return '';
	}
	if ( '+' !== substr( $clean, 0, 1 ) && 10 === strlen( $clean ) ) {
		$clean = '+1' . $clean;
	}
	return $clean;
}

/* -------------------------------------------------------------------------
 * Body classes
 * ---------------------------------------------------------------------- */
function brooks_law_body_classes( $classes ) {
	if ( is_front_page() ) {
		$classes[] = 'is-front';
	}
	return $classes;
}
add_filter( 'body_class', 'brooks_law_body_classes' );

/* -------------------------------------------------------------------------
 * Includes
 * ---------------------------------------------------------------------- */
require get_template_directory() . '/inc/template-tags.php';
require get_template_directory() . '/inc/contact-toggle.php';
require get_template_directory() . '/inc/homepage-content.php';
require get_template_directory() . '/inc/customizer.php';
require get_template_directory() . '/inc/schema-identity.php';
require get_template_directory() . '/inc/schema-graph.php';
require get_template_directory() . '/inc/faq-schema.php';
require get_template_directory() . '/inc/service-areas.php';
require get_template_directory() . '/inc/action-center.php';
require get_template_directory() . '/inc/page-tiles.php';
require get_template_directory() . '/inc/ribbon-art.php';
require get_template_directory() . '/inc/page-ribbon.php';
require get_template_directory() . '/inc/editorial-sky.php';
require get_template_directory() . '/inc/editorial-pages.php';
require get_template_directory() . '/inc/editorial-blocks.php';
require get_template_directory() . '/inc/editorial-atmosphere.php';
require get_template_directory() . '/inc/editorial-patterns.php';
require get_template_directory() . '/inc/home-style.php';
require get_template_directory() . '/inc/component-loader.php';
require get_template_directory() . '/inc/ui-components.php';
require get_template_directory() . '/inc/speculation.php';
require get_template_directory() . '/inc/site-tuning.php';
require get_template_directory() . '/inc/schema-repair.php';
require get_template_directory() . '/inc/design-studio.php';
require get_template_directory() . '/inc/performance.php';
require get_template_directory() . '/inc/block-editor.php';

/* ==== v2.2.0 — hero video + header/footer ribbon photos (Docket media retrofit) ==== */

function brooks_law_media_customize( $wp_customize ) {
	/* Hero video joins the existing Homepage: Hero section. */
	$wp_customize->add_setting( 'hero_video', array( 'default' => 0, 'sanitize_callback' => 'absint' ) );
	$wp_customize->add_control( new WP_Customize_Media_Control( $wp_customize, 'hero_video', array(
		'label'       => __( 'Hero background video (MP4, optional)', 'brooks-law-30-pro' ),
		'description' => __( 'Plays muted on loop behind the heading. Keep it short (10-20s) and under ~5MB so PageSpeed stays green. The hero photo is the fallback where video does not play, and your existing "Photo darkening" slider dims the video too.', 'brooks-law-30-pro' ),
		'section'     => 'brooks_law_hero',
		'mime_type'   => 'video',
	) ) );

	/* Ribbons get their own section inside the theme panel. */
	$wp_customize->add_section( 'brooks_law_ribbons', array(
		'title'       => __( 'Header & Footer Ribbons', 'brooks-law-30-pro' ),
		'panel'       => 'brooks_law',
		'description' => __( 'Optional photos behind the header and footer. The firm name, logo, and navigation always stay on top. Opacity is 0-100 (percent) — keep it subtle (10-25) so text stays readable.', 'brooks-law-30-pro' ),
	) );
	$wp_customize->add_setting( 'header_ribbon', array( 'default' => '', 'sanitize_callback' => 'esc_url_raw' ) );
	$wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize, 'header_ribbon', array(
		'label'   => __( 'Header ribbon photo', 'brooks-law-30-pro' ),
		'section' => 'brooks_law_ribbons',
	) ) );
	$wp_customize->add_setting( 'header_ribbon_op', array( 'default' => 15, 'sanitize_callback' => 'absint' ) );
	$wp_customize->add_control( 'header_ribbon_op', array(
		'label'       => __( 'Header ribbon opacity (0-100%)', 'brooks-law-30-pro' ),
		'section'     => 'brooks_law_ribbons',
		'type'        => 'number',
		'input_attrs' => array( 'min' => 0, 'max' => 100, 'step' => 5 ),
	) );
	$wp_customize->add_setting( 'footer_ribbon', array( 'default' => '', 'sanitize_callback' => 'esc_url_raw' ) );
	$wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize, 'footer_ribbon', array(
		'label'   => __( 'Footer ribbon photo', 'brooks-law-30-pro' ),
		'section' => 'brooks_law_ribbons',
	) ) );
	$wp_customize->add_setting( 'footer_ribbon_op', array( 'default' => 15, 'sanitize_callback' => 'absint' ) );
	$wp_customize->add_control( 'footer_ribbon_op', array(
		'label'       => __( 'Footer ribbon opacity (0-100%)', 'brooks-law-30-pro' ),
		'section'     => 'brooks_law_ribbons',
		'type'        => 'number',
		'input_attrs' => array( 'min' => 0, 'max' => 100, 'step' => 5 ),
	) );
}
add_action( 'customize_register', 'brooks_law_media_customize' );

function brooks_law_ribbon_pct( $key ) {
	$v = absint( brooks_law_get_option( $key, 15 ) );
	return max( 0, min( 100, $v ) ) / 100;
}

function brooks_law_ribbon_css() {
	$css = '';
	$h = brooks_law_get_option( 'header_ribbon', '' );
	if ( $h ) {
		$css .= sprintf(
			'.site-header::before{content:"";position:absolute;inset:0;background:url(%1$s) center/cover no-repeat;opacity:%2$s;pointer-events:none;z-index:0}.site-header > .wrap{position:relative;z-index:1}',
			esc_url( $h ), brooks_law_ribbon_pct( 'header_ribbon_op' )
		);
	}
	$f = brooks_law_get_option( 'footer_ribbon', '' );
	if ( $f ) {
		$css .= sprintf(
			'.site-footer{position:relative}.site-footer::before{content:"";position:absolute;inset:0;background:url(%1$s) center/cover no-repeat;opacity:%2$s;pointer-events:none;z-index:0}.site-footer > .wrap{position:relative;z-index:1}',
			esc_url( $f ), brooks_law_ribbon_pct( 'footer_ribbon_op' )
		);
	}
	if ( $css ) {
		echo '<style id="brooks-law-ribbons">' . $css . '</style>';
	}
}
add_action( 'wp_head', 'brooks_law_ribbon_css' );

/** URL of the configured hero video, or empty string. */
function brooks_law_hero_video_url() {
	$id = absint( brooks_law_get_option( 'hero_video', 0 ) );
	return $id ? (string) wp_get_attachment_url( $id ) : '';
}

/** Prints the hero video layer. Sits in its own .hero-media wrapper so the existing scrim applies unchanged. */
function brooks_law_hero_video_render( $url ) {
	if ( ! $url ) {
		return;
	}
	$poster_id = absint( brooks_law_get_option( 'hero_image', 0 ) );
	$poster    = ( $poster_id && wp_attachment_is_image( $poster_id ) ) ? wp_get_attachment_image_url( $poster_id, 'full' ) : '';
	echo '<div class="hero-media hero-media--video" aria-hidden="true"><video autoplay muted loop playsinline preload="metadata"';
	if ( $poster ) {
		echo ' poster="' . esc_url( $poster ) . '"';
	}
	echo '><source src="' . esc_url( $url ) . '" type="video/mp4"></video></div>';
}

/*
 * Settings migration lives in inc/editorial-sky.php as
 * brooks_law_migrate_settings(). The v2.3-era copy that used to sit here
 * searched only for theme_mods_brooks-law and raced the 2.4 one on the same
 * hook; there is a single implementation now.
 */
