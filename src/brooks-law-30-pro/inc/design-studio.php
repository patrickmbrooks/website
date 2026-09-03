<?php
/**
 * Design Studio — advanced styling controls (v3.0).
 *
 * Every design token in style.css becomes a Customizer control:
 * full color system, typography (font pairing, base size, scale),
 * spacing density, corner radius, and button style. Values are
 * emitted as a :root CSS-variable override AFTER style.css, so the
 * whole site restyles instantly with zero extra HTTP requests and
 * zero specificity fights. Leave anything blank/default and the
 * shipped design is untouched.
 *
 * @package brooks-law
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Token map: option key => [ CSS var, label, default ].
 *
 * @return array
 */
function brooks_law_ds_colors() {
	return array(
		'ds_court'        => array( '--court', __( 'Primary dark (hero, footer)', 'brooks-law-30-pro' ), '#12202e' ),
		'ds_court2'       => array( '--court-2', __( 'Raised panels on dark', 'brooks-law-30-pro' ), '#1b2c3d' ),
		'ds_paper'        => array( '--paper', __( 'Page background', 'brooks-law-30-pro' ), '#ffffff' ),
		'ds_limestone'    => array( '--limestone', __( 'Alternating section band', 'brooks-law-30-pro' ), '#f5f2ea' ),
		'ds_ink'          => array( '--ink', __( 'Body text', 'brooks-law-30-pro' ), '#222933' ),
		'ds_muted'        => array( '--muted', __( 'Secondary text', 'brooks-law-30-pro' ), '#4b5563' ),
		'ds_brass'        => array( '--brass', __( 'Accent (rules, numerals)', 'brooks-law-30-pro' ), '#b08d3e' ),
		'ds_brass_bright' => array( '--brass-bright', __( 'Accent text on dark', 'brooks-law-30-pro' ), '#d9b45c' ),
		'ds_brass_btn'    => array( '--brass-btn', __( 'Button fill', 'brooks-law-30-pro' ), '#c8a04a' ),
		'ds_oxblood'      => array( '--oxblood', __( 'Eyebrow labels', 'brooks-law-30-pro' ), '#7c2d2d' ),
		'ds_link'         => array( '--link', __( 'Links on light', 'brooks-law-30-pro' ), '#14538c' ),
		'ds_link_dark'    => array( '--link-dark', __( 'Links on dark', 'brooks-law-30-pro' ), '#e6c778' ),
		'ds_focus'        => array( '--focus-ring', __( 'Keyboard focus ring', 'brooks-law-30-pro' ), '#2f74c0' ),
	);
}

/**
 * Font stacks — all local, zero downloads, zero CLS.
 *
 * @return array
 */
function brooks_law_ds_font_stacks() {
	return array(
		'georgia'   => array( __( 'Georgia (classic serif) — default', 'brooks-law-30-pro' ), 'Georgia, "Times New Roman", Times, serif' ),
		'palatino'  => array( __( 'Palatino (bookish serif)', 'brooks-law-30-pro' ), 'Palatino, "Palatino Linotype", "Book Antiqua", Georgia, serif' ),
		'charter'   => array( __( 'Charter / Bitstream (editorial serif)', 'brooks-law-30-pro' ), 'Charter, "Bitstream Charter", "Sitka Text", Cambria, Georgia, serif' ),
		'didone'    => array( __( 'Didot / Bodoni (high-contrast serif)', 'brooks-law-30-pro' ), 'Didot, "Bodoni MT", "Playfair Display", Georgia, serif' ),
		'system'    => array( __( 'System sans (modern)', 'brooks-law-30-pro' ), 'system-ui, -apple-system, "Segoe UI", Roboto, Ubuntu, Cantarell, "Helvetica Neue", Arial, sans-serif' ),
		'helvetica' => array( __( 'Helvetica / Arial (neutral sans)', 'brooks-law-30-pro' ), '"Helvetica Neue", Helvetica, Arial, sans-serif' ),
		'avenir'    => array( __( 'Avenir / Futura (geometric sans)', 'brooks-law-30-pro' ), 'Avenir, "Avenir Next", Futura, "Century Gothic", "Segoe UI", sans-serif' ),
	);
}

/**
 * Register the Design Studio section + controls.
 *
 * @param WP_Customize_Manager $wp_customize Manager.
 */
