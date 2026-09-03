<?php
/**
 * Brooks Law 4.3 — Editorial page layout.
 *
 * Six pages (both alcohol pages, the three probation-violation pages, and
 * Shelby County Drug Court) shipped a richer layout as a ~14KB <style> block
 * plus ~10KB of inline SVG pasted into the post content. Identical bytes on
 * every page, so nothing cached, nothing editable in the block editor, and a
 * second palette that ignored the Customizer entirely.
 *
 * This module makes that layout a theme feature any page can opt into:
 *
 *   - the stylesheet is assets/css/editorial-pages.css, enqueued once and
 *     cached;
 *   - the Google Fonts @import (render-blocking, no preconnect, discovered
 *     late) becomes a proper enqueue with resource hints — or is dropped for
 *     the system stack;
 *   - the scene SVG moves into brooks_law_edpage_scene(), so a new page needs
 *     a checkbox rather than ten kilobytes of pasted markup.
 *
 * Nothing existing changes. The six legacy pages already carry their own
 * wrapper and inline copy; they are detected, they keep rendering exactly as
 * before, and their inline block simply becomes redundant.
 *
 * Deliberately separate from inc/editorial-sky.php, which is the older
 * sky-only layer on .blf-sky. Different prefix, different file, different
 * stylesheet. Where both would apply, the older sky is stood down for that
 * request so the two never stack.
 *
 * @package Brooks_Law
 * @since   4.3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Google Fonts URL for the editorial pair.
 *
 * Fraunces and IBM Plex are both open-licensed and are now served from this
 * theme rather than fonts.gstatic.com. See assets/fonts/fonts.css for why,
 * and tools/fetch-fonts.sh to regenerate the subset.
 *
 * @return string
 */
function brooks_law_edpage_fonts_url() {
	return get_template_directory_uri() . '/assets/fonts/fonts.css';
}

/**
 * Preload the two faces that draw above-the-fold text.
 *
 * A self-hosted @font-face is only discovered once the stylesheet referencing
 * it has been fetched and parsed, which is one round trip later than it needs
 * to be for the display face in the page heading. Only the Latin subsets of
 * the two faces that actually render first are hinted — preloading a file the
 * page may not use costs more than it saves.
 */
function brooks_law_edpage_preload_fonts() {
	if ( ! brooks_law_edpage_active() || 'editorial' !== brooks_law_get_option( 'edpage_type' ) ) {
		return;
	}

	$base = get_template_directory_uri() . '/assets/fonts/';

	foreach ( array( 'fraunces-600-latin.woff2', 'ibmplexsans-400-latin.woff2' ) as $file ) {
		printf(
			'<link rel="preload" href="%s" as="font" type="font/woff2" crossorigin>' . "\n",
			esc_url( $base . $file )
		);
	}
}
add_action( 'wp_head', 'brooks_law_edpage_preload_fonts', 1 );

/**
 * Is the editorial layout active for this request?
 *
 * True when a page opts in via the checkbox, and also when its content
 * already contains the wrapper — so the six legacy pages keep working whether
 * or not anyone ever ticks the box.
 *
 * @return bool
 */
function brooks_law_edpage_active() {
	static $active = null;

	if ( null !== $active ) {
		return $active;
	}

	$active = false;

	if ( is_singular() && brooks_law_get_option( 'edpage_enable' ) ) {
		$post = get_post();

		if ( $post instanceof WP_Post ) {
			$choice = (string) get_post_meta( $post->ID, '_br_edpage', true );

			if ( 'off' === $choice ) {
				// An explicit "Standard" wins, even on a page whose content
				// carries the editorial markup — that is the whole point of
				// giving the legacy pages a selector.
				$active = false;
			} elseif ( 'on' === $choice ) {
				$active = true;
			} else {
				$active = ( false !== strpos( (string) $post->post_content, 'class="blfE' ) );
			}

			/**
			 * Filter whether the editorial layout applies to this request.
			 *
			 * @param bool    $active Result.
			 * @param WP_Post $post   Current post.
			 */
			$active = (bool) apply_filters( 'brooks_law_edpage_active', $active, $post );
		}
	}

	return $active;
}

/**
 * Does this page carry its own inline copy of the layout?
 *
 * Legacy pages do. They need the wrapper left alone and the scene left alone,
 * because both are already in their content.
 *
 * @return bool
 */
function brooks_law_edpage_is_legacy() {
	$post = get_post();

	return ( $post instanceof WP_Post )
		&& false !== strpos( (string) $post->post_content, 'class="blfE' );
}

/**
 * Does this post already carry a layout of its own?
 *
 * Broader than brooks_law_edpage_is_legacy(): as well as the editorial
 * wrapper, this catches the attorney-profile system, which is a separate set
 * of .pb- components with its own typography.
 *
 * Wrapping either one in .blfE puts its markup underneath editorial
 * typography and breaks it, so those pages are never wrapped — the checkbox
 * has no effect on them and the edit screen says so.
 *
 * @param WP_Post|null $post Post, or the current one.
 * @return bool
 */
