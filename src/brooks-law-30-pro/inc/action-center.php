<?php
/**
 * Homepage Action Center — v3.3.
 *
 * A situation-first tile grid between the hero and Areas of Practice,
 * modeled on the task-routing pattern of government action centers.
 * Fully Customizer-managed: 8 tile slots (title, subtitle, icon, link,
 * urgent-highlight toggle), section heading fields, and an editable
 * "More" link. Shares the icon library, URL sanitizer, and checkbox
 * sanitizer with Service Area Bubbles. Toggle-gated so the section
 * never renders empty.
 *
 * @package Brooks_Law
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Default tile set.
 *
 * @return array[] Slot => [ title, sub, icon, url, hot ].
 */
function brooks_law_action_center_defaults() {
	return array(
		1 => array( 'title' => 'Someone was just arrested', 'sub' => 'The first 72 hours',          'icon' => 'handcuffs',  'url' => '/what-happens-after-arrest-memphis/',  'hot' => true ),
		2 => array( 'title' => 'There\'s a warrant out',    'sub' => 'Missed court, capias recall', 'icon' => 'document',   'url' => '/capias-bench-warrant-shelby-county/', 'hot' => true ),
		3 => array( 'title' => 'Trying to make bond',       'sub' => 'Before you pay a bondsman',   'icon' => 'prisonbars', 'url' => '/how-does-bond-work-memphis/',         'hot' => true ),
		4 => array( 'title' => 'DUI charge',                'sub' => 'First offense or repeat',     'icon' => 'stoplight',  'url' => '/dui/',                                'hot' => false ),
		5 => array( 'title' => 'Drug charge',               'sub' => 'Possession to trafficking',   'icon' => 'pills',      'url' => '/drug-offense/',                       'hot' => false ),
		6 => array( 'title' => 'Domestic violence',         'sub' => 'Charges and protection orders', 'icon' => 'shield',   'url' => '/domestic-violence/',                  'hot' => false ),
		7 => array( 'title' => 'Ticket or CDL violation',   'sub' => 'We appear for you',           'icon' => 'semi',       'url' => '/traffic/',                            'hot' => false ),
		8 => array( 'title' => 'Felony charge',             'sub' => 'Indictment and preliminary hearing', 'icon' => 'scales', 'url' => '/felony-defense/',                  'hot' => false ),
	);
}

/**
 * Customizer: Action Center section.
 *
 * @param WP_Customize_Manager $wp_customize Manager.
 */
