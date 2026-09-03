<?php
/**
 * Brooks Law 4.2 — Ribbon background art.
 *
 * A shared library of original line-art motifs for the page-title ribbon,
 * built on the same pattern as brooks_law_sa_icons(): a keyed array of
 * label + inline SVG body, a whitelist sanitizer with a safe fallback, and
 * a Customizer section for site-wide defaults.
 *
 * Each motif is drawn on a 480x260 canvas with its visual mass weighted to
 * one side, so the ribbon text panel always has clear ground opposite. The
 * art uses currentColor and inherits the ribbon tint.
 *
 * The art is decorative only. It is rendered aria-hidden, behind the text,
 * and never replaces the existing photo variant — a page with a ribbon photo
 * set keeps it, and art is skipped.
 *
 * @package Brooks_Law
 * @since   4.2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The motif library.
 *
 * @return array[] key => label, side, svg.
 */
function brooks_law_ribbon_art() {
	static $art = null;

	if ( null === $art ) {
		$art = array(
			'steamboat' => array(
				'label' => __( 'Steamboat', 'brooks-law-30-pro' ),
				'side'  => 'right',
				'svg'   => '<g fill="none" stroke="currentColor" stroke-width="3" stroke-linejoin="round"> <path d="M250 196h190l-14 34H262z"/> <path d="M264 196v-30h164v30"/> <path d="M276 166v-28h140v28"/> <path d="M292 138v-24h108v24"/> <path d="M316 114V96h60v18"/> </g> <g fill="currentColor"> <rect x="272" y="174" width="9" height="14"/><rect x="292" y="174" width="9" height="14"/> <rect x="312" y="174" width="9" height="14"/><rect x="332" y="174" width="9" height="14"/> <rect x="352" y="174" width="9" height="14"/><rect x="372" y="174" width="9" height="14"/> <rect x="392" y="174" width="9" height="14"/><rect x="412" y="174" width="9" height="14"/> <rect x="286" y="146" width="8" height="12"/><rect x="306" y="146" width="8" height="12"/> <rect x="326" y="146" width="8" height="12"/><rect x="346" y="146" width="8" height="12"/> <rect x="366" y="146" width="8" height="12"/><rect x="386" y="146" width="8" height="12"/> <rect x="322" y="40" width="8" height="58"/><rect x="362" y="40" width="8" height="58"/> </g> <g fill="none" stroke="currentColor" stroke-width="3"> <circle cx="446" cy="200" r="26"/> <path d="M420 200h52M446 174v52M428 182l36 36M428 218l36-36"/> </g> <g stroke="currentColor" stroke-width="2" opacity=".55"> <path d="M40 238h150M70 250h180"/> </g>',
			),
			'riverboat' => array(
				'label' => __( 'River boat', 'brooks-law-30-pro' ),
				'side'  => 'right',
				'svg'   => '<g fill="none" stroke="currentColor" stroke-width="3" stroke-linejoin="round"> <path d="M258 200h200l-18 30H268z"/> <path d="M272 200v-34h172v34"/> <path d="M288 166v-30h140v30"/> <path d="M330 136V112h56v24"/> </g> <g fill="currentColor"> <rect x="282" y="176" width="10" height="16"/><rect x="304" y="176" width="10" height="16"/> <rect x="326" y="176" width="10" height="16"/><rect x="348" y="176" width="10" height="16"/> <rect x="370" y="176" width="10" height="16"/><rect x="392" y="176" width="10" height="16"/> <rect x="414" y="176" width="10" height="16"/> <rect x="300" y="144" width="9" height="14"/><rect x="324" y="144" width="9" height="14"/> <rect x="348" y="144" width="9" height="14"/><rect x="372" y="144" width="9" height="14"/> <rect x="396" y="144" width="9" height="14"/> <rect x="352" y="60" width="7" height="52"/> </g> <g stroke="currentColor" stroke-width="2" opacity=".5"> <path d="M30 244h170M60 256h190M250 244h60"/> </g>',
			),
			'mbridge' => array(
				'label' => __( 'M bridge', 'brooks-law-30-pro' ),
				'side'  => 'left',
				'svg'   => '<g fill="none" stroke="currentColor" stroke-width="4" stroke-linejoin="round"> <path d="M20 210h300"/> <path d="M40 210V120M300 210V120"/> <path d="M40 120q42-86 85 0"/> <path d="M215 120q42-86 85 0"/> <path d="M125 120q45 60 90 0"/> </g> <g stroke="currentColor" stroke-width="2" opacity=".75"> <path d="M60 210v-72M82 210v-84M104 210v-72"/> <path d="M236 210v-72M258 210v-84M280 210v-72"/> </g> <g fill="currentColor"><rect x="16" y="208" width="308" height="5"/></g> <g stroke="currentColor" stroke-width="2" opacity=".45"> <path d="M30 236h250M70 250h230"/> </g>',
			),
			'skyline' => array(
				'label' => __( 'Downtown skyline', 'brooks-law-30-pro' ),
				'side'  => 'right',
				'svg'   => '<g fill="none" stroke="currentColor" stroke-width="3" stroke-linejoin="round"> <path d="M250 226v-72h34v72"/> <path d="M292 226v-96h30v96"/> <path d="M330 226V96h40v130"/> <path d="M378 226v-84h26v84"/> <path d="M412 226v-58h30v58"/> <path d="M330 96l20-30 20 30"/> </g> <g fill="currentColor" opacity=".8"> <rect x="258" y="166" width="7" height="9"/><rect x="270" y="166" width="7" height="9"/> <rect x="258" y="186" width="7" height="9"/><rect x="270" y="186" width="7" height="9"/> <rect x="300" y="144" width="7" height="9"/><rect x="311" y="144" width="7" height="9"/> <rect x="300" y="166" width="7" height="9"/><rect x="311" y="166" width="7" height="9"/> <rect x="340" y="116" width="8" height="10"/><rect x="354" y="116" width="8" height="10"/> <rect x="340" y="142" width="8" height="10"/><rect x="354" y="142" width="8" height="10"/> <rect x="340" y="168" width="8" height="10"/><rect x="354" y="168" width="8" height="10"/> <rect x="386" y="158" width="7" height="9"/><rect x="386" y="180" width="7" height="9"/> <rect x="420" y="184" width="7" height="9"/> </g> <path d="M240 226h216" stroke="currentColor" stroke-width="4"/>',
			),
			'barsign' => array(
				'label' => __( 'Bar sign', 'brooks-law-30-pro' ),
				'side'  => 'right',
				'svg'   => '<g fill="none" stroke="currentColor" stroke-width="4" stroke-linejoin="round"> <path d="M300 30v200"/> <rect x="312" y="60" width="130" height="96" rx="8"/> <rect x="324" y="72" width="106" height="72" rx="5"/> </g> <g fill="currentColor"> <rect x="344" y="94" width="10" height="30"/><rect x="360" y="94" width="10" height="30"/> <rect x="376" y="94" width="10" height="30"/><rect x="392" y="94" width="10" height="30"/> </g> <g fill="currentColor" opacity=".85"> <circle cx="318" cy="66" r="4"/><circle cx="348" cy="60" r="4"/><circle cx="377" cy="60" r="4"/> <circle cx="406" cy="60" r="4"/><circle cx="436" cy="66" r="4"/> <circle cx="318" cy="150" r="4"/><circle cx="348" cy="156" r="4"/><circle cx="377" cy="156" r="4"/> <circle cx="406" cy="156" r="4"/><circle cx="436" cy="150" r="4"/> </g> <g stroke="currentColor" stroke-width="3" opacity=".5"> <path d="M284 230h150"/> </g>',
			),
			'sign2am' => array(
				'label' => __( 'Bar sign — Open 2 AM', 'brooks-law-30-pro' ),
				'side'  => 'right',
				'svg'   => '<g fill="none" stroke="currentColor" stroke-width="4" stroke-linejoin="round"> <path d="M298 24v212"/> <rect x="310" y="56" width="140" height="104" rx="8"/> <rect x="322" y="68" width="116" height="80" rx="5"/> </g> <text x="380" y="102" font-family="Georgia, serif" font-size="26" font-weight="700" fill="currentColor" text-anchor="middle" letter-spacing="2">OPEN</text> <text x="380" y="134" font-family="Georgia, serif" font-size="30" font-weight="700" fill="currentColor" text-anchor="middle" letter-spacing="1">2 AM</text> <g fill="currentColor" opacity=".9"> <circle cx="316" cy="62" r="4"/><circle cx="346" cy="56" r="4"/><circle cx="380" cy="56" r="4"/> <circle cx="414" cy="56" r="4"/><circle cx="444" cy="62" r="4"/> <circle cx="316" cy="154" r="4"/><circle cx="346" cy="160" r="4"/><circle cx="380" cy="160" r="4"/> <circle cx="414" cy="160" r="4"/><circle cx="444" cy="154" r="4"/> </g> <path d="M282 236h160" stroke="currentColor" stroke-width="3" opacity=".5"/>',
			),
			'signlive' => array(
				'label' => __( 'Bar sign — Live Music', 'brooks-law-30-pro' ),
				'side'  => 'right',
				'svg'   => '<g fill="none" stroke="currentColor" stroke-width="4" stroke-linejoin="round"> <path d="M292 24v212"/> <rect x="304" y="52" width="152" height="110" rx="8"/> <rect x="316" y="64" width="128" height="86" rx="5"/> </g> <text x="380" y="100" font-family="Georgia, serif" font-size="27" font-weight="700" fill="currentColor" text-anchor="middle" letter-spacing="2">LIVE</text> <text x="380" y="134" font-family="Georgia, serif" font-size="25" font-weight="700" fill="currentColor" text-anchor="middle" letter-spacing="1">MUSIC</text> <g fill="currentColor"> <ellipse cx="470" cy="118" rx="9" ry="7" transform="rotate(-18 470 118)"/> <rect x="477" y="88" width="4" height="28"/> <path d="M477 88q14-4 24-3v6q-11-1-24 3z"/> </g> <g fill="currentColor" opacity=".9"> <circle cx="310" cy="58" r="4"/><circle cx="342" cy="52" r="4"/><circle cx="380" cy="52" r="4"/> <circle cx="418" cy="52" r="4"/><circle cx="450" cy="58" r="4"/> <circle cx="310" cy="156" r="4"/><circle cx="342" cy="162" r="4"/><circle cx="380" cy="162" r="4"/> <circle cx="418" cy="162" r="4"/><circle cx="450" cy="156" r="4"/> </g> <path d="M276 236h170" stroke="currentColor" stroke-width="3" opacity=".5"/>',
			),
			'trolley' => array(
				'label' => __( 'Trolley car', 'brooks-law-30-pro' ),
				'side'  => 'right',
				'svg'   => '<g fill="none" stroke="currentColor" stroke-width="3" stroke-linejoin="round"> <rect x="252" y="96" width="196" height="112" rx="8"/> <path d="M252 132h196"/> <path d="M264 132v-24h172v24"/> <path d="M350 30v66"/> <path d="M350 40l40-14"/> </g> <g fill="currentColor" opacity=".85"> <rect x="266" y="146" width="30" height="34"/><rect x="308" y="146" width="30" height="34"/> <rect x="350" y="146" width="30" height="34"/><rect x="392" y="146" width="30" height="34"/> </g> <g fill="none" stroke="currentColor" stroke-width="3"> <circle cx="292" cy="220" r="14"/><circle cx="410" cy="220" r="14"/> </g> <path d="M232 236h240" stroke="currentColor" stroke-width="3" opacity=".6"/>',
			),
			'gate' => array(
				'label' => __( 'Iron gate with note', 'brooks-law-30-pro' ),
				'side'  => 'left',
				'svg'   => '<g fill="none" stroke="currentColor" stroke-width="4" stroke-linejoin="round"> <rect x="24" y="70" width="26" height="160"/> <rect x="278" y="70" width="26" height="160"/> <path d="M50 118q102-52 228 0"/> <path d="M50 118v112M304 118v112"/> </g> <g stroke="currentColor" stroke-width="3" opacity=".85"> <path d="M78 110v120M106 102v128M134 96v134M162 93v137M190 96v134M218 102v128M246 110v120"/> <path d="M50 170h254M50 214h254"/> </g> <g fill="currentColor"> <ellipse cx="150" cy="152" rx="13" ry="10" transform="rotate(-18 150 152)"/> <rect x="160" y="112" width="5" height="38"/> <ellipse cx="196" cy="146" rx="13" ry="10" transform="rotate(-18 196 146)"/> <rect x="206" y="106" width="5" height="38"/> <path d="M160 112q26-8 46-6v9q-20-2-46 6z"/> </g>',
			),
			'steelguitar' => array(
				'label' => __( 'Steel guitar', 'brooks-law-30-pro' ),
				'side'  => 'right',
				'svg'   => '<g fill="none" stroke="currentColor" stroke-width="3" stroke-linejoin="round"> <rect x="248" y="112" width="200" height="56" rx="6"/> <path d="M248 140h200"/> <rect x="262" y="176" width="26" height="46"/> <rect x="408" y="176" width="26" height="46"/> <path d="M288 198h120"/> </g> <g stroke="currentColor" stroke-width="2" opacity=".8"> <path d="M256 120h184M256 128h184M256 152h184M256 160h184"/> </g> <g fill="currentColor"> <circle cx="272" cy="98" r="5"/><circle cx="290" cy="98" r="5"/><circle cx="308" cy="98" r="5"/> <circle cx="388" cy="98" r="5"/><circle cx="406" cy="98" r="5"/><circle cx="424" cy="98" r="5"/> </g> <g fill="none" stroke="currentColor" stroke-width="3"><path d="M272 103v9M290 103v9M308 103v9M388 103v9M406 103v9M424 103v9"/></g>',
			),
			'bluesguitar' => array(
				'label' => __( 'Blues guitar', 'brooks-law-30-pro' ),
				'side'  => 'right',
				'svg'   => '<g fill="none" stroke="currentColor" stroke-width="3" stroke-linejoin="round"> <path d="M352 232c-30 0-52-20-52-46 0-18 12-30 12-46s-12-24-12-40c0-24 20-40 44-40 18 0 28 10 36 10s18-10 36-10c24 0 44 16 44 40 0 16-12 24-12 40s12 28 12 46c0 26-22 46-52 46-20 0-24-8-28-8s-8 8-28 8z"/> <circle cx="380" cy="126" r="24"/> <path d="M368 60V22h24v38"/> <rect x="360" y="6" width="40" height="18" rx="4"/> </g> <g stroke="currentColor" stroke-width="2" opacity=".8"> <path d="M370 24v152M377 24v152M384 24v152M391 24v152"/> </g> <path d="M344 178h72" stroke="currentColor" stroke-width="4"/>',
			),
			'buffalo' => array(
				'label' => __( 'Buffalo', 'brooks-law-30-pro' ),
				'side'  => 'right',
				'svg'   => '<path fill="currentColor" d=" M334 104 C346 84 362 76 380 79 C402 83 424 102 440 128 C454 151 462 178 466 202 L436 205 L330 209 C322 186 320 152 334 104 Z"/> <path fill="currentColor" d=" M248 232 L249 176 C251 156 259 143 272 136 C288 128 304 122 316 112 C324 105 330 104 334 108 C324 148 322 182 328 208 L306 210 C296 212 288 219 282 230 Z"/> <path fill="none" stroke="#12202e" stroke-width="3.5" opacity=".5" d="M330 110c-8 30-10 66-4 98"/> <path fill="currentColor" d="M262 230 L296 222 L288 268 L266 268 Z"/> <path fill="none" stroke="#12202e" stroke-width="3" opacity=".4" d="M384 88c-6 32-7 70-3 116"/> <g fill="none" stroke="#12202e" stroke-width="2.4" stroke-linecap="round" opacity=".28"> <path d="M348 106l-4 96M362 96l-3 106M374 90l-2 112"/> </g> <path fill="none" stroke="currentColor" stroke-width="6" stroke-linecap="round" d="M274 138c-12-8-25-3-29 8"/> <circle cx="270" cy="174" r="3.6" fill="#12202e" opacity=".8"/> <g fill="currentColor"> <path d="M306 209h20l-5 53h-16z"/> <path d="M340 208h17l-4 52h-14z"/> <path d="M424 205h13l-2 53h-12z"/> <path d="M448 204h13l-2 54h-12z"/> </g> <path fill="none" stroke="currentColor" stroke-width="4" stroke-linecap="round" d="M465 196c9 8 11 22 6 34"/> <path d="M236 268h254" stroke="currentColor" stroke-width="3" opacity=".45"/>',
			),
			'oak' => array(
				'label' => __( 'Oak tree', 'brooks-law-30-pro' ),
				'side'  => 'left',
				'svg'   => '<g fill="none" stroke="currentColor" stroke-width="4" stroke-linejoin="round"> <path d="M164 232v-72"/> <path d="M164 176l-34-30M164 190l34-26M164 158l-22-26M164 148l26-24"/> <path d="M96 118c-18-6-26-24-18-40 4-10 14-16 24-16 2-20 20-34 42-34 14 0 26 6 34 16 8-10 20-16 34-16 22 0 40 14 42 34 10 0 20 6 24 16 8 16 0 34-18 40"/> <path d="M96 118c6 14 22 24 40 24h56c18 0 34-10 40-24"/> </g> <g stroke="currentColor" stroke-width="2" opacity=".6"> <path d="M118 96q18-12 36 0M154 82q18-12 36 0M186 98q18-12 34 0"/> </g> <path d="M124 236h84" stroke="currentColor" stroke-width="3" opacity=".55"/>',
			),
			'house' => array(
				'label' => __( 'Columned house', 'brooks-law-30-pro' ),
				'side'  => 'right',
				'svg'   => '<g fill="none" stroke="currentColor" stroke-width="3" stroke-linejoin="round"> <path d="M244 112L352 46l108 66"/> <path d="M266 112h172"/> <path d="M300 112L352 78l52 34"/> <path d="M258 112v116h188V112"/> <path d="M258 172h188"/> </g> <g fill="none" stroke="currentColor" stroke-width="3"> <path d="M282 118v104M316 118v104M350 118v104M384 118v104M418 118v104"/> </g> <g stroke="currentColor" stroke-width="2.4" opacity=".85"> <path d="M276 120h12M310 120h12M344 120h12M378 120h12M412 120h12"/> <path d="M276 220h12M310 220h12M344 220h12M378 220h12M412 220h12"/> </g> <g fill="none" stroke="currentColor" stroke-width="2.2" opacity=".75"> <rect x="336" y="182" width="28" height="46"/> <rect x="292" y="188" width="18" height="24"/><rect x="392" y="188" width="18" height="24"/> <rect x="292" y="130" width="18" height="26"/><rect x="336" y="130" width="28" height="26"/> <rect x="392" y="130" width="18" height="26"/> </g> <path d="M232 228h240" stroke="currentColor" stroke-width="3.5"/> <g stroke="currentColor" stroke-width="2" opacity=".45"><path d="M248 246h208"/></g>',
			),
			'pyramid' => array(
				'label' => __( 'Pyramid', 'brooks-law-30-pro' ),
				'side'  => 'right',
				'svg'   => '<g fill="none" stroke="currentColor" stroke-width="4" stroke-linejoin="round"> <path d="M262 224L360 66l98 158z"/> <path d="M360 66v158"/> <path d="M296 170h128"/> <path d="M312 144h96"/> </g> <g stroke="currentColor" stroke-width="2" opacity=".6"> <path d="M282 198h156M330 106h60"/> </g> <path d="M244 224h232" stroke="currentColor" stroke-width="4"/>',
			),
		);
	}

	/**
	 * Filter the ribbon art library.
	 *
	 * @param array $art Motifs.
	 */
	return apply_filters( 'brooks_law_ribbon_art', $art );
}