function brooks_law_edpage_has_own_layout( $post = null ) {
	$post = $post ? $post : get_post();

	if ( ! $post instanceof WP_Post ) {
		return false;
	}

	$content = (string) $post->post_content;

	$markers = apply_filters(
		'brooks_law_edpage_layout_markers',
		array(
			'class="blfE', // Editorial layout.
			'class="pb-sec', // Attorney profile layout.
			'pb-profile-styles', // Its stylesheet id, in case the markup changes.
		)
	);

	foreach ( $markers as $marker ) {
		if ( false !== strpos( $content, $marker ) ) {
			return true;
		}
	}

	return false;
}

/**
 * Enqueue the stylesheet, and the fonts when the editorial face is chosen.
 */
function brooks_law_edpage_assets() {
	if ( ! brooks_law_edpage_active() ) {
		return;
	}

	if ( 'editorial' === brooks_law_get_option( 'edpage_type' ) ) {
		wp_enqueue_style(
			'brooks-law-edpage-fonts',
			brooks_law_edpage_fonts_url(),
			array(),
			brooks_law_asset_ver( '/assets/fonts/fonts.css' )
		);
	}

	$path = get_template_directory() . '/assets/css/editorial-pages.css';

	wp_enqueue_style(
		'brooks-law-edpage',
		get_template_directory_uri() . '/assets/css/editorial-pages.css',
		array(),
		file_exists( $path ) ? (string) filemtime( $path ) : BROOKS_LAW_VERSION
	);
}
add_action( 'wp_enqueue_scripts', 'brooks_law_edpage_assets', 25 );

/*
 * The preconnect hints to fonts.googleapis.com and fonts.gstatic.com that
 * used to live here are gone with the third-party request they warmed up.
 * The faces are served from this origin now, which the browser is already
 * connected to; brooks_law_edpage_preload_fonts() above starts the two that
 * matter as early as a preconnect ever could.
 */

/**
 * Body class, so the stylesheet can swap to the system stack.
 *
 * @param string[] $classes Classes.
 * @return string[]
 */
function brooks_law_edpage_body_class( $classes ) {
	if ( brooks_law_edpage_active() ) {
		$classes[] = 'blf-editorial';

		if ( 'system' === brooks_law_get_option( 'edpage_type' ) ) {
			$classes[] = 'blf-system-type';
		}
	}

	return $classes;
}
add_filter( 'body_class', 'brooks_law_edpage_body_class' );

/**
 * Stand down the older sky layer where this one applies.
 *
 * inc/editorial-sky.php prints .blf-sky on wp_body_open and skips pages whose
 * content contains class="sky". A page that opts in through the checkbox has
 * no such marker, so without this both would render.
 */
function brooks_law_edpage_suppress_sky() {
	if ( brooks_law_edpage_active() && function_exists( 'brooks_law_editorial_render_sky' ) ) {
		remove_action( 'wp_body_open', 'brooks_law_editorial_render_sky' );
	}
}
add_action( 'template_redirect', 'brooks_law_edpage_suppress_sky' );

/**
 * Wrap opted-in content in the editorial container and add the scene.
 *
 * Legacy pages are skipped — they already have both.
 *
 * @param string $content Content.
 * @return string
 */
function brooks_law_edpage_wrap( $content ) {
	if ( is_admin() || ! in_the_loop() || ! is_main_query() ) {
		return $content;
	}
	if ( ! brooks_law_edpage_active() || brooks_law_edpage_has_own_layout() ) {
		return $content;
	}

	return '<div class="blfE">'
		. brooks_law_edpage_scene()
		. '<div class="wrap">' . $content . '</div>'
		. '</div>';
}
add_filter( 'the_content', 'brooks_law_edpage_wrap', 7 );

/**
 * Customizer controls.
 *
 * @param WP_Customize_Manager $wp_customize Manager.
 */
