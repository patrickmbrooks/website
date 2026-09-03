<?php
/**
 * Editorial layer — background artwork, assets, and settings migration.
 *
 * Added in 2.4. Everything in this file is additive: it enqueues two
 * assets, prints one decorative element, adds one body class, and
 * extends the existing side-by-side settings migration to source from
 * the 2.3 slug. No template, option panel, or Customizer field defined
 * elsewhere in the theme is touched.
 *
 * @package brooks-law
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Enqueue the editorial stylesheet and behaviour script.
 *
 * Runs after brooks_law_scripts() (priority 20) so editorial.css loads
 * after style.css and can read its custom properties.
 */
function brooks_law_editorial_sky_active() {
	static $active = null;

	if ( null !== $active ) {
		return $active;
	}

	$active = ! is_admin() && ! is_feed() && ! is_embed();

	// A page carrying its own .sky artwork in its content supplies both the
	// markup and, through the editorial page layer, the styling for it.
	if ( $active && is_singular() ) {
		$post = get_post();
		if ( $post instanceof WP_Post && false !== strpos( (string) $post->post_content, 'class="sky"' ) ) {
			$active = false;
		}
	}

	/**
	 * Filter whether the editorial background artwork is active.
	 *
	 * This single predicate governs the artwork markup, the body class that
	 * scopes its stacking rules, and the stylesheet that draws it — so a site
	 * can switch the whole layer off in one place and never end up with the
	 * markup on the page and no CSS for it, or the reverse.
	 *
	 * @since 5.3.0
	 *
	 * @param bool $active Whether the artwork layer is active.
	 */
	return $active = (bool) apply_filters( 'brooks_law_editorial_sky_active', $active );
}

/**
 * Enqueue the editorial stylesheet and behaviour script.
 *
 * Loaded when the artwork layer is active, or when the request can show one
 * of the .pb-* / .blf-* components the same sheet styles. Previously both
 * loaded on every request including admin-adjacent ones, which is what
 * inc/component-loader.php was written to avoid.
 */
function brooks_law_editorial_assets() {

	$sky        = brooks_law_editorial_sky_active();
	$components = function_exists( 'brooks_law_components_needed' ) && brooks_law_components_needed();

	if ( ! $sky && ! $components ) {
		return;
	}

	$css = get_template_directory() . '/assets/css/editorial.css';
	$js  = get_template_directory() . '/assets/js/editorial.js';

	wp_enqueue_style(
		'brooks-law-editorial',
		get_template_directory_uri() . '/assets/css/editorial.css',
		array( 'brooks-law-style' ),
		file_exists( $css ) ? (string) filemtime( $css ) : BROOKS_LAW_VERSION
	);

	wp_enqueue_script(
		'brooks-law-editorial',
		get_template_directory_uri() . '/assets/js/editorial.js',
		array(),
		file_exists( $js ) ? (string) filemtime( $js ) : BROOKS_LAW_VERSION,
		array(
			'strategy'  => 'defer',
			'in_footer' => true,
		)
	);
}
add_action( 'wp_enqueue_scripts', 'brooks_law_editorial_assets', 20 );

/**
 * Body class hook for the editorial layer's scoping.
 *
 * @param array $classes Existing body classes.
 * @return array
 */
function brooks_law_editorial_body_class( $classes ) {
	if ( brooks_law_editorial_sky_active() ) {
		$classes[] = 'brooks-editorial';
	}

	return $classes;
}
add_filter( 'body_class', 'brooks_law_editorial_body_class' );

/**
 * The background artwork: river, bluff, bridge, Beale Street sign.
 *
 * Decorative only — aria-hidden, pointer-events disabled in CSS,
 * invisible to assistive technology.
 *
 * @return string
 */
