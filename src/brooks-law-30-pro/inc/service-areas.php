<?php
/**
 * Service Area Bubbles — v2.3.
 *
 * Clickable community bubbles on the front page, between the
 * homepage content sections and Contact. Fully Customizer-managed:
 * per-bubble label, link, and optional photo. Toggle-gated so the
 * section never renders empty.
 *
 * @package Brooks_Law
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Default bubble set.
 *
 * @return array[] Slot => [ label, url ].
 */
function brooks_law_service_area_defaults() {
	return array(
		1  => array( 'label' => 'Memphis',           'url' => '/criminal-defense-2/' ),
		2  => array( 'label' => 'Shelby County',     'url' => '/criminal-defense-2/' ),
		3  => array( 'label' => 'Bartlett',          'url' => '/bartlett-criminal-defense/' ),
		4  => array( 'label' => 'Collierville',      'url' => '/collierville-criminal-defense/' ),
		5  => array( 'label' => 'Germantown',        'url' => '/germantown-criminal-defense/' ),
		6  => array( 'label' => 'Fayette County',    'url' => '/fayette-county-criminal-defense/' ),
		7  => array( 'label' => 'Tipton County',     'url' => '/tipton-county-criminal-defense/' ),
		8  => array( 'label' => 'Lauderdale County', 'url' => '/lauderdale-county-criminal-defense/' ),
		9  => array( 'label' => 'Haywood County',    'url' => '/haywood-county-criminal-defense/' ),
		10 => array( 'label' => 'Millington',        'url' => '/millington-criminal-defense/' ),
		11 => array( 'label' => 'Arlington',         'url' => '/arlington-criminal-defense/' ),
		12 => array( 'label' => 'Lakeland',          'url' => '/lakeland-criminal-defense/' ),
	);
}

/**
 * Sanitize a bubble link: relative path or absolute URL.
 *
 * @param string $value Raw value.
 * @return string
 */
function brooks_law_sanitize_bubble_url( $value ) {
	$value = trim( (string) $value );
	if ( '' === $value ) {
		return '';
	}
	if ( 0 === strpos( $value, '/' ) ) {
		return '/' . ltrim( sanitize_text_field( $value ), '/' );
	}
	return esc_url_raw( $value );
}


/**
 * Inline SVG icon library for bubbles.
 *
 * @return array key => [ label, svg path markup ].
 */