function brooks_law_ds_customize( $wp_customize ) {

	$wp_customize->add_section( 'brooks_law_design', array(
		'title'       => __( 'Design Studio', 'brooks-law-30-pro' ),
		'panel'       => 'brooks_law',
		'priority'    => 5,
		'description' => __( 'Site-wide colors, typography, and spacing. Changes apply everywhere instantly — no code. "Reset" any field by restoring its default value.', 'brooks-law-30-pro' ),
	) );

	/* ---- Colors ---- */
	foreach ( brooks_law_ds_colors() as $key => $def ) {
		$wp_customize->add_setting( $key, array(
			'default'           => $def[2],
			'sanitize_callback' => 'sanitize_hex_color',
			'transport'         => 'refresh',
		) );
		$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, $key, array(
			'label'   => $def[1],
			'section' => 'brooks_law_design',
		) ) );
	}

	/* ---- Typography ---- */
	$stacks  = brooks_law_ds_font_stacks();
	$choices = array();
	foreach ( $stacks as $k => $v ) {
		$choices[ $k ] = $v[0];
	}

	$wp_customize->add_setting( 'ds_font_heading', array( 'default' => 'georgia', 'sanitize_callback' => 'sanitize_key' ) );
	$wp_customize->add_control( 'ds_font_heading', array(
		'label'   => __( 'Heading font', 'brooks-law-30-pro' ),
		'section' => 'brooks_law_design',
		'type'    => 'select',
		'choices' => $choices,
	) );

	$wp_customize->add_setting( 'ds_font_body', array( 'default' => 'system', 'sanitize_callback' => 'sanitize_key' ) );
	$wp_customize->add_control( 'ds_font_body', array(
		'label'       => __( 'Body font', 'brooks-law-30-pro' ),
		'section'     => 'brooks_law_design',
		'type'        => 'select',
		'choices'     => $choices,
		'description' => __( 'All stacks are locally installed fonts: zero download, zero layout shift, best PageSpeed.', 'brooks-law-30-pro' ),
	) );

	$wp_customize->add_setting( 'ds_base_size', array( 'default' => 17, 'sanitize_callback' => 'absint' ) );
	$wp_customize->add_control( 'ds_base_size', array(
		'label'       => __( 'Base text size (px)', 'brooks-law-30-pro' ),
		'section'     => 'brooks_law_design',
		'type'        => 'number',
		'input_attrs' => array( 'min' => 15, 'max' => 20, 'step' => 1 ),
	) );

	$wp_customize->add_setting( 'ds_heading_scale', array( 'default' => 100, 'sanitize_callback' => 'absint' ) );
	$wp_customize->add_control( 'ds_heading_scale', array(
		'label'       => __( 'Heading size scale (%)', 'brooks-law-30-pro' ),
		'section'     => 'brooks_law_design',
		'type'        => 'number',
		'input_attrs' => array( 'min' => 85, 'max' => 125, 'step' => 5 ),
	) );

	$wp_customize->add_setting( 'ds_line_height', array( 'default' => 165, 'sanitize_callback' => 'absint' ) );
	$wp_customize->add_control( 'ds_line_height', array(
		'label'       => __( 'Body line height (%)', 'brooks-law-30-pro' ),
		'section'     => 'brooks_law_design',
		'type'        => 'number',
		'input_attrs' => array( 'min' => 140, 'max' => 190, 'step' => 5 ),
	) );

	/* ---- Layout & shape ---- */
	$wp_customize->add_setting( 'ds_density', array( 'default' => 100, 'sanitize_callback' => 'absint' ) );
	$wp_customize->add_control( 'ds_density', array(
		'label'       => __( 'Section spacing (%)', 'brooks-law-30-pro' ),
		'section'     => 'brooks_law_design',
		'type'        => 'number',
		'input_attrs' => array( 'min' => 70, 'max' => 140, 'step' => 10 ),
		'description' => __( '100 = shipped rhythm. Lower is denser, higher is airier.', 'brooks-law-30-pro' ),
	) );

	$wp_customize->add_setting( 'ds_radius', array( 'default' => 3, 'sanitize_callback' => 'absint' ) );
	$wp_customize->add_control( 'ds_radius', array(
		'label'       => __( 'Corner radius (px)', 'brooks-law-30-pro' ),
		'section'     => 'brooks_law_design',
		'type'        => 'number',
		'input_attrs' => array( 'min' => 0, 'max' => 16, 'step' => 1 ),
	) );

	$wp_customize->add_setting( 'ds_measure', array( 'default' => 66, 'sanitize_callback' => 'absint' ) );
	$wp_customize->add_control( 'ds_measure', array(
		'label'       => __( 'Reading column width (characters)', 'brooks-law-30-pro' ),
		'section'     => 'brooks_law_design',
		'type'        => 'number',
		'input_attrs' => array( 'min' => 55, 'max' => 80, 'step' => 1 ),
	) );

	$wp_customize->add_setting( 'ds_buttons', array( 'default' => 'solid', 'sanitize_callback' => 'sanitize_key' ) );
	$wp_customize->add_control( 'ds_buttons', array(
		'label'   => __( 'Button style', 'brooks-law-30-pro' ),
		'section' => 'brooks_law_design',
		'type'    => 'select',
		'choices' => array(
			'solid'   => __( 'Solid brass (default)', 'brooks-law-30-pro' ),
			'outline' => __( 'Outlined', 'brooks-law-30-pro' ),
			'pill'    => __( 'Solid, pill-shaped', 'brooks-law-30-pro' ),
		),
	) );
}
add_action( 'customize_register', 'brooks_law_ds_customize' );

