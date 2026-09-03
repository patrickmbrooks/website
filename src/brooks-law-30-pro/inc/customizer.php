<?php
/**
 * Brooks Law v2 — Customizer.
 *
 * Everything an owner should be able to change without touching code:
 * firm info, hero, practice cards, about, testimonials, case results,
 * contact, footer disclaimer, schema toggle.
 *
 * @package Brooks_Law
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* -------------------------------------------------------------------------
 * Sanitizers
 * ---------------------------------------------------------------------- */
function brooks_law_sanitize_checkbox( $checked ) {
	// wp_validate_boolean() rather than a loose comparison: it reads the
	// string "false" as false, which `true == $checked` did not. The
	// performance module already used it, so this also makes the two
	// sanitisers in the theme agree.
	return wp_validate_boolean( $checked );
}

function brooks_law_sanitize_overlay( $value ) {
	$value = (float) $value;

	// Clamped so the hero can never be dialed down to unreadable.
	return max( 0.5, min( 0.95, $value ) );
}

function brooks_law_sanitize_focus( $value ) {
	return in_array( $value, array( 'top', 'center', 'bottom' ), true ) ? $value : 'center';
}

function brooks_law_sanitize_url_or_path( $value ) {
	$value = trim( (string) $value );
	if ( '' === $value ) {
		return '';
	}
	/*
	 * Allow site-relative paths like /criminal-defense-2/ and in-page anchors,
	 * but not "//evil.example/x": a protocol-relative URL starts with a slash
	 * and so passed the old check, which meant a practice-card link could
	 * silently point off-site.
	 */
	if ( '//' !== substr( $value, 0, 2 ) && ( '/' === substr( $value, 0, 1 ) || '#' === substr( $value, 0, 1 ) ) ) {
		return sanitize_text_field( $value );
	}

	return esc_url_raw( $value );
}

/* -------------------------------------------------------------------------
 * Register
 * ---------------------------------------------------------------------- */