function brooks_law_sa_icons() {
	/*
	 * Static: every label here is a live __() call, so unlike a constant array
	 * PHP cannot intern this one — each call ran 59 translation lookups. It is
	 * reached from 14 sites, including once per tile inside the homepage
	 * loops, so a single render rebuilt it around twenty times.
	 */
	static $icons = null;

	if ( null !== $icons ) {
		return $icons;
	}

	$icons = array(
		'pin'        => array( 'label' => __( 'Location pin', 'brooks-law-30-pro' ),      'svg' => '<path fill="currentColor" d="M12 2a7 7 0 0 0-7 7c0 5.25 7 13 7 13s7-7.75 7-13a7 7 0 0 0-7-7zm0 9.5A2.5 2.5 0 1 1 12 6a2.5 2.5 0 0 1 0 5.5z"/>' ),
		'courthouse' => array( 'label' => __( 'Courthouse', 'brooks-law-30-pro' ),        'svg' => '<path fill="currentColor" d="M12 2 2 7v2h20V7L12 2zM4 10v8H3v3h18v-3h-1v-8h-3v8h-2.5v-8h-3v8H9v-8H6v8H4v-8z"/>' ),
		'scales'     => array( 'label' => __( 'Scales of justice', 'brooks-law-30-pro' ), 'svg' => '<path fill="currentColor" d="M11 3h2v2.1l5 1.2 1.6 5.2a3.6 3.6 0 0 1-7.2 0h2A1.6 1.6 0 0 0 17 12l-1.1-3.6L13 7.7V19h4v2H7v-2h4V7.7l-2.9.7L7 12a1.6 1.6 0 0 0 2.6 1.5h2a3.6 3.6 0 0 1-7.2-1l1.6-5.2 5-1.2V3z"/>' ),
		'gavel'      => array( 'label' => __( 'Gavel', 'brooks-law-30-pro' ),             'svg' => '<path fill="currentColor" d="m14.7 3 6.3 6.3-1.4 1.4-.7-.7-2.1 2.1 4.2 4.2-1.4 1.4-4.2-4.2-2.1 2.1.7.7-1.4 1.4L6.3 11l1.4-1.4.7.7 4.9-4.9-.7-.7L14 3.3l.7-.3zM3 19h10v2H3v-2z"/>' ),
		'shield'     => array( 'label' => __( 'Shield', 'brooks-law-30-pro' ),            'svg' => '<path fill="currentColor" d="M12 2 4 5v6c0 5 3.4 9.4 8 11 4.6-1.6 8-6 8-11V5l-8-3zm0 2.2 6 2.2V11c0 4-2.6 7.6-6 9-3.4-1.4-6-5-6-9V6.4l6-2.2z"/>' ),
		'pillar'     => array( 'label' => __( 'Pillar', 'brooks-law-30-pro' ),            'svg' => '<path fill="currentColor" d="M7 3h10v2H7V3zm1 3h8l-1 1v10l1 1v1H7v-1l1-1V7L7 6h1zm2 2v8h1.2V8H10zm2.8 0v8H14V8h-1.2zM6 21h12v-1H6v1z"/>' ),
		'map'        => array( 'label' => __( 'Map', 'brooks-law-30-pro' ),               'svg' => '<path fill="currentColor" d="m9 3 6 2 5.6-1.9c.2-.1.4.1.4.3V18l-6 2-6-2-5.6 1.9c-.2.1-.4-.1-.4-.3V6l6-3zm0 2.2L5 6.7v11.6l4-1.4V5.2zm2 .1v11.5l4 1.3V6.6l-4-1.3zm10 .3-4 1.4v11.5l4-1.4V5.6z"/>' ),
		'star'       => array( 'label' => __( 'Star', 'brooks-law-30-pro' ),              'svg' => '<path fill="currentColor" d="m12 2 2.9 6.3 6.9.7-5.2 4.6 1.5 6.8L12 16.9l-6.1 3.5 1.5-6.8L2.2 9l6.9-.7L12 2z"/>' ),
		'document'   => array( 'label' => __( 'Document', 'brooks-law-30-pro' ),          'svg' => '<path fill="currentColor" d="M6 2h9l5 5v15H6V2zm8 2H8v16h10V9h-4V4zm2 .8V7h2.2L16 4.8zM9 11h8v1.5H9V11zm0 3h8v1.5H9V14zm0 3h5v1.5H9V17z"/>' ),
		'compass'    => array( 'label' => __( 'Compass', 'brooks-law-30-pro' ),           'svg' => '<path fill="currentColor" d="M12 2a10 10 0 1 0 0 20 10 10 0 0 0 0-20zm0 2a8 8 0 1 1 0 16 8 8 0 0 1 0-16zm4.5 3.5-3 6-6 3 3-6 6-3zM12 11a1 1 0 1 0 0 2 1 1 0 0 0 0-2z"/>' ),
		'truck'      => array( 'label' => __( 'Truck', 'brooks-law-30-pro' ),             'svg' => '<path fill="currentColor" d="M2 6h11v9.3a3.5 3.5 0 0 0-1.7 2.2H8.7a3.5 3.5 0 0 0-6.7 0V6zm13 3h3.6L22 13.4V17.5h-1.2a3.5 3.5 0 0 0-3.8-2.4V9zm1.8 1.8v2.7h3l-1.6-2.7h-1.4zM5.5 15.5a2.3 2.3 0 1 1 0 4.6 2.3 2.3 0 0 1 0-4.6zm12.5 0a2.3 2.3 0 1 1 0 4.6 2.3 2.3 0 0 1 0-4.6zm-12.5 1.3a1 1 0 1 0 0 2 1 1 0 0 0 0-2zm12.5 0a1 1 0 1 0 0 2 1 1 0 0 0 0-2z"/>' ),
		'tractor'    => array( 'label' => __( 'Tractor (farming)', 'brooks-law-30-pro' ), 'svg' => '<path fill="currentColor" d="M6 5h6l1.5 5H17V7h2v3h2.4l.6 2h-2.2a4.5 4.5 0 0 1 2 3.2l-1.9.5A2.9 2.9 0 0 0 17.2 14a2.9 2.9 0 0 0-2.9 2.9c0 .3 0 .6.1.8h-2.6a5.3 5.3 0 0 0-9.8-2.5V10h2L6 5zm2 1.8L6.6 10h5l-1-3.2H8zM6.3 15.3a3.6 3.6 0 1 1 0 7.2 3.6 3.6 0 0 1 0-7.2zm0 2a1.6 1.6 0 1 0 0 3.2 1.6 1.6 0 0 0 0-3.2zm10.9-1.6a2.2 2.2 0 1 1 0 4.4 2.2 2.2 0 0 1 0-4.4zm0 1.4a.8.8 0 1 0 0 1.6.8.8 0 0 0 0-1.6z"/>' ),
		'tree'       => array( 'label' => __( 'Tree', 'brooks-law-30-pro' ),              'svg' => '<path fill="currentColor" d="M12 2 6.5 9.5h2.3L5 15h2.7L4 20h7v2h2v-2h7l-3.7-5H19l-3.8-5.5h2.3L12 2z"/>' ),
		'skyline'    => array( 'label' => __( 'City skyline', 'brooks-law-30-pro' ),      'svg' => '<path fill="currentColor" d="M3 21V11h4v10H3zm2-8H4v1.5h1V13zm0 3H4v1.5h1V16zM8 21V4h6v17h-2v-3h-2v3H8zm2-14.5H9V8h1V6.5zm2.5 0h-1V8h1V6.5zM10 9.5H9V11h1V9.5zm2.5 0h-1V11h1V9.5zM10 12.5H9V14h1v-1.5zm2.5 0h-1V14h1v-1.5zM15 21V8h6v13h-6zm2.5-10.5h-1V12h1v-1.5zm2 0h-1V12h1v-1.5zm-2 3h-1V15h1v-1.5zm2 0h-1V15h1v-1.5zm-2 3h-1V18h1v-1.5zm2 0h-1V18h1v-1.5z"/>' ),
		'music'      => array( 'label' => __( 'Musical note', 'brooks-law-30-pro' ),      'svg' => '<path fill="currentColor" d="M19 3v11.3a3.2 3.2 0 1 1-2-3V7.4l-7 1.9v8a3.2 3.2 0 1 1-2-3V6l11-3z"/>' ),
		'river'      => array( 'label' => __( 'River', 'brooks-law-30-pro' ),             'svg' => '<path fill="currentColor" d="M3 6.5c1.5-1.4 3-1.4 4.5 0s3 1.4 4.5 0 3-1.4 4.5 0 3 1.4 4.5 0V9c-1.5 1.4-3 1.4-4.5 0s-3-1.4-4.5 0-3 1.4-4.5 0-3-1.4-4.5 0V6.5zm0 5c1.5-1.4 3-1.4 4.5 0s3 1.4 4.5 0 3-1.4 4.5 0 3 1.4 4.5 0V14c-1.5 1.4-3 1.4-4.5 0s-3-1.4-4.5 0-3 1.4-4.5 0-3-1.4-4.5 0v-2.5zm0 5c1.5-1.4 3-1.4 4.5 0s3 1.4 4.5 0 3-1.4 4.5 0 3 1.4 4.5 0V19c-1.5 1.4-3 1.4-4.5 0s-3-1.4-4.5 0-3 1.4-4.5 0-3-1.4-4.5 0v-2.5z"/>' ),
		'corn'       => array( 'label' => __( 'Corn field', 'brooks-law-30-pro' ),        'svg' => '<path fill="currentColor" d="M12 2c1.8 1.2 2.8 3.4 2.8 6.2 0 4.3-1.5 8-2.8 9.8-1.3-1.8-2.8-5.5-2.8-9.8C9.2 5.4 10.2 3.2 12 2zm0 3.2c-.6.9-.9 1.9-.9 3 0 2.4.4 4.6.9 6.2.5-1.6.9-3.8.9-6.2 0-1.1-.3-2.1-.9-3zM6.2 8c1.9.4 3 1.7 3.4 3.5.3 1.4.2 2.9-.2 4.1-1.6-.7-2.8-1.9-3.3-3.5C5.7 10.7 5.7 9.3 6.2 8zm11.6 0c.5 1.3.5 2.7.1 4.1-.5 1.6-1.7 2.8-3.3 3.5-.4-1.2-.5-2.7-.2-4.1.4-1.8 1.5-3.1 3.4-3.5zM11 18.5h2V22h-2v-3.5z"/>' ),
		'wheat'      => array( 'label' => __( 'Wheat field', 'brooks-law-30-pro' ),       'svg' => '<path fill="currentColor" d="M11 22V10h2v12h-2zM12 2c1 .8 1.6 2 1.6 3.2S13 7.6 12 8.4c-1-.8-1.6-2-1.6-3.2S11 2.8 12 2zM7.4 6.2c1.3.1 2.4.8 3 1.9.5 1 .6 2.2.2 3.2-1.3-.1-2.4-.8-3-1.9-.5-1-.6-2.2-.2-3.2zm9.2 0c.4 1 .3 2.2-.2 3.2-.6 1.1-1.7 1.8-3 1.9-.4-1-.3-2.2.2-3.2.6-1.1 1.7-1.8 3-1.9zM7.4 11.2c1.3.1 2.4.8 3 1.9.5 1 .6 2.2.2 3.2-1.3-.1-2.4-.8-3-1.9-.5-1-.6-2.2-.2-3.2zm9.2 0c.4 1 .3 2.2-.2 3.2-.6 1.1-1.7 1.8-3 1.9-.4-1-.3-2.2.2-3.2.6-1.1 1.7-1.8 3-1.9z"/>' ),
		'cotton'     => array( 'label' => __( 'Cotton field', 'brooks-law-30-pro' ),      'svg' => '<path fill="currentColor" d="M12 3a3.4 3.4 0 0 1 3.3 2.6 3.4 3.4 0 0 1 2.6 5A3.4 3.4 0 0 1 15 15.9a3.4 3.4 0 0 1-6 0 3.4 3.4 0 0 1-2.9-5.3 3.4 3.4 0 0 1 2.6-5A3.4 3.4 0 0 1 12 3zm0 2a1.4 1.4 0 0 0-1.4 1.5l.1.9-.9-.2a1.4 1.4 0 0 0-1.1 2.5l.7.5-.7.5a1.4 1.4 0 0 0 1.1 2.5l.9-.2-.1.9a1.4 1.4 0 0 0 2.8 0l-.1-.9.9.2a1.4 1.4 0 0 0 1.1-2.5l-.7-.5.7-.5a1.4 1.4 0 0 0-1.1-2.5l-.9.2.1-.9A1.4 1.4 0 0 0 12 5zm-1 12.6 1.5 1V22h-2v-3.2l.5-1.2z"/>' ),
		'riverboat'  => array( 'label' => __( 'Paddlewheel riverboat', 'brooks-law-30-pro' ), 'svg' => '<path fill="currentColor" d="M7 4h2v2h2v2h4V6h2v4.5l-1 .5h-9V4zm-1 8h11.5a4.3 4.3 0 0 1 4.3 3.2l-1.6 2.3H3.8L2 14.6l4-2.6zm-.6 2-1.6 1 .9 1.5h13.9l.8-1.1a2.3 2.3 0 0 0-1.9-1.4H5.4zM16 12.2a3.9 3.9 0 0 1 3.4-2.1 3.9 3.9 0 0 1 3.5 2.1l-1.7 1a1.9 1.9 0 0 0-1.8-1.1 1.9 1.9 0 0 0-1.7 1.1l-1.7-1zM3 19h18v2H3v-2zM8 4h6v1H8V4z"/>' ),
		'barge'      => array( 'label' => __( 'Barge', 'brooks-law-30-pro' ),             'svg' => '<path fill="currentColor" d="M4 9h6v3H4V9zm7 0h6v3h-6V9zm-8 4h18l-2 4H4l-1-4zm14-8h3v3h-3V5zM2 19c1.3 1.1 2.7 1.1 4 0s2.7-1.1 4 0 2.7 1.1 4 0 2.7-1.1 4 0 2.7 1.1 4 0v2c-1.3 1.1-2.7 1.1-4 0s-2.7-1.1-4 0-2.7 1.1-4 0-2.7-1.1-4 0-2.7 1.1-4 0v-2z"/>' ),
		'beale'      => array( 'label' => __( 'Beale Street (guitar)', 'brooks-law-30-pro' ), 'svg' => '<path fill="currentColor" d="m19.6 2.6 1.8 1.8-4.9 4.9c.6 1.2.4 2.6-.6 3.6l-.9.9c.3.9.1 2-.7 2.7l-2.5 2.6c-2 2-5.2 2-7.1 0-2-2-2-5.2 0-7.1L7.3 9.4c.8-.8 1.8-1 2.7-.7l.9-.9c1-1 2.4-1.2 3.6-.6l5.1-4.6zM11.8 9.9l-.7.7.6.6a1.6 1.6 0 0 1 0 2.3 1.6 1.6 0 0 1-2.3 0l-.6-.6-2.7 2.7a3 3 0 0 0 0 4.3 3 3 0 0 0 4.3 0l2.6-2.6-.6-.6 1.4-1.4.7.7.6-.6c.5-.5.5-1.2.1-1.7l-2.2-2.2c-.5-.4-.7-.1-1.2.4z"/>' ),
		'magnolia'   => array( 'label' => __( 'Magnolia blossom', 'brooks-law-30-pro' ),  'svg' => '<path fill="currentColor" d="M12 2.5c1.2 1.3 1.8 2.8 1.8 4.3 0 .5-.1 1-.2 1.4a5.6 5.6 0 0 1 4.7-1.4c.2 1.8-.3 3.4-1.3 4.6-.3.4-.7.7-1.1 1 1.7.2 3.2 1 4.3 2.3-1.1 1.4-2.6 2.2-4.3 2.3l-1.1-.1c.7 1.6.7 3.3.1 4.9-1.7-.4-3.1-1.4-3.9-2.8-.8 1.4-2.2 2.4-3.9 2.8-.6-1.6-.6-3.3.1-4.9l-1.1.1c-1.7-.1-3.2-.9-4.3-2.3 1.1-1.3 2.6-2.1 4.3-2.3-.4-.3-.8-.6-1.1-1-1-1.2-1.5-2.8-1.3-4.6a5.6 5.6 0 0 1 4.7 1.4c-.1-.4-.2-.9-.2-1.4 0-1.5.6-3 1.8-4.3zM12 9a3 3 0 1 0 0 6 3 3 0 0 0 0-6z"/>' ),
		'tomato'     => array( 'label' => __( 'Tomato (Ripley)', 'brooks-law-30-pro' ),   'svg' => '<path fill="currentColor" d="M13 2h2l-1.4 1.9c1 .2 2 .1 3-.2l.7 1.8c-1.2.5-2.5.6-3.8.4.1.3.2.7.2 1 3.9.3 7 3.3 7 7.1 0 4-3.5 7-8.7 7S3.3 18 3.3 14c0-3.8 3.1-6.8 7-7.1 0-.3.1-.7.2-1-1.3.2-2.6.1-3.8-.4l.7-1.8c1 .3 2 .4 3 .2L9 2h2c.2.5.3 1.1.3 1.6h1.4c0-.5.1-1.1.3-1.6zm-1 6.8c-3.7 0-6.7 2.2-6.7 5.2 0 2.9 2.6 5 6.7 5s6.7-2.1 6.7-5c0-3-3-5.2-6.7-5.2z"/>' ),
		'eagle'      => array( 'label' => __( 'Bald eagle', 'brooks-law-30-pro' ),        'svg' => '<path fill="currentColor" d="M14.2 3c1.6 0 3 .8 3.8 2l2.4.8-1.9 1c.2.5.3 1 .3 1.5 0 1.5-.7 2.9-1.8 3.8l3 5.9-5.3-2.3-.9 2.5-1.5-1.7-2.6 5-1-4.5-3.2 2.2 1.2-3.6-4.7.5 3.5-2.9C4 12.4 3.3 10.6 3.3 8.7c1.9.6 3.5 1.6 4.8 3l1.2 1.4c.4-.2.8-.4 1.1-.7-1.2-1.4-1.9-3.1-2.2-5 1.7.4 3.2 1.3 4.4 2.6.2-.4.3-.8.3-1.3 0-2.6-.1-3.5 1.3-5.7zm1.9 3.2a.8.8 0 1 0 0 1.6.8.8 0 0 0 0-1.6z"/>' ),
		'cypress'    => array( 'label' => __( 'Cypress tree', 'brooks-law-30-pro' ),      'svg' => '<path fill="currentColor" d="M12 2c2.6 2.2 4 5.4 4 8.6 0 2.9-1.1 5.4-2.9 6.9l.1.5H16l1.5-1.2.9 1-1.4 1.2H13v3h-2v-3H6.9l-1.4-1.2.9-1L7.9 18h2.9l.1-.5C9.1 16 8 13.5 8 10.6 8 7.4 9.4 4.2 12 2zm0 3c-1.3 1.6-2 3.6-2 5.6 0 2.3.8 4.3 2 5.6 1.2-1.3 2-3.3 2-5.6 0-2-.7-4-2-5.6zM3 21h18v1.5H3V21z"/>' ),
		'banjo'      => array( 'label' => __( 'Banjo', 'brooks-law-30-pro' ),             'svg' => '<path fill="currentColor" d="m19.9 2.7 1.4 1.4-1.2 1.2.5.5-1.4 1.4-.5-.5-4.9 4.9a5.5 5.5 0 1 1-1.5-1.5l4.9-4.9-.5-.5 1.4-1.4.5.5 1.3-1.1zM8.5 11.5a3.5 3.5 0 1 0 0 7 3.5 3.5 0 0 0 0-7zm0 1.5a2 2 0 1 1 0 4 2 2 0 0 1 0-4z"/>' ),
		'steel'      => array( 'label' => __( 'Steel guitar', 'brooks-law-30-pro' ),      'svg' => '<path fill="currentColor" d="M3 7h18v7H3V7zm2 2v3h14V9H5zm1 .8h12v.6H6v-.6zm0 1.2h12v.6H6V11zM5 16h2l-1 4H4l1-4zm12 0h2l1 4h-2l-1-4zM8 5h2v2H8V5zm6 0h2v2h-2V5z"/>' ),
		'country'    => array( 'label' => __( 'Country music (fiddle)', 'brooks-law-30-pro' ), 'svg' => '<path fill="currentColor" d="m18.9 2.3 2.8 2.8-1.3 1.3-.5-.2-2.5 2.5.2 1.1-1.2 1.2c.6 1.6.3 3.4-1 4.6-.5.6-.8.6-1.2 1.5-.3.9 0 1.7-.8 2.6-1.1 1.1-2.9 1.1-4 0-.4-.4-.7-1-.7-1.5-.6-.1-1.1-.3-1.6-.8-1.1-1.1-1.1-2.9 0-4 .8-.8 1.7-.5 2.6-.8.9-.3.9-.7 1.5-1.2 1.2-1.2 3-1.5 4.6-1l1.2-1.2 1.1.2 2.4-2.6-.2-.5 1.4-1.3.7.5.5-1.2zM8.2 15.2a1 1 0 1 0 0 2 1 1 0 0 0 0-2z"/>' ),
		'cowboy'     => array( 'label' => __( 'Cowboy hat', 'brooks-law-30-pro' ),        'svg' => '<path fill="currentColor" d="M9.5 4c.9-.7 2-.7 2.5-.7s1.6 0 2.5.7c.9.8 1.6 3.7 2 6.5.6 2 1.3 2.1 3.4 1.7l2.1-.5c-.3 2.3-3.3 5.3-10 5.3S2.3 14 2 11.7l2.1.5c2.1.4 2.8.3 3.4-1.7.4-2.8 1.1-5.7 2-6.5zm2.5 1.3c-.5 0-1 .1-1.2.3-.4.3-.9 2.5-1.3 5.1 1.7.5 3.3.5 5 0-.4-2.6-.9-4.8-1.3-5.1-.2-.2-.7-.3-1.2-.3zM4.6 14.4c1.9 1 4.4 1.6 7.4 1.6s5.5-.6 7.4-1.6c-1.7 1.9-4.2 2.6-7.4 2.6s-5.7-.7-7.4-2.6z"/>' ),
		'horse'      => array( 'label' => __( 'Horse', 'brooks-law-30-pro' ),             'svg' => '<path fill="currentColor" d="m14 2 2.5 2.5L19 5l1.5 3-2 .5-1.2-1c-.2 1.1-.8 2.1-1.7 2.8l.9 4.2 1 6.5h-2.2l-1-6-2.6 1.3.8 4.7H10.3l-.8-4.5c-.2-1 .3-2 1.2-2.5l1.9-1c-1.7-.1-3.2-1-4.1-2.4L6 12.4 5.7 16H3.9l.3-4.2c.1-.9.6-1.7 1.4-2.1l2.7-1.4C8.6 5.2 11 3 14 3V2zm1.5 4.2a.8.8 0 1 0 0 1.6.8.8 0 0 0 0-1.6z"/>' ),
		'waterfall'  => array( 'label' => __( 'Waterfall', 'brooks-law-30-pro' ),         'svg' => '<path fill="currentColor" d="M3 3h7v2H8.5c.8 1 1.3 2.3 1.3 3.7V15h-2V8.7C7.8 7.2 6.6 6 5.1 6H3V3zm9 1h2v11h-2V4zm4.8 0h2v11h-2V4zM2 18c1.3 1.1 2.7 1.1 4 0s2.7-1.1 4 0 2.7 1.1 4 0 2.7-1.1 4 0 2.7 1.1 4 0v2.5c-1.3 1.1-2.7 1.1-4 0s-2.7-1.1-4 0-2.7 1.1-4 0-2.7-1.1-4 0-2.7 1.1-4 0V18z"/>' ),
		'bass'       => array( 'label' => __( 'Largemouth bass', 'brooks-law-30-pro' ),   'svg' => '<path fill="currentColor" d="M4 8.5 7.5 11c1.8-2.4 4.6-4 7.9-4 2.6 0 5 1 6.6 2.7L20.5 12l1.5 2.3C20.4 16 18 17 15.4 17c-3.3 0-6.1-1.6-7.9-4L4 15.5c.5-1.2.8-2.4.8-3.5S4.5 9.7 4 8.5zm11.4.7c-1.9 0-3.7.8-5 2.1l-.6.7.6.7c1.3 1.3 3.1 2.1 5 2.1 1.6 0 3.1-.5 4.3-1.4l-.9-1.4.9-1.4a7.1 7.1 0 0 0-4.3-1.4zm2.1 1.6a.8.8 0 1 1 0 1.6.8.8 0 0 1 0-1.6zM10 6.5c1-.9 2.3-1.5 3.7-1.7-.4.8-1 1.5-1.8 2L10 6.5zm0 11c.6-.1 1.3-.2 1.9-.3.8.5 1.4 1.2 1.8 2-1.4-.2-2.7-.8-3.7-1.7z"/>' ),
		'catfish'    => array( 'label' => __( 'Catfish', 'brooks-law-30-pro' ),           'svg' => '<path fill="currentColor" d="M9.5 8C12 6.6 15 6.4 17.8 7.6c1.6.7 2.9 1.8 3.7 3.2l-1.6.6 1.6 1c-.8 1.4-2.1 2.5-3.7 3.2A9.5 9.5 0 0 1 9.5 16L6 18.5c.4-1.4.6-2.9.5-4.4L2 13.5l4.4-1L2 11.6l4.5-.7c.1-1.5-.1-3-.5-4.4L9.5 8zm7.8 1.5c-2-.9-4.3-.8-6.3.2l-1.5.8c.1.5.1 1 .1 1.5s0 1-.1 1.5l1.5.8c2 1 4.3 1.1 6.3.2 1-.4 1.9-1.1 2.5-1.9l-1-.6 1-.6c-.6-.8-1.5-1.5-2.5-1.9zm-1 1.6a.8.8 0 1 1 0 1.6.8.8 0 0 1 0-1.6z"/>' ),
		'mockingbird' => array( 'label' => __( 'Mockingbird', 'brooks-law-30-pro' ),      'svg' => '<path fill="currentColor" d="M15.5 3a3.5 3.5 0 0 1 3.4 2.6L22 6.7l-2.6 1.4c.1 2.6-.8 5.2-2.6 7.2l1.7 3.2-3-1c-1.4.9-3 1.4-4.7 1.5L9 22H7.5l1.5-3.2c-2.5-.4-4.7-1.9-6-4.2l6.8.9c.9.1 1.8-.2 2.4-.9l.9-.9L4 14.9c1.1-2.9 3.6-5 6.6-5.6l1.6-.3c.4-3.3 1.5-6 3.3-6zm1 2.7a.8.8 0 1 0 0 1.6.8.8 0 0 0 0-1.6z"/>' ),
		'sailboat'   => array( 'label' => __( 'Sailboat', 'brooks-law-30-pro' ),          'svg' => '<path fill="currentColor" d="M12 2c3.2 2.7 5.1 6.6 5.3 10.8H13V2h-1zm-1 2.9V12.8H5.2C6 9 8 5.7 11 4.9v.0zM3.5 15h17l-1.5 3h-14l-1.5-3zM2 20c1.3 1.1 2.7 1.1 4 0s2.7-1.1 4 0 2.7 1.1 4 0 2.7-1.1 4 0 2.7 1.1 4 0v2c-1.3 1.1-2.7 1.1-4 0s-2.7-1.1-4 0-2.7 1.1-4 0-2.7-1.1-4 0-2.7 1.1-4 0v-2z"/>' ),
		'semi'       => array( 'label' => __( '18-wheeler', 'brooks-law-30-pro' ),        'svg' => '<path fill="currentColor" d="M1 5h6v11.1a3.2 3.2 0 0 0-1.6 1.9H4.6A3.2 3.2 0 0 0 1 15.9V5zm7 1h3.4l3 3.5v6.6a3.2 3.2 0 0 0-2 1.9H8V6zm1.4 1.6v2.5h3.2l-2.1-2.5H9.4zM15.5 5H23v11.6a3.2 3.2 0 0 0-1.7 1.4h-6.1a3.2 3.2 0 0 0-1.7-1.4V9.2L15.5 5zM3.9 16.9a1.9 1.9 0 1 1 0 3.8 1.9 1.9 0 0 1 0-3.8zm0 1.1a.8.8 0 1 0 0 1.6.8.8 0 0 0 0-1.6zm10.3-1.1a1.9 1.9 0 1 1 0 3.8 1.9 1.9 0 0 1 0-3.8zm0 1.1a.8.8 0 1 0 0 1.6.8.8 0 0 0 0-1.6zm5.9-1.1a1.9 1.9 0 1 1 0 3.8 1.9 1.9 0 0 1 0-3.8zm0 1.1a.8.8 0 1 0 0 1.6.8.8 0 0 0 0-1.6z"/>' ),
		'stoplight'  => array( 'label' => __( 'Stoplight', 'brooks-law-30-pro' ),         'svg' => '<path fill="currentColor" d="M8 2h8a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-2v2h-4v-2H8a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2zm0 2v14h8V4H8zm-4 1h2v3l-2-1V5zm14 0h2v2l-2 1V5zM4 10h2v3l-2-1v-2zm14 0h2v2l-2 1v-3zM4 15h2v3l-2-1v-2zm14 0h2v2l-2 1v-3zM12 5.3a1.7 1.7 0 1 1 0 3.4 1.7 1.7 0 0 1 0-3.4zm0 4.9a1.7 1.7 0 1 1 0 3.4 1.7 1.7 0 0 1 0-3.4zm0 4.9a1.7 1.7 0 1 1 0 3.4 1.7 1.7 0 0 1 0-3.4z"/>' ),
		'gun'        => array( 'label' => __( 'Firearm', 'brooks-law-30-pro' ),           'svg' => '<path fill="currentColor" d="M2 7h19v2h-1v2.5a2 2 0 0 1-2 2h-6.2l-.9 2.6a2 2 0 0 1-1.9 1.4H5.2l1.6-4H5a3 3 0 0 1-3-3V7zm2 2v2.5c0 .6.4 1 1 1h4.7l3-.1H18V9H4zm4.2 5.5-.8 2h1.7l.7-2H8.2z"/>' ),
		'pills'      => array( 'label' => __( 'Prescription bottle', 'brooks-law-30-pro' ), 'svg' => '<path fill="currentColor" d="M7 2h10v3H7V2zm1 4h8a1 1 0 0 1 1 1v13a2 2 0 0 1-2 2H9a2 2 0 0 1-2-2V7a1 1 0 0 1 1-1zm1 2v11h6V8H9zm1 2h4l-3 3h3l-4 4v-3h-1l1-4z"/>' ),
		'pen'        => array( 'label' => __( 'Fountain pen', 'brooks-law-30-pro' ),      'svg' => '<path fill="currentColor" d="m18.5 2 3.5 3.5-2.2 2.2-3.5-3.5L18.5 2zm-3.6 3.6 3.5 3.5-8.1 8.1-4.6 1.7c-.5.2-1-.3-.8-.8l1.7-4.6 8.3-7.9zm-7 8.4-.9 2.4 2.4-.9 6.5-6.5-1.4-1.4-6.6 6.4zM3 20h18v2H3v-2z"/>' ),
		'scroll'     => array( 'label' => __( 'Declaration scroll', 'brooks-law-30-pro' ), 'svg' => '<path fill="currentColor" d="M6 2h13a3 3 0 0 1 3 3c0 1.2-.7 2.2-1.7 2.7V7a1.3 1.3 0 0 0-2.6 0v12a3 3 0 0 1-3 3H5a3 3 0 0 1-3-3c0-1.2.7-2.2 1.7-2.7V19a1.3 1.3 0 0 0 2.6 0V5a3 3 0 0 1 .7-3zm2.2 2c-.1.3-.2.6-.2 1v14c0 .4-.1.7-.2 1h6.9a1 1 0 0 0 1-1V5c0-.4.1-.7.2-1H8.2zM10 7h5v1.5h-5V7zm0 3h5v1.5h-5V10zm0 3h3.5v1.5H10V13z"/>' ),
		'bell'       => array( 'label' => __( 'Liberty Bell', 'brooks-law-30-pro' ),      'svg' => '<path fill="currentColor" d="M8 2h8v2h-1c1.9 1.3 3 3.5 3 5.9 0 1.7.5 3.3 1.5 4.6l.5.7V17H4v-1.8l.5-.7c1-1.3 1.5-2.9 1.5-4.6C6 7.5 7.1 5.3 9 4H8V2zm4 3.1c-2.3 0-4 2.1-4 4.8 0 1.9-.5 3.7-1.5 5.1h4.1l.7-2.5-.9-1.3 1.2-2.9h1l-1 2.6.9 1.3-.8 2.8h4.8c-1-1.4-1.5-3.2-1.5-5.1 0-2.7-1.7-4.8-4-4.8zM9.5 18h5a2.5 2.5 0 0 1-5 0z"/>' ),
		'hotrod'     => array( 'label' => __( 'Hot rod', 'brooks-law-30-pro' ),           'svg' => '<path fill="currentColor" d="M14 6h4l3 4.5h.5a1.5 1.5 0 0 1 1.5 1.5v3h-2.1a3 3 0 0 0-5.8 0h-4.2a3.4 3.4 0 0 0-6.6 0H1v-2.5L3.5 11 5 8h6l3-2zm-.6 2-2.1 1.4-.5.6h6.7L15.9 8h-2.5zM7.7 13.6a2.3 2.3 0 1 1 0 4.6 2.3 2.3 0 0 1 0-4.6zm0 1.4a.9.9 0 1 0 0 1.8.9.9 0 0 0 0-1.8zm10.2-.8a2 2 0 1 1 0 4 2 2 0 0 1 0-4zm0 1.2a.8.8 0 1 0 0 1.6.8.8 0 0 0 0-1.6zM2 8h2v1.5H2V8z"/>' ),
		'cadillac'   => array( 'label' => __( 'Old Cadillac', 'brooks-law-30-pro' ),      'svg' => '<path fill="currentColor" d="M6 8h9l3 3h2.5a1.5 1.5 0 0 1 1.5 1.5V15h-2.1a2.9 2.9 0 0 0-5.6 0H9.4a2.9 2.9 0 0 0-5.6 0H1v-2l2-1 3-4zm1 1.6L5.2 12h4.3V9.6H7zm4 0V12h4.6l-2.4-2.4H11zM6.6 14.1a1.9 1.9 0 1 1 0 3.8 1.9 1.9 0 0 1 0-3.8zm0 1.1a.8.8 0 1 0 0 1.6.8.8 0 0 0 0-1.6zm10.6-1.1a1.9 1.9 0 1 1 0 3.8 1.9 1.9 0 0 1 0-3.8zm0 1.1a.8.8 0 1 0 0 1.6.8.8 0 0 0 0-1.6zM21 12.5h2v1h-2v-1z"/>' ),
		'pickup'     => array( 'label' => __( 'Old pickup truck', 'brooks-law-30-pro' ),  'svg' => '<path fill="currentColor" d="M4 6h8v5h2.5L17 8h3l2 3.5V15h-1.6a3 3 0 0 0-5.8 0h-4.2a3 3 0 0 0-5.8 0H2V9l2-3zm2 2-.9 1.5V11h4.9V8H6zm11.6 2 .9 1h2l-1-1h-1.9zM7.5 13.6a2.2 2.2 0 1 1 0 4.4 2.2 2.2 0 0 1 0-4.4zm0 1.3a.9.9 0 1 0 0 1.8.9.9 0 0 0 0-1.8zm10 .1a2.2 2.2 0 1 1 0 4.4 2.2 2.2 0 0 1 0-4.4zm0 1.3a.9.9 0 1 0 0 1.8.9.9 0 0 0 0-1.8z"/>' ),
		'carriage'   => array( 'label' => __( 'Horse carriage', 'brooks-law-30-pro' ),    'svg' => '<path fill="currentColor" d="M3 5h8a3 3 0 0 1 3 3v3h3.5l3.5 2v2h-1.7a3.3 3.3 0 0 0-4.2-1.9A3.3 3.3 0 0 0 13 15H9.9a4.3 4.3 0 0 0-8.4.5L3 12V5zm2 2v4.6l1.5-.6H12V8a1 1 0 0 0-1-1H5zm.7 6.9a2.8 2.8 0 1 1 0 5.6 2.8 2.8 0 0 1 0-5.6zm0 1.4a1.4 1.4 0 1 0 0 2.8 1.4 1.4 0 0 0 0-2.8zm11 .2a2.1 2.1 0 1 1 0 4.2 2.1 2.1 0 0 1 0-4.2zm0 1.2a.9.9 0 1 0 0 1.8.9.9 0 0 0 0-1.8z"/>' ),
		'buffalo'    => array( 'label' => __( 'Buffalo', 'brooks-law-30-pro' ),           'svg' => '<path fill="currentColor" d="M4.5 6C5.6 4.7 7.3 4 9 4h6.5c1.5 0 2.9.7 3.8 1.9l.5.7L22 6v2l-2 .5c.3.7.5 1.5.5 2.3V12l-1.5 1 .8 5h-2.1l-.7-4.3-2 .8.5 3.5h-2.1l-.4-3.2H9.9L9.5 18H7.4l.5-3.6C6.1 13.5 5 11.9 5 10c0-.5.1-1 .2-1.5L2 8V6l2.5.4V6zm4.5 0c-1.6 0-2.9 1.2-3 2.8-.1 1.7 1 3.2 2.7 3.6l6.1 1.5 2.7-1.1v-1c0-.6-.1-1.1-.4-1.6L15.9 8.5c-.5-1.5-1.9-2.5-3.4-2.5H9zm8.7 1.3a.8.8 0 1 0 0 1.6.8.8 0 0 0 0-1.6z"/>' ),
		'pyramid'    => array( 'label' => __( 'Pyramid (Memphis)', 'brooks-law-30-pro' ), 'svg' => '<path fill="currentColor" d="M12 3 22.5 18h-21L12 3zm0 3.5L5.4 16h4.1L12 8.9 14.5 16h4.1L12 6.5zM12 12l-1.4 4h2.8L12 12zM1 19.5h22V21H1v-1.5z"/>' ),
		'neon'       => array( 'label' => __( 'Beale St neon sign', 'brooks-law-30-pro' ), 'svg' => '<path fill="currentColor" d="M4 2h3v20H4V2zm4 1h13a1 1 0 0 1 1 1v9a1 1 0 0 1-1 1H8V3zm2 2v7h10V5H10zm1.5 1.3h2.2c.8 0 1.3.5 1.3 1.1 0 .4-.2.8-.6.9.5.2.8.6.8 1.1 0 .7-.6 1.2-1.4 1.2h-2.3V6.3zm1.2 1v.7h.9c.2 0 .4-.2.4-.4s-.2-.3-.4-.3h-.9zm0 1.6v.8h1c.3 0 .5-.2.5-.4s-.2-.4-.5-.4h-1zm5.1-2.6h1.2v3.1h1.8v1.1h-3V6.3zM8 16h9l-1.5 2H8v-2z"/>' ),
		'handcuffs'  => array( 'label' => __( 'Handcuffs', 'brooks-law-30-pro' ),         'svg' => '<path fill="currentColor" fill-rule="evenodd" d="M7.3 4a4.6 4.6 0 1 0 0 9.2 4.6 4.6 0 0 0 0-9.2zm0 2a2.6 2.6 0 1 1 0 5.2 2.6 2.6 0 0 1 0-5.2zm9.4 4.8a4.6 4.6 0 1 0 0 9.2 4.6 4.6 0 0 0 0-9.2zm0 2a2.6 2.6 0 1 1 0 5.2 2.6 2.6 0 0 1 0-5.2z"/><path fill="currentColor" d="m12 10.2 2 1.8-2 1.8-2-1.8 2-1.8zM2.4 2.2l2.3-1 1.2 2.7-2.3 1-1.2-2.7zm15.7 17.9 2.3-1 1.2 2.7-2.3 1-1.2-2.7z"/>' ),
		'prisonbars' => array( 'label' => __( 'Prison bars', 'brooks-law-30-pro' ),       'svg' => '<path fill="currentColor" d="M3 3h18v2H3V3zm0 16h18v2H3v-2zM4 6h2v12H4V6zm4.7 0h2v12h-2V6zm4.6 0h2v12h-2V6zm4.7 0h2v12h-2V6z"/>' ),
		'barsign'    => array( 'label' => __( 'Neon bar sign', 'brooks-law-30-pro' ),     'svg' => '<path fill="currentColor" d="M3 2h2v20H3V2zm3 2h14.5a1 1 0 0 1 1 1v11a1 1 0 0 1-1 1H6V4zm2 2v9h11.5V6H8z"/><path fill="currentColor" d="M9.7 7.5h7.2l-2.7 3v1.9h1.6V14h-5v-1.6h1.6v-1.9l-2.7-3zm2.2 1.3 1.4 1.6 1.4-1.6h-2.8z"/>' ),
		'purpleheart' => array( 'label' => __( 'Purple Heart medal', 'brooks-law-30-pro' ), 'svg' => '<path fill="currentColor" d="M7.5 2h9L15 8.4h-6L7.5 2zm2 1.8.6 2.8h3.8l.6-2.8H9.5z"/><path fill="currentColor" d="M12 10.6c1-1.3 2.7-1.7 4.1-.9 1.7 1 2.2 3.4 1 5.1L12 21.5l-5.1-6.7c-1.2-1.7-.7-4.1 1-5.1 1.4-.8 3.1-.4 4.1.9z"/>' ),
		'house'      => array( 'label' => __( 'House', 'brooks-law-30-pro' ),             'svg' => '<path fill="currentColor" d="M12 3 2 11.2h2.8V21H10v-6h4v6h5.2v-9.8H22L12 3zm0 2.6 5.2 4.3V19H16v-6H8v6H6.8V9.9L12 5.6zM15.5 4h2.3v2.6l-2.3-1.9V4z"/>' ),
		'office'     => array( 'label' => __( 'Office building', 'brooks-law-30-pro' ),   'svg' => '<path fill="currentColor" fill-rule="evenodd" d="M4 3h10v18H4V3zm3.1 3h1.8v1.8H7.1V6zm3.7 0h1.8v1.8h-1.8V6zM7.1 9.6h1.8v1.8H7.1V9.6zm3.7 0h1.8v1.8h-1.8V9.6zm-3.7 3.6h1.8V15H7.1v-1.8zm3.7 0h1.8V15h-1.8v-1.8zm-3.7 3.6h1.8v1.8H7.1v-1.8z"/><path fill="currentColor" fill-rule="evenodd" d="M15 8h6v13h-6V8zm1.7 2.2h1.6v1.6h-1.6v-1.6zm0 3.2h1.6V15h-1.6v-1.6zm0 3.2h1.6v1.6h-1.6v-1.6z"/><path fill="currentColor" d="M2 21h20v1.5H2V21z"/>' ),
		'towtruck'   => array( 'label' => __( 'Tow truck', 'brooks-law-30-pro' ),         'svg' => '<path fill="currentColor" d="M1 8h5.4l2-3H11v8.6h1.2l4.9-3.4 1 1.5-4.3 3H23v2h-1.5a2.7 2.7 0 0 0-5.2 0H9.4a2.7 2.7 0 0 0-5.2 0H1V8zm2 2v4.6h.8a2.7 2.7 0 0 1 4-.1h1.2V7.4L8.5 10H3zm3.8 5.9a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3zm12.1 0a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3z"/><path fill="currentColor" d="M14.9 8.4 17 6h3.4L22.5 8.7v2.6h-1.3a2.5 2.5 0 0 0-4.4-.4L15.5 10l-.6-1.6zm2.9-.9-.9 1h3.6l-.8-1h-1.9z"/>' ),
		'trolley'    => array( 'label' => __( 'Trolley car', 'brooks-law-30-pro' ),       'svg' => '<path fill="currentColor" d="M11 2h2v2.5h5a1 1 0 0 1 1 1V16a2 2 0 0 1-2 2h-.5l1.6 2.5h-2.2L14.4 18H9.6l-1.5 2.5H5.9L7.5 18H7a2 2 0 0 1-2-2V5.5a1 1 0 0 1 1-1h5V2zM7 6.5V11h10V6.5H7zM7 13v3h10v-3H7zm1.6.8h1.6v1.4H8.6v-1.4zm5.2 0h1.6v1.4h-1.6v-1.4zM3.5 6.5h1V12h-1V6.5zm16 0h1V12h-1V6.5z"/>' ),
		'mainstreet' => array( 'label' => __( 'Main street', 'brooks-law-30-pro' ),       'svg' => '<path fill="currentColor" d="M2 20h20v1.5H2V20zM3 10h5.5v9H3v-9zm1.5 1.6v2.6h2.5v-2.6H4.5zm5-6.1H15v13.5H9.5V5.5zm1.5 2v3.2h2.5V7.5H11zm5 2.5h5v9h-5v-9zm1.5 1.6v2.6H20v-2.6h-2.5z"/><path fill="currentColor" d="M2.4 9 5.8 6.8 9.1 9v.6H2.4V9zm6.8-4.4L12.2 2l3.1 2.6v.6H9.2v-.6zM15.4 8.6l3.1-2 3.1 2v.6h-6.2v-.6z"/>' ),
	);

	return $icons;
}