function brooks_law_editorial_sky() {

	ob_start();
	?>
	<div class="blf-sky" aria-hidden="true">

		<svg class="far" data-speed="0.04" viewBox="0 0 1440 300" xmlns="http://www.w3.org/2000/svg" stroke-width="1.6" focusable="false" role="presentation">
			<g class="water"><path d="M-60 300 L-60 250 q34 -7 68 2 q30 8 58 -2 q22 -6 40 4 q10 6 8 18 L104 300 Z" opacity=".5"/></g>
			<g class="foam">
				<path d="M-46 254 q34 -7 68 2" stroke-width="2" opacity=".8"/>
				<path d="M18 262 q32 -7 62 3" stroke-width="1.5" opacity=".55"/>
				<path d="M-30 272 q40 -5 78 3" stroke-width="1.2" opacity=".38"/>
			</g>
			<path d="M30 236 Q120 96 210 236"/>
			<path d="M210 236 Q300 96 390 236"/>
			<path d="M33 239 Q121 101 208 238" opacity=".5"/>
			<path d="M212 238 Q301 100 388 239" opacity=".5"/>
			<line x1="18" y1="238" x2="402" y2="238"/>
			<line x1="70" y1="238" x2="70" y2="196"/>
			<line x1="120" y1="238" x2="120" y2="152"/>
			<line x1="170" y1="238" x2="170" y2="190"/>
			<line x1="250" y1="238" x2="250" y2="190"/>
			<line x1="300" y1="238" x2="300" y2="152"/>
			<line x1="350" y1="238" x2="350" y2="196"/>
			<line x1="210" y1="118" x2="210" y2="138" opacity=".6"/>
			<path d="M188 140 q0 -11 12 -11 h20 q12 0 12 11 q0 20 -22 28 q-22 -8 -22 -28 Z"/>
			<path d="M470 254 q70 -22 148 -20 q86 3 162 -18 q84 -23 172 -14 q104 11 190 -10 q84 -20 150 2 v58 H470 Z"/>
			<path d="M498 249 l13 -10 M540 244 l13 -10 M584 239 l13 -9 M630 234 l12 -9 M678 228 l12 -9 M728 222 l12 -9 M780 216 l12 -8 M834 211 l12 -8 M890 208 l12 -8 M946 206 l12 -8 M1004 204 l11 -8 M1062 203 l11 -7 M1120 202 l11 -7 M1180 200 l11 -7 M1240 198 l11 -7 M1300 196 l11 -7 M1360 194 l11 -7" opacity=".4"/>
			<path d="M700 208 q6 -20 18 -23 q13 4 17 23" opacity=".5"/>
			<path d="M1046 194 q6 -21 19 -24 q14 4 18 24" opacity=".5"/>
			<path d="M1338 184 q5 -19 16 -22 q12 4 15 22" opacity=".45"/>
		</svg>

		<svg class="near" data-speed="0.085" viewBox="0 0 1440 300" xmlns="http://www.w3.org/2000/svg" stroke-width="2" focusable="false" role="presentation">
			<line x1="430" y1="252" x2="1430" y2="252"/>
			<line x1="705" y1="252" x2="705" y2="74"/>
			<line x1="702" y1="252" x2="702" y2="78" opacity=".4"/>
			<line x1="705" y1="82" x2="810" y2="82"/>
			<line x1="742" y1="82" x2="742" y2="96" opacity=".7"/>
			<line x1="792" y1="82" x2="792" y2="96" opacity=".7"/>
			<rect x="722" y="96" width="150" height="42" rx="4"/>
			<rect x="727" y="101" width="140" height="32" rx="3" opacity=".45"/>
			<text x="797" y="125" text-anchor="middle" font-family="Georgia,serif" font-size="24" font-weight="700" letter-spacing="3" fill="#222933" stroke="none">BEALE ST.</text>
			<rect x="748" y="146" width="98" height="24" rx="3" opacity=".8"/>
			<text x="797" y="163" text-anchor="middle" font-family="ui-monospace,monospace" font-size="11" letter-spacing="2" fill="#222933" stroke="none" opacity=".8">HOME OF THE BLUES</text>
			<line x1="852" y1="60" x2="852" y2="88"/>
			<path d="M852 60 q14 4 16 14" opacity=".8"/>
			<ellipse cx="845" cy="90" rx="8" ry="6"/>
			<rect x="1010" y="96" width="46" height="156" rx="3"/>
			<line x1="1033" y1="96" x2="1033" y2="64"/>
			<line x1="1018" y1="126" x2="1048" y2="126" opacity=".4"/>
			<line x1="1018" y1="156" x2="1048" y2="156" opacity=".4"/>
			<line x1="1018" y1="186" x2="1048" y2="186" opacity=".4"/>
			<path d="M1088 252 V152 h13 v-17 h13 v-15 h17 v15 h13 v17 h13 v100"/>
			<line x1="1096" y1="188" x2="1166" y2="188" opacity=".35"/>
			<line x1="1096" y1="216" x2="1166" y2="216" opacity=".35"/>
			<path d="M1210 252 v-36 h58 v36"/>
			<path d="M1298 252 q6 -24 20 -28 q15 4 19 28" opacity=".6"/>
			<path d="M1348 252 q5 -19 16 -23 q12 4 16 23" opacity=".5"/>
		</svg>

	</div>
	<?php
	return ob_get_clean();
}

/**
 * Print the artwork once, immediately after <body> opens.
 *
 * Skipped in admin, feeds, and embeds, and on any page whose content
 * already carries its own `.sky` artwork (the Probation Violation
 * family), so nothing ever doubles up.
 */
function brooks_law_editorial_render_sky() {

	if ( ! brooks_law_editorial_sky_active() ) {
		return;
	}

	echo brooks_law_editorial_sky(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static markup, no user input.
}
add_action( 'wp_body_open', 'brooks_law_editorial_render_sky' );

/**
 * Settings migration for 2.4: prefer the 2.3 slug, then the original.
 *
 * Same contract as brooks_law_23_migrate_settings(): only ever fills an
 * empty slate, never modifies the source theme's settings. Hooked at
 * priority 9 so it runs before the 2.3 migration; whichever fills the
 * settings first wins, and the other returns without writing.
 */
function brooks_law_24_migrate_settings() {

	$current = get_option( 'theme_mods_' . get_option( 'stylesheet' ) );

	// Only migrate into an empty slate: never clobber settings that exist.
	if ( is_array( $current ) && count( $current ) > 1 ) {
		return;
	}

	$sources = array( 'theme_mods_brooks-law-24-editorial', 'theme_mods_brooks-law-23', 'theme_mods_brooks-law' );

	foreach ( $sources as $option_name ) {

		$source = get_option( $option_name );
		if ( ! is_array( $source ) || empty( $source ) ) {
			continue;
		}

		// Preserve sidebar/widget mapping WP may have already set for this slug.
		if ( is_array( $current ) && isset( $current['sidebars_widgets'] ) && ! isset( $source['sidebars_widgets'] ) ) {
			$source['sidebars_widgets'] = $current['sidebars_widgets'];
		}

		update_option( 'theme_mods_' . get_option( 'stylesheet' ), $source );
		return;
	}
}
add_action( 'after_switch_theme', 'brooks_law_24_migrate_settings', 9 );