/**
 * Whitelist a motif key. Unknown values become '' (no art).
 *
 * @param string $value Candidate key.
 * @return string
 */
function brooks_law_sanitize_ribbon_art( $value ) {
	$value = sanitize_key( $value );

	return array_key_exists( $value, brooks_law_ribbon_art() ) ? $value : '';
}

/**
 * Clamp an opacity percentage to a sane, legible range.
 *
 * Below 8 the art is invisible; above 45 it starts to fight the title.
 *
 * @param mixed $value Candidate.
 * @return int
 */
function brooks_law_sanitize_ribbon_art_opacity( $value ) {
	$value = absint( $value );

	if ( $value < 8 ) {
		$value = 8;
	}
	if ( $value > 45 ) {
		$value = 45;
	}

	return $value;
}

/**
 * Whitelist a side.
 *
 * @param string $value Candidate.
 * @return string 'left', 'right', or '' for the motif's own default.
 */
function brooks_law_sanitize_ribbon_art_side( $value ) {
	$value = sanitize_key( $value );

	return in_array( $value, array( 'left', 'right' ), true ) ? $value : '';
}

/**
 * Resolve the art settings for one post: per-page value, else site default.
 *
 * @param int $post_id Post.
 * @return array|false key, svg, side, opacity — or false when there is none.
 */