function brooks_law_action_center_customize( $wp_customize ) {

	$wp_customize->add_section( 'brooks_law_action_center', array(
		'title'       => __( 'Action Center', 'brooks-law-30-pro' ),
		'description' => __( 'Situation tiles between the hero and Areas of Practice. Leave a tile\'s title or link blank to hide it. "Urgent highlight" gives a tile the brass-tinted treatment for the panic-state entries.', 'brooks-law-30-pro' ),
		'priority'    => 131,
	) );

	$wp_customize->add_setting( 'ac_enable', array(
		'default'           => true,
		'sanitize_callback' => 'brooks_law_sanitize_checkbox',
	) );
	$wp_customize->add_control( 'ac_enable', array(
		'section' => 'brooks_law_action_center',
		'label'   => __( 'Show Action Center section', 'brooks-law-30-pro' ),
		'type'    => 'checkbox',
	) );

	$text_fields = array(
		'ac_kicker'     => array( __( 'Kicker', 'brooks-law-30-pro' ), 'Where You Stand Right Now' ),
		'ac_heading'    => array( __( 'Section heading', 'brooks-law-30-pro' ), 'Start With What Happened' ),
		'ac_subheading' => array( __( 'Section subheading', 'brooks-law-30-pro' ), 'Pick the closest match — each one goes straight to a page that explains the process in Shelby County and the surrounding courts.' ),
		'ac_more_label' => array( __( 'More link — label', 'brooks-law-30-pro' ), 'See every charge we defend' ),
	);
	foreach ( $text_fields as $key => $field ) {
		$wp_customize->add_setting( $key, array(
			'default'           => $field[1],
			'sanitize_callback' => 'sanitize_text_field',
		) );
		$wp_customize->add_control( $key, array(
			'section' => 'brooks_law_action_center',
			'label'   => $field[0],
			'type'    => 'text',
		) );
	}

	$wp_customize->add_setting( 'ac_hot_tag', array(
		'default'           => 'Time-sensitive',
		'sanitize_callback' => 'sanitize_text_field',
	) );
	$wp_customize->add_control( 'ac_hot_tag', array(
		'section'     => 'brooks_law_action_center',
		'label'       => __( 'Urgent tile tag', 'brooks-law-30-pro' ),
		'description' => __( 'Small label on the urgent tiles so urgency is stated in text, not only shown in color. Leave blank to hide.', 'brooks-law-30-pro' ),
		'type'        => 'text',
	) );

	$wp_customize->add_setting( 'ac_more_url', array(
		'default'           => '/criminal-defense-2/',
		'sanitize_callback' => 'brooks_law_sanitize_bubble_url',
	) );
	$wp_customize->add_control( 'ac_more_url', array(
		'section'     => 'brooks_law_action_center',
		'label'       => __( 'More link — destination', 'brooks-law-30-pro' ),
		'description' => __( 'Relative path like /criminal-defense-2/ or a full URL. Leave blank to hide the link.', 'brooks-law-30-pro' ),
		'type'        => 'text',
	) );

	$defaults     = brooks_law_action_center_defaults();
	$icon_choices = array();
	foreach ( brooks_law_sa_icons() as $icon_key => $icon ) {
		$icon_choices[ $icon_key ] = $icon['label'];
	}

	for ( $i = 1; $i <= 8; $i++ ) {

		$wp_customize->add_setting( "ac_{$i}_title", array(
			'default'           => $defaults[ $i ]['title'],
			'sanitize_callback' => 'sanitize_text_field',
		) );
		$wp_customize->add_control( "ac_{$i}_title", array(
			'section' => 'brooks_law_action_center',
			/* translators: %d: tile slot number. */
			'label'   => sprintf( __( 'Tile %d — Title', 'brooks-law-30-pro' ), $i ),
			'type'    => 'text',
		) );

		$wp_customize->add_setting( "ac_{$i}_sub", array(
			'default'           => $defaults[ $i ]['sub'],
			'sanitize_callback' => 'sanitize_text_field',
		) );
		$wp_customize->add_control( "ac_{$i}_sub", array(
			'section' => 'brooks_law_action_center',
			/* translators: %d: tile slot number. */
			'label'   => sprintf( __( 'Tile %d — Subtitle', 'brooks-law-30-pro' ), $i ),
			'type'    => 'text',
		) );

		$wp_customize->add_setting( "ac_{$i}_icon", array(
			'default'           => $defaults[ $i ]['icon'],
			'sanitize_callback' => 'brooks_law_sanitize_bubble_icon',
		) );
		$wp_customize->add_control( "ac_{$i}_icon", array(
			'section' => 'brooks_law_action_center',
			/* translators: %d: tile slot number. */
			'label'   => sprintf( __( 'Tile %d — Icon', 'brooks-law-30-pro' ), $i ),
			'type'    => 'select',
			'choices' => $icon_choices,
		) );

		$wp_customize->add_setting( "ac_{$i}_url", array(
			'default'           => $defaults[ $i ]['url'],
			'sanitize_callback' => 'brooks_law_sanitize_bubble_url',
		) );
		$wp_customize->add_control( "ac_{$i}_url", array(
			'section'     => 'brooks_law_action_center',
			/* translators: %d: tile slot number. */
			'label'       => sprintf( __( 'Tile %d — Link', 'brooks-law-30-pro' ), $i ),
			'description' => __( 'Relative path or full URL.', 'brooks-law-30-pro' ),
			'type'        => 'text',
		) );

		$wp_customize->add_setting( "ac_{$i}_hot", array(
			'default'           => $defaults[ $i ]['hot'],
			'sanitize_callback' => 'brooks_law_sanitize_checkbox',
		) );
		$wp_customize->add_control( "ac_{$i}_hot", array(
			'section' => 'brooks_law_action_center',
			/* translators: %d: tile slot number. */
			'label'   => sprintf( __( 'Tile %d — Urgent highlight', 'brooks-law-30-pro' ), $i ),
			'type'    => 'checkbox',
		) );
	}
}
add_action( 'customize_register', 'brooks_law_action_center_customize' );

/**
 * Populated tiles for the template.
 *
 * @return array[] title, sub, url, icon, hot.
 */
function brooks_law_get_action_tiles() {
	$defaults = brooks_law_action_center_defaults();
	$tiles    = array();

	for ( $i = 1; $i <= 8; $i++ ) {
		$title = get_theme_mod( "ac_{$i}_title", $defaults[ $i ]['title'] );
		$url   = get_theme_mod( "ac_{$i}_url", $defaults[ $i ]['url'] );

		if ( '' === trim( (string) $title ) || '' === trim( (string) $url ) ) {
			continue;
		}
		if ( 0 === strpos( $url, '/' ) ) {
			$url = home_url( $url );
		}

		$tiles[] = array(
			'title' => $title,
			'sub'   => get_theme_mod( "ac_{$i}_sub", $defaults[ $i ]['sub'] ),
			'url'   => $url,
			'icon'  => brooks_law_sanitize_bubble_icon( get_theme_mod( "ac_{$i}_icon", $defaults[ $i ]['icon'] ) ),
			'hot'   => (bool) get_theme_mod( "ac_{$i}_hot", $defaults[ $i ]['hot'] ),
		);
	}

	return $tiles;
}

/**
 * The Action Center "More" link URL, resolved.
 *
 * @return string Empty string when hidden.
 */
function brooks_law_action_center_more_url() {
	$url = trim( (string) get_theme_mod( 'ac_more_url', '/criminal-defense-2/' ) );
	if ( '' === $url ) {
		return '';
	}
	if ( 0 === strpos( $url, '/' ) ) {
		$url = home_url( $url );
	}
	return $url;
}