function brooks_law_customize_register( $wp_customize ) {

	$defaults = brooks_law_defaults();

	// Live-preview the core site title. Guarded: a plugin that removes the
	// setting would otherwise make this a fatal on ->transport.
	$blogname = $wp_customize->get_setting( 'blogname' );
	if ( $blogname instanceof WP_Customize_Setting ) {
		$blogname->transport = 'postMessage';
	}

	$wp_customize->add_panel( 'brooks_law', array(
		'title'    => __( 'Brooks Law Firm', 'brooks-law-30-pro' ),
		'priority' => 10,
	) );

	/**
	 * Helper to register a setting + control pair.
	 *
	 * @param WP_Customize_Manager $wp_customize Manager.
	 * @param string               $key          Setting key.
	 * @param array                $args         label, section, type, description, transport, sanitize.
	 * @param array                $defaults     Theme defaults.
	 */
	$add_field = function ( $wp_customize, $key, $args, $defaults ) {
		$type      = isset( $args['type'] ) ? $args['type'] : 'text';
		$transport = isset( $args['transport'] ) ? $args['transport'] : 'refresh';

		$sanitize = 'sanitize_text_field';
		if ( isset( $args['sanitize'] ) ) {
			$sanitize = $args['sanitize'];
		} elseif ( 'textarea' === $type ) {
			$sanitize = 'sanitize_textarea_field';
		} elseif ( 'checkbox' === $type ) {
			$sanitize = 'brooks_law_sanitize_checkbox';
		} elseif ( 'email' === $type ) {
			$sanitize = 'sanitize_email';
		}

		$wp_customize->add_setting( $key, array(
			'default'           => isset( $defaults[ $key ] ) ? $defaults[ $key ] : '',
			'sanitize_callback' => $sanitize,
			'transport'         => $transport,
		) );

		$wp_customize->add_control( $key, array(
			'label'       => $args['label'],
			'description' => isset( $args['description'] ) ? $args['description'] : '',
			'section'     => $args['section'],
			'type'        => $type,
		) );
	};

	/* ---- Firm info ---- */
	$wp_customize->add_section( 'brooks_law_firm', array(
		'title' => __( 'Firm Info', 'brooks-law-30-pro' ),
		'panel' => 'brooks_law',
	) );

	$add_field( $wp_customize, 'firm_shortname', array( 'label' => __( 'Firm short name (footer / copyright)', 'brooks-law-30-pro' ), 'section' => 'brooks_law_firm' ), $defaults );
	$add_field( $wp_customize, 'firm_tagline', array( 'label' => __( 'Header tagline', 'brooks-law-30-pro' ), 'section' => 'brooks_law_firm', 'transport' => 'postMessage' ), $defaults );
	$add_field( $wp_customize, 'firm_phone', array( 'label' => __( 'Office phone (display)', 'brooks-law-30-pro' ), 'section' => 'brooks_law_firm' ), $defaults );
	$add_field( $wp_customize, 'firm_phone_link', array( 'label' => __( 'Office phone (dial format, e.g. +19013245000)', 'brooks-law-30-pro' ), 'section' => 'brooks_law_firm' ), $defaults );
	$add_field( $wp_customize, 'firm_cell', array( 'label' => __( 'Criminal line (display)', 'brooks-law-30-pro' ), 'section' => 'brooks_law_firm' ), $defaults );
	$add_field( $wp_customize, 'firm_cell_link', array( 'label' => __( 'Criminal line (dial format)', 'brooks-law-30-pro' ), 'section' => 'brooks_law_firm' ), $defaults );
	$add_field( $wp_customize, 'firm_email', array( 'label' => __( 'Email', 'brooks-law-30-pro' ), 'section' => 'brooks_law_firm', 'type' => 'email' ), $defaults );
	$add_field( $wp_customize, 'firm_address', array( 'label' => __( 'Street address', 'brooks-law-30-pro' ), 'section' => 'brooks_law_firm' ), $defaults );
	$add_field( $wp_customize, 'firm_city_state', array( 'label' => __( 'City, state, ZIP', 'brooks-law-30-pro' ), 'section' => 'brooks_law_firm' ), $defaults );
	$add_field( $wp_customize, 'firm_hours', array( 'label' => __( 'Office hours', 'brooks-law-30-pro' ), 'section' => 'brooks_law_firm' ), $defaults );
	$add_field( $wp_customize, 'firm_facebook', array( 'label' => __( 'Facebook page URL', 'brooks-law-30-pro' ), 'section' => 'brooks_law_firm', 'sanitize' => 'esc_url_raw' ), $defaults );
	$add_field( $wp_customize, 'topbar_note', array( 'label' => __( 'Top bar note', 'brooks-law-30-pro' ), 'section' => 'brooks_law_firm' ), $defaults );

	/* ---- Hero ---- */
	$wp_customize->add_section( 'brooks_law_hero', array(
		'title' => __( 'Homepage: Hero', 'brooks-law-30-pro' ),
		'panel' => 'brooks_law',
	) );

	$add_field( $wp_customize, 'hero_kicker', array( 'label' => __( 'Small label above the heading', 'brooks-law-30-pro' ), 'section' => 'brooks_law_hero' ), $defaults );
	$add_field( $wp_customize, 'hero_heading', array( 'label' => __( 'Heading (the page H1)', 'brooks-law-30-pro' ), 'section' => 'brooks_law_hero', 'transport' => 'postMessage' ), $defaults );
	$add_field( $wp_customize, 'hero_lead', array( 'label' => __( 'Lead paragraph', 'brooks-law-30-pro' ), 'section' => 'brooks_law_hero', 'type' => 'textarea', 'transport' => 'postMessage' ), $defaults );
	$add_field( $wp_customize, 'trust_point_1', array( 'label' => __( 'Trust point 1', 'brooks-law-30-pro' ), 'section' => 'brooks_law_hero' ), $defaults );
	$add_field( $wp_customize, 'trust_point_2', array( 'label' => __( 'Trust point 2', 'brooks-law-30-pro' ), 'section' => 'brooks_law_hero' ), $defaults );
	$add_field( $wp_customize, 'trust_point_3', array( 'label' => __( 'Trust point 3', 'brooks-law-30-pro' ), 'section' => 'brooks_law_hero' ), $defaults );

	/* ---- Hero photo ---- */
	$wp_customize->add_setting( 'hero_image', array(
		'default'           => 0,
		'sanitize_callback' => 'absint',
	) );
	$wp_customize->add_control( new WP_Customize_Media_Control( $wp_customize, 'hero_image', array(
		'label'       => __( 'Hero photo (optional)', 'brooks-law-30-pro' ),
		'description' => __( 'Sits behind the heading and phone numbers. Use a wide, uncluttered shot — around 2000px wide, saved under 250KB. Leave empty for the plain dark hero.', 'brooks-law-30-pro' ),
		'section'     => 'brooks_law_hero',
		'mime_type'   => 'image',
	) ) );

	$wp_customize->add_setting( 'hero_overlay', array(
		'default'           => $defaults['hero_overlay'],
		'sanitize_callback' => 'brooks_law_sanitize_overlay',
	) );
	$wp_customize->add_control( 'hero_overlay', array(
		'label'       => __( 'Photo darkening', 'brooks-law-30-pro' ),
		'description' => __( 'How much the photo is dimmed so text stays readable. Higher is darker and safer. Below 0.6, check a busy photo carefully.', 'brooks-law-30-pro' ),
		'section'     => 'brooks_law_hero',
		'type'        => 'range',
		'input_attrs' => array(
			'min'  => 0.5,
			'max'  => 0.95,
			'step' => 0.01,
		),
	) );

	$wp_customize->add_setting( 'hero_focus', array(
		'default'           => $defaults['hero_focus'],
		'sanitize_callback' => 'brooks_law_sanitize_focus',
	) );
	$wp_customize->add_control( 'hero_focus', array(
		'label'       => __( 'Photo focal point', 'brooks-law-30-pro' ),
		'description' => __( 'Which part of the photo to keep in frame when it is cropped.', 'brooks-law-30-pro' ),
		'section'     => 'brooks_law_hero',
		'type'        => 'select',
		'choices'     => array(
			'top'    => __( 'Top', 'brooks-law-30-pro' ),
			'center' => __( 'Middle', 'brooks-law-30-pro' ),
			'bottom' => __( 'Bottom', 'brooks-law-30-pro' ),
		),
	) );


	/* ---- Practice cards ---- */
	$wp_customize->add_section( 'brooks_law_practice', array(
		'title'       => __( 'Homepage: Practice Areas', 'brooks-law-30-pro' ),
		'panel'       => 'brooks_law',
		'description' => __( 'The three cards under the hero. Links accept a full URL or a relative path like /criminal-defense-2/.', 'brooks-law-30-pro' ),
	) );

	/* translators: %d: practice-area card number, 1 to 3. */
	for ( $i = 1; $i <= 3; $i++ ) {
		$add_field( $wp_customize, "practice_{$i}_title", array( 'label' => sprintf( __( 'Card %d — title', 'brooks-law-30-pro' ), $i ), 'section' => 'brooks_law_practice' ), $defaults );
		$add_field( $wp_customize, "practice_{$i}_desc", array( 'label' => sprintf( __( 'Card %d — description', 'brooks-law-30-pro' ), $i ), 'section' => 'brooks_law_practice', 'type' => 'textarea' ), $defaults );
		$add_field( $wp_customize, "practice_{$i}_url", array( 'label' => sprintf( __( 'Card %d — link', 'brooks-law-30-pro' ), $i ), 'section' => 'brooks_law_practice', 'sanitize' => 'brooks_law_sanitize_url_or_path' ), $defaults );
	}

	/* ---- About ---- */
	$wp_customize->add_section( 'brooks_law_about', array(
		'title' => __( 'Homepage: About', 'brooks-law-30-pro' ),
		'panel' => 'brooks_law',
	) );

	$add_field( $wp_customize, 'about_heading', array( 'label' => __( 'Heading', 'brooks-law-30-pro' ), 'section' => 'brooks_law_about' ), $defaults );
	$add_field( $wp_customize, 'about_text', array( 'label' => __( 'Text (blank line = new paragraph)', 'brooks-law-30-pro' ), 'section' => 'brooks_law_about', 'type' => 'textarea' ), $defaults );
	$add_field( $wp_customize, 'about_attorney_line', array( 'label' => __( 'Attorney line', 'brooks-law-30-pro' ), 'section' => 'brooks_law_about' ), $defaults );

	$wp_customize->add_setting( 'about_photo', array(
		'default'           => 0,
		'sanitize_callback' => 'absint',
	) );
	$wp_customize->add_control( new WP_Customize_Media_Control( $wp_customize, 'about_photo', array(
		'label'       => __( 'Firm photo (optional)', 'brooks-law-30-pro' ),
		'description' => __( 'Shown beside the About text. Around 1200px wide is plenty.', 'brooks-law-30-pro' ),
		'section'     => 'brooks_law_about',
		'mime_type'   => 'image',
	) ) );

	/* ---- Testimonials ---- */
	$wp_customize->add_section( 'brooks_law_testimonials', array(
		'title'       => __( 'Homepage: Testimonials', 'brooks-law-30-pro' ),
		'panel'       => 'brooks_law',
		'description' => __( 'Shown only when enabled and at least one quote is filled in.', 'brooks-law-30-pro' ),
	) );

	$add_field( $wp_customize, 'testimonials_enable', array( 'label' => __( 'Show the testimonials section', 'brooks-law-30-pro' ), 'section' => 'brooks_law_testimonials', 'type' => 'checkbox' ), $defaults );
	$add_field( $wp_customize, 'testimonials_heading', array( 'label' => __( 'Heading', 'brooks-law-30-pro' ), 'section' => 'brooks_law_testimonials' ), $defaults );

	/* translators: %d: testimonial slot number, 1 to 4. */
	for ( $i = 1; $i <= 4; $i++ ) {
		$add_field( $wp_customize, "testimonial_{$i}_quote", array( 'label' => sprintf( __( 'Testimonial %d — quote', 'brooks-law-30-pro' ), $i ), 'section' => 'brooks_law_testimonials', 'type' => 'textarea' ), $defaults );
		$add_field( $wp_customize, "testimonial_{$i}_name", array( 'label' => sprintf( __( 'Testimonial %d — attribution (e.g. “D.M., DUI client”)', 'brooks-law-30-pro' ), $i ), 'section' => 'brooks_law_testimonials' ), $defaults );
	}

	$add_field( $wp_customize, 'testimonials_note', array( 'label' => __( 'Fine-print note under the section', 'brooks-law-30-pro' ), 'section' => 'brooks_law_testimonials', 'type' => 'textarea' ), $defaults );

	/* ---- Case results ---- */
	$wp_customize->add_section( 'brooks_law_results', array(
		'title'       => __( 'Homepage: Case Results', 'brooks-law-30-pro' ),
		'panel'       => 'brooks_law',
		'description' => __( 'One result per line. Use a pipe to split charge from outcome, e.g. “Felony drug possession | Reduced to misdemeanor”.', 'brooks-law-30-pro' ),
	) );

	$add_field( $wp_customize, 'results_enable', array( 'label' => __( 'Show the case results section', 'brooks-law-30-pro' ), 'section' => 'brooks_law_results', 'type' => 'checkbox' ), $defaults );
	$add_field( $wp_customize, 'results_heading', array( 'label' => __( 'Heading', 'brooks-law-30-pro' ), 'section' => 'brooks_law_results' ), $defaults );
	$add_field( $wp_customize, 'results_items', array( 'label' => __( 'Results (one per line)', 'brooks-law-30-pro' ), 'section' => 'brooks_law_results', 'type' => 'textarea' ), $defaults );
	$add_field( $wp_customize, 'results_note', array( 'label' => __( 'Fine-print note under the section', 'brooks-law-30-pro' ), 'section' => 'brooks_law_results', 'type' => 'textarea' ), $defaults );

	/* ---- Contact ---- */
	$wp_customize->add_section( 'brooks_law_contact', array(
		'title' => __( 'Homepage: Contact', 'brooks-law-30-pro' ),
		'panel' => 'brooks_law',
	) );

	$add_field( $wp_customize, 'contact_heading', array( 'label' => __( 'Heading', 'brooks-law-30-pro' ), 'section' => 'brooks_law_contact' ), $defaults );
	$add_field( $wp_customize, 'contact_intro', array( 'label' => __( 'Intro line', 'brooks-law-30-pro' ), 'section' => 'brooks_law_contact', 'type' => 'textarea' ), $defaults );
	$add_field( $wp_customize, 'contact_note', array( 'label' => __( 'Confidentiality note', 'brooks-law-30-pro' ), 'section' => 'brooks_law_contact', 'type' => 'textarea' ), $defaults );
	$add_field( $wp_customize, 'contact_form_shortcode', array(
		'label'       => __( 'Contact form shortcode (optional)', 'brooks-law-30-pro' ),
		'description' => __( 'Paste a form plugin shortcode, e.g. [contact-form-7 id="123"]. Leave blank for call/email only.', 'brooks-law-30-pro' ),
		'section'     => 'brooks_law_contact',
	), $defaults );

	/* ---- Footer ---- */
	$wp_customize->add_section( 'brooks_law_footer', array(
		'title' => __( 'Footer', 'brooks-law-30-pro' ),
		'panel' => 'brooks_law',
	) );

	$add_field( $wp_customize, 'footer_disclaimer', array( 'label' => __( 'Attorney advertising disclaimer', 'brooks-law-30-pro' ), 'section' => 'brooks_law_footer', 'type' => 'textarea' ), $defaults );

	/* ---- SEO / schema ---- */
	$wp_customize->add_section( 'brooks_law_seo', array(
		'title'       => __( 'SEO & Schema', 'brooks-law-30-pro' ),
		'panel'       => 'brooks_law',
		'description' => __( 'Outputs LegalService structured data built from Firm Info. Safe alongside Yoast.', 'brooks-law-30-pro' ),
	) );

	$add_field( $wp_customize, 'schema_enable', array( 'label' => __( 'Output LegalService schema (JSON-LD)', 'brooks-law-30-pro' ), 'section' => 'brooks_law_seo', 'type' => 'checkbox' ), $defaults );
}
add_action( 'customize_register', 'brooks_law_customize_register' );

/* -------------------------------------------------------------------------
 * Live-preview script
 * ---------------------------------------------------------------------- */
function brooks_law_customize_preview_js() {
	wp_enqueue_script(
		'brooks-law-customizer',
		get_template_directory_uri() . '/assets/js/customizer.js',
		array( 'customize-preview' ),
		BROOKS_LAW_VERSION,
		true
	);
}
add_action( 'customize_preview_init', 'brooks_law_customize_preview_js' );