function brooks_law_ribbon_art_for( $post_id ) {
	if ( ! brooks_law_get_option( 'ribbon_art_enable' ) ) {
		return false;
	}

	$key = get_post_meta( $post_id, '_br_ribbon_art', true );

	if ( 'none' === $key ) {
		return false;
	}

	if ( '' === $key ) {
		$key = (string) brooks_law_get_option( 'ribbon_art_default' );
	}

	$key = brooks_law_sanitize_ribbon_art( $key );
	if ( '' === $key ) {
		return false;
	}

	$art = brooks_law_ribbon_art();

	$side = brooks_law_sanitize_ribbon_art_side( get_post_meta( $post_id, '_br_ribbon_art_side', true ) );
	if ( '' === $side ) {
		$side = $art[ $key ]['side'];
	}

	$opacity = get_post_meta( $post_id, '_br_ribbon_art_opacity', true );
	if ( '' === $opacity ) {
		$opacity = brooks_law_get_option( 'ribbon_art_opacity' );
	}

	$resolved = array(
		'key'     => $key,
		'svg'     => $art[ $key ]['svg'],
		'side'    => $side,
		'opacity' => brooks_law_sanitize_ribbon_art_opacity( $opacity ),
	);

	/**
	 * Filter the resolved ribbon art for a post.
	 *
	 * @param array $resolved Settings.
	 * @param int   $post_id  Post.
	 */
	return apply_filters( 'brooks_law_ribbon_art_resolved', $resolved, $post_id );
}