function brooks_law_edpage_customize( $wp_customize ) {
	$wp_customize->add_section(
		'brooks_law_edpage',
		array(
			'title'       => __( 'Editorial Layout', 'brooks-law-30-pro' ),
			'description' => __( 'The richer layout used on the probation violation, alcohol, and drug court pages. Its stylesheet now lives in the theme and is cached once. Switch it on for any page in the Editorial Layout box on the edit screen.', 'brooks-law-30-pro' ),
			'priority'    => 134,
		)
	);

	$wp_customize->add_setting(
		'edpage_enable',
		array(
			'default'           => true,
			'sanitize_callback' => 'brooks_law_sanitize_checkbox',
		)
	);
	$wp_customize->add_control(
		'edpage_enable',
		array(
			'section'     => 'brooks_law_edpage',
			'label'       => __( 'Enable editorial layout', 'brooks-law-30-pro' ),
			'description' => __( 'Unticking this returns every page to the standard layout, including the six that currently use it.', 'brooks-law-30-pro' ),
			'type'        => 'checkbox',
		)
	);

	$wp_customize->add_setting(
		'edpage_consolidate',
		array(
			'default'           => true,
			'sanitize_callback' => 'brooks_law_sanitize_checkbox',
		)
	);
	$wp_customize->add_control(
		'edpage_consolidate',
		array(
			'section'     => 'brooks_law_edpage',
			'label'       => __( 'Serve shared CSS from the theme', 'brooks-law-30-pro' ),
			'description' => __( 'Nine older pages still hold a copy of their stylesheet inside their content. With this on, that copy is not printed and the theme serves the same CSS from a cached file instead. Nothing in the database changes, so unticking this puts it straight back.', 'brooks-law-30-pro' ),
			'type'        => 'checkbox',
		)
	);

	$wp_customize->add_setting(
		'edpage_unify_scene',
		array(
			'default'           => true,
			'sanitize_callback' => 'brooks_law_sanitize_checkbox',
		)
	);
	$wp_customize->add_control(
		'edpage_unify_scene',
		array(
			'section'     => 'brooks_law_edpage',
			'label'       => __( 'Serve the scene artwork from the theme', 'brooks-law-30-pro' ),
			'description' => __( 'The seven older pages each hold their own copy of the parallax scene. With this on, the theme substitutes its copy at render, so changing the artwork once changes it everywhere. Page size is unchanged — the scene has to stay inline for the weather effect to work.', 'brooks-law-30-pro' ),
			'type'        => 'checkbox',
		)
	);

	$wp_customize->add_setting(
		'edpage_type',
		array(
			'default'           => 'editorial',
			'sanitize_callback' => 'brooks_law_sanitize_edpage_type',
		)
	);
	$wp_customize->add_control(
		'edpage_type',
		array(
			'section'     => 'brooks_law_edpage',
			'label'       => __( 'Typeface', 'brooks-law-30-pro' ),
			'description' => __( 'Editorial keeps Fraunces and IBM Plex, matching how these pages look today, at the cost of one webfont request. System uses Georgia and the system sans, which is how the rest of the site works and costs nothing.', 'brooks-law-30-pro' ),
			'type'        => 'select',
			'choices'     => array(
				'editorial' => __( 'Editorial (Fraunces + IBM Plex)', 'brooks-law-30-pro' ),
				'system'    => __( 'System (no webfont request)', 'brooks-law-30-pro' ),
			),
		)
	);
}
add_action( 'customize_register', 'brooks_law_edpage_customize', 27 );

/**
 * Whitelist the typeface choice.
 *
 * @param string $value Candidate.
 * @return string
 */
function brooks_law_sanitize_edpage_type( $value ) {
	return in_array( $value, array( 'editorial', 'system' ), true ) ? $value : 'editorial';
}

/**
 * The per-page checkbox.
 */
function brooks_law_edpage_meta() {
	foreach ( array( 'page', 'post' ) as $type ) {
		add_meta_box(
			'brooks_edpage',
			__( 'Editorial Layout', 'brooks-law-30-pro' ),
			'brooks_law_edpage_meta_html',
			$type,
			'side'
		);
	}
}
add_action( 'add_meta_boxes', 'brooks_law_edpage_meta' );

/**
 * Render the checkbox.
 *
 * @param WP_Post $post Post.
 */
function brooks_law_edpage_meta_html( $post ) {
	wp_nonce_field( 'brooks_edpage_save', 'brooks_edpage_nonce' );

	$choice    = (string) get_post_meta( $post->ID, '_br_edpage', true );
	$content   = (string) $post->post_content;
	$is_edit   = ( false !== strpos( $content, 'class="blfE' ) );
	$is_prof   = ( false !== strpos( $content, 'class="pb-sec' ) || false !== strpos( $content, 'pb-profile-styles' ) );

	if ( $is_prof ) {
		echo '<p>' . esc_html__( 'This page uses the attorney profile layout, which has its own typography and is served from the theme. The editorial layout is not offered here, because applying both would override the profile styling.', 'brooks-law-30-pro' ) . '</p>';
		return;
	}

	$auto = $is_edit
		? __( 'Automatic — Editorial (built into this page)', 'brooks-law-30-pro' )
		: __( 'Automatic — Standard', 'brooks-law-30-pro' );

	echo '<p><label for="br_edpage"><strong>' . esc_html__( 'Layout', 'brooks-law-30-pro' ) . '</strong></label><br>';
	echo '<select id="br_edpage" name="br_edpage" style="width:100%">';
	echo '<option value="" ' . selected( $choice, '', false ) . '>' . esc_html( $auto ) . '</option>';
	echo '<option value="on" ' . selected( $choice, 'on', false ) . '>' . esc_html__( 'Editorial', 'brooks-law-30-pro' ) . '</option>';
	echo '<option value="off" ' . selected( $choice, 'off', false ) . '>' . esc_html__( 'Standard', 'brooks-law-30-pro' ) . '</option>';
	echo '</select></p>';

	if ( $is_edit ) {
		echo '<p class="description">' . esc_html__( 'This page has the editorial markup in its own content. Choosing Standard drops the stylesheet and the artwork, leaving the text in the ordinary site layout. Nothing is deleted — switch back any time.', 'brooks-law-30-pro' ) . '</p>';
	} else {
		echo '<p class="description">' . esc_html__( 'Editorial adds the umber palette, display typography, and the parallax scene. Set the typeface site-wide in Customizer → Editorial Layout.', 'brooks-law-30-pro' ) . '</p>';
	}
}