/**
 * Sanitize a bubble icon choice.
 *
 * @param string $value Raw value.
 * @return string
 */
function brooks_law_sanitize_bubble_icon( $value ) {
	return array_key_exists( $value, brooks_law_sa_icons() ) ? $value : 'pin';
}

/**
 * Customizer: Service Area Bubbles section.
 *
 * @param WP_Customize_Manager $wp_customize Manager.
 */
function brooks_law_service_areas_customize( $wp_customize ) {

	$wp_customize->add_section( 'brooks_law_service_areas', array(
		'title'       => __( 'Service Area Bubbles', 'brooks-law-30-pro' ),
		'description' => __( 'Clickable community bubbles near the bottom of the front page. Leave a bubble\'s label or link blank to hide it. Add a photo to replace the default marker.', 'brooks-law-30-pro' ),
		'priority'    => 132,
	) );

	$wp_customize->add_setting( 'sa_enable', array(
		'default'           => true,
		'sanitize_callback' => 'brooks_law_sanitize_checkbox',
	) );
	$wp_customize->add_control( 'sa_enable', array(
		'section' => 'brooks_law_service_areas',
		'label'   => __( 'Show service area section', 'brooks-law-30-pro' ),
		'type'    => 'checkbox',
	) );

	$wp_customize->add_setting( 'sa_heading', array(
		'default'           => 'Communities We Serve',
		'sanitize_callback' => 'sanitize_text_field',
	) );
	$wp_customize->add_control( 'sa_heading', array(
		'section' => 'brooks_law_service_areas',
		'label'   => __( 'Section heading', 'brooks-law-30-pro' ),
		'type'    => 'text',
	) );

	$wp_customize->add_setting( 'sa_subheading', array(
		'default'           => 'Criminal defense in courts across the Mid-South — select your community for local court information.',
		'sanitize_callback' => 'sanitize_text_field',
	) );
	$wp_customize->add_control( 'sa_subheading', array(
		'section' => 'brooks_law_service_areas',
		'label'   => __( 'Section subheading', 'brooks-law-30-pro' ),
		'type'    => 'text',
	) );

	$defaults = brooks_law_service_area_defaults();
	for ( $i = 1; $i <= 12; $i++ ) {

		$wp_customize->add_setting( "sa_{$i}_label", array(
			'default'           => $defaults[ $i ]['label'],
			'sanitize_callback' => 'sanitize_text_field',
		) );
		$wp_customize->add_control( "sa_{$i}_label", array(
			'section' => 'brooks_law_service_areas',
			/* translators: %d: bubble slot number. */
			'label'   => sprintf( __( 'Bubble %d — Label', 'brooks-law-30-pro' ), $i ),
			'type'    => 'text',
		) );

		$wp_customize->add_setting( "sa_{$i}_url", array(
			'default'           => $defaults[ $i ]['url'],
			'sanitize_callback' => 'brooks_law_sanitize_bubble_url',
		) );
		$wp_customize->add_control( "sa_{$i}_url", array(
			'section'     => 'brooks_law_service_areas',
			/* translators: %d: bubble slot number. */
			'label'       => sprintf( __( 'Bubble %d — Link', 'brooks-law-30-pro' ), $i ),
			'description' => __( 'Relative path like /bartlett-criminal-defense/ or a full URL.', 'brooks-law-30-pro' ),
			'type'        => 'text',
		) );

		$wp_customize->add_setting( "sa_{$i}_icon", array(
			'default'           => 'pin',
			'sanitize_callback' => 'brooks_law_sanitize_bubble_icon',
		) );
		$icon_choices = array();
		foreach ( brooks_law_sa_icons() as $icon_key => $icon ) {
			$icon_choices[ $icon_key ] = $icon['label'];
		}
		$wp_customize->add_control( "sa_{$i}_icon", array(
			'section' => 'brooks_law_service_areas',
			/* translators: %d: bubble slot number. */
			'label'   => sprintf( __( 'Bubble %d — Icon', 'brooks-law-30-pro' ), $i ),
			'type'    => 'select',
			'choices' => $icon_choices,
		) );

		$wp_customize->add_setting( "sa_{$i}_image", array(
			'default'           => 0,
			'sanitize_callback' => 'absint',
		) );
		$wp_customize->add_control( new WP_Customize_Media_Control( $wp_customize, "sa_{$i}_image", array(
			'section'   => 'brooks_law_service_areas',
			/* translators: %d: bubble slot number. */
			'label'     => sprintf( __( 'Bubble %d — Photo (optional)', 'brooks-law-30-pro' ), $i ),
			'mime_type' => 'image',
		) ) );
	}
}
add_action( 'customize_register', 'brooks_law_service_areas_customize' );

/**
 * Populated bubbles for the template.
 *
 * @return array[] label, url, img_id.
 */
function brooks_law_get_service_areas() {
	$defaults = brooks_law_service_area_defaults();
	$areas    = array();

	for ( $i = 1; $i <= 12; $i++ ) {
		$label = get_theme_mod( "sa_{$i}_label", $defaults[ $i ]['label'] );
		$url   = get_theme_mod( "sa_{$i}_url", $defaults[ $i ]['url'] );

		if ( '' === trim( (string) $label ) || '' === trim( (string) $url ) ) {
			continue;
		}
		if ( 0 === strpos( $url, '/' ) ) {
			$url = home_url( $url );
		}

		$areas[] = array(
			'label'  => $label,
			'url'    => $url,
			'img_id' => absint( get_theme_mod( "sa_{$i}_image", 0 ) ),
			'icon'   => brooks_law_sanitize_bubble_icon( get_theme_mod( "sa_{$i}_icon", 'pin' ) ),
		);
	}

	return $areas;
}