/**
 * Customizer: site-wide defaults, filed with the other ribbon controls.
 *
 * @param WP_Customize_Manager $wp_customize Manager.
 */
function brooks_law_ribbon_art_customize( $wp_customize ) {

	$wp_customize->add_section(
		'brooks_law_ribbon_art',
		array(
			'title'       => __( 'Ribbon Artwork', 'brooks-law-30-pro' ),
			'description' => __( 'Line-art motifs behind the page-title ribbon. Set the default here; any page can override it in the Page Ribbon box on the edit screen. Pages that use a ribbon photo keep the photo.', 'brooks-law-30-pro' ),
			'priority'    => 133,
		)
	);

	$wp_customize->add_setting(
		'ribbon_art_enable',
		array(
			'default'           => true,
			'sanitize_callback' => 'brooks_law_sanitize_checkbox',
		)
	);
	$wp_customize->add_control(
		'ribbon_art_enable',
		array(
			'section' => 'brooks_law_ribbon_art',
			'label'   => __( 'Show ribbon artwork', 'brooks-law-30-pro' ),
			'type'    => 'checkbox',
		)
	);

	$choices = array( '' => __( '— None —', 'brooks-law-30-pro' ) );
	foreach ( brooks_law_ribbon_art() as $key => $item ) {
		$choices[ $key ] = $item['label'];
	}

	$wp_customize->add_setting(
		'ribbon_art_default',
		array(
			'default'           => 'mbridge',
			'sanitize_callback' => 'brooks_law_sanitize_ribbon_art',
		)
	);
	$wp_customize->add_control(
		'ribbon_art_default',
		array(
			'section'     => 'brooks_law_ribbon_art',
			'label'       => __( 'Default motif', 'brooks-law-30-pro' ),
			'description' => __( 'Used on any page that has not chosen its own.', 'brooks-law-30-pro' ),
			'type'        => 'select',
			'choices'     => $choices,
		)
	);

	$wp_customize->add_setting(
		'ribbon_art_opacity',
		array(
			'default'           => 22,
			'sanitize_callback' => 'brooks_law_sanitize_ribbon_art_opacity',
		)
	);
	$wp_customize->add_control(
		'ribbon_art_opacity',
		array(
			'section'     => 'brooks_law_ribbon_art',
			'label'       => __( 'Default opacity (%)', 'brooks-law-30-pro' ),
			'description' => __( 'Between 8 and 45. Around 20 to 26 reads as a wash without competing with the title.', 'brooks-law-30-pro' ),
			'type'        => 'number',
			'input_attrs' => array(
				'min'  => 8,
				'max'  => 45,
				'step' => 1,
			),
		)
	);

	$wp_customize->add_setting(
		'ribbon_art_parallax',
		array(
			'default'           => true,
			'sanitize_callback' => 'brooks_law_sanitize_checkbox',
		)
	);
	$wp_customize->add_control(
		'ribbon_art_parallax',
		array(
			'section'     => 'brooks_law_ribbon_art',
			'label'       => __( 'Drift on scroll', 'brooks-law-30-pro' ),
			'description' => __( 'The motif shifts slightly as the ribbon passes through the viewport. Always disabled for visitors who ask for reduced motion.', 'brooks-law-30-pro' ),
			'type'        => 'checkbox',
		)
	);
}
add_action( 'customize_register', 'brooks_law_ribbon_art_customize', 26 );

/**
 * Front-end script for the drift, loaded only where art is present.
 */
function brooks_law_ribbon_art_assets() {
	if ( ! is_singular() || ! brooks_law_get_option( 'ribbon_art_enable' ) ) {
		return;
	}
	if ( ! brooks_law_get_option( 'ribbon_art_parallax' ) ) {
		return;
	}

	wp_enqueue_script(
		'brooks-law-ribbon-art',
		get_template_directory_uri() . '/assets/js/ribbon-art.js',
		array(),
		BROOKS_LAW_VERSION,
		array(
			'in_footer' => true,
			'strategy'  => 'defer',
		)
	);
}
add_action( 'wp_enqueue_scripts', 'brooks_law_ribbon_art_assets', 20 );