/**
 * Save the checkbox.
 *
 * @param int $post_id Post.
 */
function brooks_law_edpage_save( $post_id ) {
	if ( ! isset( $_POST['brooks_edpage_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['brooks_edpage_nonce'] ), 'brooks_edpage_save' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	$choice = isset( $_POST['br_edpage'] ) ? sanitize_key( wp_unslash( $_POST['br_edpage'] ) ) : '';

	if ( in_array( $choice, array( 'on', 'off' ), true ) ) {
		update_post_meta( $post_id, '_br_edpage', $choice );
	} else {
		delete_post_meta( $post_id, '_br_edpage' );
	}
}
add_action( 'save_post', 'brooks_law_edpage_save' );


/**
 * The parallax scene: weather pair plus the two river layers.
 *
 * Extracted from the pasted content so an opted-in page does not require
 * anyone to hand-copy ten kilobytes of SVG. Decorative, and hidden from
 * assistive technology.
 *
 * @return string
 */
function brooks_law_edpage_scene() {
	$svg = array(
		'<svg class="storm" viewBox="0 0 1200 700" preserveAspectRatio="xMidYMid slice" xmlns="http://www.w3.org/2000/svg"><rect class="stormtint" x="0" y="0" width="1200" height="700" opacity=".1"/><g opacity=".16"><g class="drift1"><path class="cloudD" d="M196 150 q5 -46 64 -39 q34 -30 85 0 q39 -9 71 34 q25 34 -221 5 Z"/><path class="cloudD" d="M638 110 q5 -54 76 -46 q40 -35 100 0 q46 -11 84 40 q30 40 -259 5 Z"/><path class="cloudD" d="M1034 176 q4 -38 53 -32 q28 -25 70 0 q32 -8 59 28 q21 28 -182 4 Z"/><path class="cloudD" d="M48 236 q3 -32 45 -27 q24 -21 59 0 q27 -6 50 24 q18 24 -154 3 Z"/></g><g class="drift2" opacity=".7"><path class="cloudD" d="M470 232 q4 -40 56 -34 q30 -26 74 0 q34 -8 62 30 q22 30 -192 4 Z"/><path class="cloudD" d="M904 268 q3 -34 48 -29 q26 -22 63 0 q29 -7 53 26 q19 26 -163 3 Z"/></g></g></svg>',
		'<svg class="clear" viewBox="0 0 1200 700" preserveAspectRatio="xMidYMid slice" xmlns="http://www.w3.org/2000/svg"><g class="sun" opacity=".55"><circle cx="1040" cy="128" r="46" fill="none"/><g class="sunspin"><line x1="1102" y1="128" x2="1124" y2="128" opacity=".8"/><line x1="1094" y1="159" x2="1113" y2="170" opacity=".8"/><line x1="1071" y1="182" x2="1082" y2="201" opacity=".8"/><line x1="1040" y1="190" x2="1040" y2="212" opacity=".8"/><line x1="1009" y1="182" x2="998" y2="201" opacity=".8"/><line x1="986" y1="159" x2="967" y2="170" opacity=".8"/><line x1="978" y1="128" x2="956" y2="128" opacity=".8"/><line x1="986" y1="97" x2="967" y2="86" opacity=".8"/><line x1="1009" y1="74" x2="998" y2="55" opacity=".8"/><line x1="1040" y1="66" x2="1040" y2="44" opacity=".8"/><line x1="1071" y1="74" x2="1082" y2="55" opacity=".8"/><line x1="1094" y1="97" x2="1113" y2="86" opacity=".8"/></g></g><g opacity=".5"><g class="drift1"><path class="cloudW" d="M172 138 q5 -48 67 -41 q36 -31 89 0 q41 -10 74 36 q26 36 -230 5 Z"/><path class="cloudW" d="M634 190 q4 -38 53 -32 q28 -25 70 0 q32 -8 59 28 q21 28 -182 4 Z"/><path class="cloudW" d="M1046 128 q4 -42 59 -36 q32 -27 78 0 q36 -8 65 32 q23 32 -202 4 Z"/></g><g class="drift2" opacity=".75"><path class="cloudW" d="M428 246 q3 -32 45 -27 q24 -21 59 0 q27 -6 50 24 q18 24 -154 3 Z"/><path class="cloudW" d="M887 282 q3 -28 39 -24 q21 -18 52 0 q24 -6 43 21 q15 21 -134 3 Z"/></g></g><g class="grass" opacity=".2"><path d="M-20 700 L-20 646 q120 -22 250 -12 q140 11 280 -8 q150 -20 300 -6 q140 13 280 -10 q90 -15 130 4 L1240 700 Z"/></g><g class="blade" opacity=".22"><path d="M-20 700 q-4 -18 -7 -34" opacity="0.65"/><path d="M-3 700 q4 -29 -3 -40" opacity="0.36"/><path d="M14 700 q2 -18 -7 -40" opacity="0.47"/><path d="M31 700 q-4 -27 3 -40" opacity="0.50"/><path d="M48 700 q-2 -21 -3 -41" opacity="0.57"/><path d="M65 700 q2 -26 -3 -43" opacity="0.67"/><path d="M82 700 q-2 -28 -3 -36" opacity="0.65"/><path d="M99 700 q-2 -19 7 -35" opacity="0.64"/><path d="M116 700 q-4 -28 3 -39" opacity="0.45"/><path d="M133 700 q2 -23 3 -35" opacity="0.38"/><path d="M150 700 q-4 -19 7 -30" opacity="0.49"/><path d="M167 700 q4 -25 -7 -39" opacity="0.71"/><path d="M184 700 q2 -28 -7 -27" opacity="0.71"/><path d="M201 700 q-2 -23 -3 -37" opacity="0.67"/><path d="M218 700 q2 -17 7 -38" opacity="0.51"/><path d="M235 700 q-4 -27 -3 -29" opacity="0.75"/><path d="M252 700 q-4 -18 7 -44" opacity="0.41"/><path d="M269 700 q4 -26 3 -28" opacity="0.57"/><path d="M286 700 q-2 -16 -7 -44" opacity="0.39"/><path d="M303 700 q-2 -22 -3 -30" opacity="0.36"/><path d="M320 700 q-2 -20 -3 -42" opacity="0.48"/><path d="M337 700 q4 -29 -3 -25" opacity="0.71"/><path d="M354 700 q2 -30 7 -42" opacity="0.68"/><path d="M371 700 q4 -29 -3 -41" opacity="0.41"/><path d="M388 700 q-4 -29 7 -29" opacity="0.59"/><path d="M405 700 q-2 -18 -3 -39" opacity="0.60"/><path d="M422 700 q-4 -24 -7 -34" opacity="0.62"/><path d="M439 700 q4 -28 -7 -41" opacity="0.37"/><path d="M456 700 q-2 -20 -7 -27" opacity="0.55"/><path d="M473 700 q-4 -28 -7 -38" opacity="0.48"/><path d="M490 700 q-2 -27 3 -38" opacity="0.55"/><path d="M507 700 q4 -24 -3 -40" opacity="0.70"/><path d="M524 700 q2 -30 -3 -38" opacity="0.40"/><path d="M541 700 q-4 -22 7 -34" opacity="0.38"/><path d="M558 700 q-2 -22 -7 -30" opacity="0.62"/><path d="M575 700 q-4 -30 -3 -44" opacity="0.61"/><path d="M592 700 q-2 -20 -3 -38" opacity="0.44"/><path d="M609 700 q-4 -22 7 -29" opacity="0.75"/><path d="M626 700 q-2 -18 7 -40" opacity="0.51"/><path d="M643 700 q4 -19 3 -34" opacity="0.39"/><path d="M660 700 q2 -16 3 -41" opacity="0.53"/><path d="M677 700 q-4 -22 3 -40" opacity="0.60"/><path d="M694 700 q-4 -17 -3 -27" opacity="0.38"/><path d="M711 700 q2 -16 -3 -32" opacity="0.65"/><path d="M728 700 q4 -29 3 -36" opacity="0.41"/><path d="M745 700 q4 -27 3 -26" opacity="0.46"/><path d="M762 700 q-2 -22 -7 -32" opacity="0.73"/><path d="M779 700 q-4 -28 3 -26" opacity="0.59"/><path d="M796 700 q-2 -17 3 -27" opacity="0.53"/><path d="M813 700 q2 -24 7 -32" opacity="0.60"/><path d="M830 700 q-4 -24 -3 -27" opacity="0.74"/><path d="M847 700 q2 -16 -3 -30" opacity="0.72"/><path d="M864 700 q2 -24 -3 -33" opacity="0.53"/><path d="M881 700 q-2 -20 3 -24" opacity="0.75"/><path d="M898 700 q-4 -16 -7 -40" opacity="0.57"/><path d="M915 700 q-2 -24 7 -31" opacity="0.72"/><path d="M932 700 q-4 -26 7 -39" opacity="0.57"/><path d="M949 700 q4 -24 3 -30" opacity="0.74"/><path d="M966 700 q2 -19 -3 -36" opacity="0.75"/><path d="M983 700 q-4 -29 -3 -24" opacity="0.38"/><path d="M1000 700 q2 -22 -3 -25" opacity="0.38"/><path d="M1017 700 q4 -29 3 -43" opacity="0.45"/><path d="M1034 700 q2 -16 7 -29" opacity="0.41"/><path d="M1051 700 q4 -16 3 -35" opacity="0.73"/><path d="M1068 700 q2 -19 -7 -33" opacity="0.44"/><path d="M1085 700 q-2 -16 3 -36" opacity="0.38"/><path d="M1102 700 q2 -24 -3 -31" opacity="0.55"/><path d="M1119 700 q-4 -17 3 -26" opacity="0.41"/><path d="M1136 700 q-4 -22 -7 -33" opacity="0.47"/><path d="M1153 700 q-2 -17 -3 -43" opacity="0.51"/><path d="M1170 700 q2 -27 7 -28" opacity="0.46"/><path d="M1187 700 q-2 -16 7 -40" opacity="0.41"/><path d="M1204 700 q-4 -29 -3 -26" opacity="0.36"/><path d="M1221 700 q-2 -26 3 -27" opacity="0.50"/><path d="M1238 700 q4 -24 -7 -44" opacity="0.36"/></g><g class="stream" opacity=".45"><path d="M-30 604 q150 -26 300 -12 q160 15 320 -8 q160 -23 320 -6 q150 15 320 -14" stroke-width="3.5"/><path d="M-30 612 q150 -26 300 -12 q160 15 320 -8 q160 -23 320 -6 q150 15 320 -14" stroke-width="1.2" opacity=".5"/></g></svg>',
		'<svg class="far" data-speed="0.04" viewBox="0 0 1440 300" xmlns="http://www.w3.org/2000/svg" stroke-width="1.6"> <g class="water"><path d="M-60 300 L-60 250 q34 -7 68 2 q30 8 58 -2 q22 -6 40 4 q10 6 8 18 L104 300 Z" opacity=".5"/></g> <g class="foam"><path d="M-46 254 q34 -7 68 2" stroke-width="2" opacity=".8"/><path d="M18 262 q32 -7 62 3" stroke-width="1.5" opacity=".55"/><path d="M-30 272 q40 -5 78 3" stroke-width="1.2" opacity="0.38"/></g> <path d="M30 236 Q120 96 210 236"/><path d="M210 236 Q300 96 390 236"/> <path d="M33 239 Q121 101 208 238" opacity=".5"/><path d="M212 238 Q301 100 388 239" opacity=".5"/> <line x1="18" y1="238" x2="402" y2="238"/> <line x1="70" y1="238" x2="70" y2="196"/><line x1="120" y1="238" x2="120" y2="152"/><line x1="170" y1="238" x2="170" y2="190"/> <line x1="250" y1="238" x2="250" y2="190"/><line x1="300" y1="238" x2="300" y2="152"/><line x1="350" y1="238" x2="350" y2="196"/> <line x1="210" y1="118" x2="210" y2="138" opacity=".6"/> <path d="M188 140 q0 -11 12 -11 h20 q12 0 12 11 q0 20 -22 28 q-22 -8 -22 -28 Z"/> <text x="210" y="160" text-anchor="middle" font-family="\'IBM Plex Mono\',ui-monospace,monospace" font-size="17" font-weight="500" fill="#2A241D" stroke="none">40</text> <path d="M470 254 q70 -22 148 -20 q86 3 162 -18 q84 -23 172 -14 q104 11 190 -10 q84 -20 150 2 v58 H470 Z"/> <path d="M498 249 l13 -10 M540 244 l13 -10 M584 239 l13 -9 M630 234 l12 -9 M678 228 l12 -9 M728 222 l12 -9 M780 216 l12 -8 M834 211 l12 -8 M890 208 l12 -8 M946 206 l12 -8 M1004 204 l11 -8 M1062 203 l11 -7 M1120 202 l11 -7 M1180 200 l11 -7 M1240 198 l11 -7 M1300 196 l11 -7 M1360 194 l11 -7" opacity=".4"/> <path d="M700 208 q6 -20 18 -23 q13 4 17 23" opacity=".5"/><path d="M1046 194 q6 -21 19 -24 q14 4 18 24" opacity=".5"/><path d="M1338 184 q5 -19 16 -22 q12 4 15 22" opacity=".45"/> </svg>',
		'<svg class="near" data-speed="0.085" viewBox="0 0 1440 300" xmlns="http://www.w3.org/2000/svg" stroke-width="2"> <line x1="430" y1="252" x2="1430" y2="252"/> <line x1="705" y1="252" x2="705" y2="74"/><line x1="702" y1="252" x2="702" y2="78" opacity=".4"/> <line x1="705" y1="82" x2="810" y2="82"/><line x1="742" y1="82" x2="742" y2="96" opacity=".7"/><line x1="792" y1="82" x2="792" y2="96" opacity=".7"/> <rect x="722" y="96" width="150" height="42" rx="4"/><rect x="727" y="101" width="140" height="32" rx="3" opacity=".45"/> <text x="797" y="125" text-anchor="middle" font-family="\'Fraunces\',Georgia,serif" font-size="24" font-weight="600" letter-spacing="3" fill="#2A241D" stroke="none">BEALE ST.</text> <rect x="748" y="146" width="98" height="24" rx="3" opacity=".8"/> <text x="797" y="163" text-anchor="middle" font-family="\'IBM Plex Mono\',ui-monospace,monospace" font-size="11" letter-spacing="2" fill="#2A241D" stroke="none" opacity=".8">HOME OF THE BLUES</text> <line x1="852" y1="60" x2="852" y2="88"/><path d="M852 60 q14 4 16 14" opacity=".8"/><ellipse cx="845" cy="90" rx="8" ry="6"/> <rect x="1010" y="96" width="46" height="156" rx="3"/><line x1="1033" y1="96" x2="1033" y2="64"/> <line x1="1018" y1="126" x2="1048" y2="126" opacity=".4"/><line x1="1018" y1="156" x2="1048" y2="156" opacity=".4"/><line x1="1018" y1="186" x2="1048" y2="186" opacity=".4"/> <path d="M1088 252 V152 h13 v-17 h13 v-15 h17 v15 h13 v17 h13 v100"/> <line x1="1096" y1="188" x2="1166" y2="188" opacity=".35"/><line x1="1096" y1="216" x2="1166" y2="216" opacity=".35"/> <path d="M1210 252 v-36 h58 v36"/><path d="M1298 252 q6 -24 20 -28 q15 4 19 28" opacity=".6"/><path d="M1348 252 q5 -19 16 -23 q12 4 16 23" opacity=".5"/> </svg>'
	);

	$out  = '<div class="wx" aria-hidden="true"><div class="scene">' . $svg[0] . $svg[1] . '</div></div>';
	$out .= '<div class="sky" aria-hidden="true">' . $svg[2] . $svg[3] . '</div>';

	/**
	 * Filter the editorial scene markup.
	 *
	 * @param string $out Markup.
	 */
	return apply_filters( 'brooks_law_edpage_scene', $out );
}

/* -------------------------------------------------------------------------
 * Render-time consolidation (v4.4)
 *
 * The nine pages that predate this module still hold their stylesheet inside
 * post content. Rather than rewriting anyone's content — which means database
 * writes, backups, and a plugin to undo them — the block is simply not
 * printed. The theme serves the same CSS from a cached file instead.
 *
 * Nothing is destroyed. The content in the database is untouched, so this is
 * reversible by unticking one Customizer box, and a page that is later edited
 * still has its original markup in the editor.
 *
 * Only blocks whose md5 matches a known copy are removed. Anything else — a
 * block someone adds tomorrow, or one of these edited by hand so the hash no
 * longer matches — is left exactly where it is and still prints.
 * ---------------------------------------------------------------------- */

/**
 * The inline blocks the theme now supersedes, by md5.
 *
 * @return array hash => stylesheet handle that replaces it.
 */
function brooks_law_edpage_known_blocks() {
	return apply_filters(
		'brooks_law_edpage_known_blocks',
		array(
			// The editorial layout: six pages, plus Cordova, whose copy is a
			// strict subset — 116 selectors, all identical to the main block.
			'79c48d4103ca7131a26a4d7957d8f023' => 'brooks-law-edpage',
			'2ddd81af333629dee9c0ea930578f0c8' => 'brooks-law-edpage',
			// The attorney profile layout: two pages, differing only in the
			// pull-quote measure, which is now a CSS variable.
			'51d2d7e009129bd5aee9602143684f27' => 'brooks-law-profile',
			'ef6c9a7cf244ce905f5b181e566e7a72' => 'brooks-law-profile',
		)
	);
}

/**
 * Which superseding stylesheets does this post need?
 *
 * @param string $content Post content.
 * @return string[] Handles.
 */
function brooks_law_edpage_needed_styles( $content ) {
	$needed = array();

	if ( false === strpos( $content, '<style' ) ) {
		return $needed;
	}

	$known = brooks_law_edpage_known_blocks();

	if ( preg_match_all( '/<style\b.*?<\/style>/is', $content, $matches ) ) {
		foreach ( $matches[0] as $block ) {
			$hash = md5( $block );

			if ( isset( $known[ $hash ] ) ) {
				$needed[ $known[ $hash ] ] = true;
			}
		}
	}

	return array_keys( $needed );
}

/**
 * Enqueue the profile stylesheet when a page needs it.
 */
function brooks_law_edpage_profile_assets() {
	if ( ! is_singular() || ! brooks_law_get_option( 'edpage_consolidate' ) ) {
		return;
	}

	$post = get_post();
	if ( ! $post instanceof WP_Post ) {
		return;
	}

	if ( ! in_array( 'brooks-law-profile', brooks_law_edpage_needed_styles( (string) $post->post_content ), true ) ) {
		return;
	}

	$path = get_template_directory() . '/assets/css/profile-pages.css';

	wp_enqueue_style(
		'brooks-law-profile',
		get_template_directory_uri() . '/assets/css/profile-pages.css',
		array(),
		file_exists( $path ) ? (string) filemtime( $path ) : BROOKS_LAW_VERSION
	);
}
add_action( 'wp_enqueue_scripts', 'brooks_law_edpage_profile_assets', 25 );

/**
 * Drop superseded blocks from the output.
 *
 * Runs late, after the ribbon and wrapper filters, so it only ever sees the
 * finished markup.
 *
 * @param string $content Content.
 * @return string
 */
function brooks_law_edpage_strip_inline( $content ) {
	if ( is_admin() || ! in_the_loop() || ! is_main_query() ) {
		return $content;
	}

	// A page switched to Standard still needs its inline block dropped, or it
	// would style itself regardless of the selector.
	if ( ! brooks_law_get_option( 'edpage_consolidate' ) && brooks_law_edpage_active() ) {
		return $content;
	}
	if ( false === strpos( $content, '<style' ) ) {
		return $content;
	}

	$known = brooks_law_edpage_known_blocks();

	return preg_replace_callback(
		'/<style\b.*?<\/style>\s*/is',
		function ( $m ) use ( $known ) {
			return isset( $known[ md5( rtrim( $m[0] ) ) ] ) ? '' : $m[0];
		},
		$content
	);
}
add_filter( 'the_content', 'brooks_law_edpage_strip_inline', 20 );

/**
 * Scene blocks the theme now supersedes, by md5.
 *
 * All seven legacy pages carry byte-identical copies, so an exact match is
 * safe: 6,459 bytes of weather pair and 3,596 of river layers.
 *
 * @return string[] Hashes.
 */
function brooks_law_edpage_known_scenes() {
	return apply_filters(
		'brooks_law_edpage_known_scenes',
		array(
			'a6fc1efad4', // .wx — weather pair.
			'fa9c5a37b0', // .sky — river layers.
		)
	);
}

/**
 * Replace a legacy page's pasted scene with the theme's copy.
 *
 * This does not make the page smaller — the scene has to stay inline, because
 * the weather cross-fade is driven by CSS from the parent and an external
 * reference could not be styled. What it buys is one source of truth: change
 * brooks_law_edpage_scene() and every page follows, instead of seven pages
 * each holding their own copy.
 *
 * Matching is by md5 of the exact block, so a page whose scene has been edited
 * keeps its own version untouched.
 *
 * @param string $content Content.
 * @return string
 */
function brooks_law_edpage_unify_scene( $content ) {
	if ( is_admin() || ! in_the_loop() || ! is_main_query() ) {
		return $content;
	}
	$standard = ! brooks_law_edpage_active();

	if ( ! $standard && ( ! brooks_law_get_option( 'edpage_unify_scene' ) || ! brooks_law_get_option( 'edpage_consolidate' ) ) ) {
		return $content;
	}
	if ( false === strpos( $content, 'class="blfE' ) ) {
		return $content;
	}

	$known   = brooks_law_edpage_known_scenes();
	$removed = 0;

	$patterns = array(
		'/<div class="wx"[^>]*>.*?<\/div><\/div>/s',
		'/<div class="sky"[^>]*>.*?<\/div>(?=\s*<)/s',
	);

	foreach ( $patterns as $pattern ) {
		$content = preg_replace_callback(
			$pattern,
			function ( $m ) use ( $known, &$removed ) {
				if ( in_array( substr( md5( $m[0] ), 0, 10 ), $known, true ) ) {
					$removed++;
					return '';
				}

				return $m[0];
			},
			$content,
			1
		);
	}

	// Only swap in the theme copy if both halves were recognised and dropped.
	if ( 2 !== $removed ) {
		return $content;
	}

	// Switched to Standard: the artwork goes and nothing replaces it.
	if ( $standard ) {
		return $content;
	}

	return preg_replace(
		'/(<div class="blfE"[^>]*>)/',
		'$1' . str_replace( '$', '\$', brooks_law_edpage_scene() ),
		$content,
		1
	);
}
add_filter( 'the_content', 'brooks_law_edpage_unify_scene', 21 );