/**
 * Build the override CSS. Only non-default values are emitted, so the
 * shipped stylesheet remains the single source of truth until you
 * actually change something.
 *
 * @return string
 */
function brooks_law_ds_css() {
	$vars = array();

	foreach ( brooks_law_ds_colors() as $key => $def ) {
		$v = get_theme_mod( $key, $def[2] );
		if ( $v && strtolower( $v ) !== strtolower( $def[2] ) ) {
			$vars[ $def[0] ] = sanitize_hex_color( $v );
		}
	}

	$stacks = brooks_law_ds_font_stacks();

	$h = get_theme_mod( 'ds_font_heading', 'georgia' );
	if ( 'georgia' !== $h && isset( $stacks[ $h ] ) ) {
		$vars['--serif'] = $stacks[ $h ][1];
	}
	$b = get_theme_mod( 'ds_font_body', 'system' );
	if ( 'system' !== $b && isset( $stacks[ $b ] ) ) {
		$vars['--sans'] = $stacks[ $b ][1];
	}

	$measure = absint( get_theme_mod( 'ds_measure', 66 ) );
	if ( 66 !== $measure && $measure >= 55 && $measure <= 80 ) {
		$vars['--measure'] = $measure . 'ch';
	}
	$radius = absint( get_theme_mod( 'ds_radius', 3 ) );
	if ( 3 !== $radius ) {
		$vars['--radius'] = $radius . 'px';
	}
	$density = absint( get_theme_mod( 'ds_density', 100 ) );
	if ( 100 !== $density && $density >= 70 && $density <= 140 ) {
		$f = $density / 100;
		$vars['--section-y'] = sprintf( 'clamp(%1$srem, %2$svw, %3$srem)', round( 3.2 * $f, 2 ), round( 8 * $f, 2 ), round( 5.5 * $f, 2 ) );
	}

	$rules = array();

	if ( $vars ) {
		$pairs = array();
		foreach ( $vars as $k => $v ) {
			$pairs[] = $k . ':' . $v;
		}
		$rules[] = ':root{' . implode( ';', $pairs ) . '}';
	}

	$size = absint( get_theme_mod( 'ds_base_size', 17 ) );
	if ( 17 !== $size && $size >= 15 && $size <= 20 ) {
		$rules[] = sprintf( 'body{font-size:%srem}', round( $size / 16, 4 ) );
	}
	$lh = absint( get_theme_mod( 'ds_line_height', 165 ) );
	if ( 165 !== $lh && $lh >= 140 && $lh <= 190 ) {
		$rules[] = sprintf( 'body{line-height:%s}', round( $lh / 100, 2 ) );
	}
	$scale = absint( get_theme_mod( 'ds_heading_scale', 100 ) );
	if ( 100 !== $scale && $scale >= 85 && $scale <= 125 ) {
		$f       = $scale / 100;
		$rules[] = sprintf(
			'h1{font-size:clamp(%1$srem,4vw + 0.8rem,%2$srem)}h2{font-size:clamp(%3$srem,2.2vw + 0.7rem,%4$srem)}h3{font-size:%5$srem}',
			round( 2 * $f, 2 ), round( 3.1 * $f, 2 ), round( 1.55 * $f, 2 ), round( 2.15 * $f, 2 ), round( 1.3 * $f, 2 )
		);
	}

	$btn = get_theme_mod( 'ds_buttons', 'solid' );
	if ( 'outline' === $btn ) {
		$rules[] = '.btn,.button,.wp-block-button__link{background:transparent;color:var(--link);border:2px solid currentColor}.hero .btn,.site-footer .btn{color:var(--brass-bright)}';
	} elseif ( 'pill' === $btn ) {
		$rules[] = '.btn,.button,.wp-block-button__link{border-radius:999px}';
	}

	return implode( '', $rules );
}

/**
 * Print the override inline in <head>, after the main stylesheet.
 */
function brooks_law_ds_print() {
	$css = brooks_law_ds_css();
	if ( '' !== $css ) {
		echo '<style id="brooks-law-design-studio">' . $css . '</style>' . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput -- built from sanitized tokens.
	}
}
add_action( 'wp_head', 'brooks_law_ds_print', 8 );

/**
 * Feed the Design Studio palette into theme.json at runtime.
 *
 * theme.json ships a static palette, and the block editor reads it to build
 * the colour swatches an author picks from. Design Studio, meanwhile,
 * overrides the same values as CSS custom properties at render time. The two
 * therefore disagreed the moment anyone changed a colour: the site restyled,
 * every editor swatch and every previously-applied block colour kept the old
 * hex, and the author had no way to tell.
 *
 * wp_theme_json_data_theme lets the shipped file stay the canonical default
 * while the live values come from the same single source the front end uses,
 * so the picker always offers the palette the site is actually wearing.
 *
 * @param WP_Theme_JSON_Data $theme_json Theme JSON data.
 * @return WP_Theme_JSON_Data
 */
function brooks_law_ds_theme_json( $theme_json ) {
	$slugs = array(
		'ds_court'        => 'court',
		'ds_court2'       => 'court-2',
		'ds_paper'        => 'paper',
		'ds_limestone'    => 'limestone',
		'ds_ink'          => 'ink',
		'ds_muted'        => 'muted',
		'ds_brass'        => 'brass',
		'ds_brass_bright' => 'brass-bright',
		'ds_oxblood'      => 'oxblood',
		'ds_link'         => 'link',
	);

	$colors = brooks_law_ds_colors();
	$names  = array(
		'court'        => __( 'Court (deep slate)', 'brooks-law-30-pro' ),
		'court-2'      => __( 'Court panel', 'brooks-law-30-pro' ),
		'paper'        => __( 'Paper', 'brooks-law-30-pro' ),
		'limestone'    => __( 'Limestone', 'brooks-law-30-pro' ),
		'ink'          => __( 'Ink', 'brooks-law-30-pro' ),
		'muted'        => __( 'Muted', 'brooks-law-30-pro' ),
		'brass'        => __( 'Brass', 'brooks-law-30-pro' ),
		'brass-bright' => __( 'Brass bright', 'brooks-law-30-pro' ),
		'oxblood'      => __( 'Oxblood', 'brooks-law-30-pro' ),
		'link'         => __( 'Link blue', 'brooks-law-30-pro' ),
	);

	$palette = array();

	foreach ( $slugs as $key => $slug ) {
		$default = isset( $colors[ $key ][2] ) ? $colors[ $key ][2] : '#000000';
		$value   = sanitize_hex_color( (string) get_theme_mod( $key, $default ) );

		$palette[] = array(
			'slug'  => $slug,
			'name'  => isset( $names[ $slug ] ) ? $names[ $slug ] : $slug,
			'color' => $value ? $value : $default,
		);
	}

	$stacks = brooks_law_ds_font_stacks();
	$serif  = get_theme_mod( 'ds_font_heading', 'georgia' );
	$sans   = get_theme_mod( 'ds_font_body', 'system' );

	$families = array(
		array(
			'slug'       => 'serif',
			'name'       => __( 'Serif (headings)', 'brooks-law-30-pro' ),
			'fontFamily' => isset( $stacks[ $serif ][1] ) ? $stacks[ $serif ][1] : $stacks['georgia'][1],
		),
		array(
			'slug'       => 'sans',
			'name'       => __( 'Sans (body)', 'brooks-law-30-pro' ),
			'fontFamily' => isset( $stacks[ $sans ][1] ) ? $stacks[ $sans ][1] : $stacks['system'][1],
		),
		array(
			'slug'       => 'mono',
			'name'       => __( 'Mono', 'brooks-law-30-pro' ),
			'fontFamily' => 'ui-monospace, SFMono-Regular, Menlo, Consolas, monospace',
		),
	);

	return $theme_json->update_with(
		array(
			'version'  => 2,
			'settings' => array(
				'color'      => array( 'palette' => $palette ),
				'typography' => array( 'fontFamilies' => $families ),
			),
		)
	);
}
add_filter( 'wp_theme_json_data_theme', 'brooks_law_ds_theme_json' );

/**
 * Mirror the overrides into the block editor so editing matches the front end.
 *
 * @param string $css Editor styles.
 */
function brooks_law_ds_editor_css() {
	$css = brooks_law_ds_css();
	if ( '' !== $css ) {
		wp_add_inline_style( 'wp-block-library', $css );
	}
}
add_action( 'enqueue_block_editor_assets', 'brooks_law_ds_editor_css' );
