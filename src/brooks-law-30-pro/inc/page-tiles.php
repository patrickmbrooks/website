<?php
/**
 * Per-page action tiles — v4.1.
 *
 * Most visitors land on an interior page from a search result and never see
 * the homepage, so the Action Center's situation-first routing is extended to
 * the pages themselves: a compact row of curated tiles directly under each
 * page hero, chosen per topic cluster.
 *
 * Curation is a recorded decision, the same way the contact-toggle matter map
 * works: a tile library, named sets, and an explicit slug map. Pages not in
 * the map fall back by matter (criminal, traffic), and only personal injury
 * and wrongful death participate on the civil side. A tile pointing at the
 * page it sits on is dropped automatically.
 *
 * @package Brooks_Law
 * @since   4.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Every tile the row can use, keyed by id.
 *
 * All URLs are site-relative and resolved through home_url() at render, so
 * the markup only ever contains absolute links.
 *
 * @return array[] id => title, sub, icon, url, hot.
 */
function brooks_law_tile_library() {
	$tiles = array(
		'arrest'        => array( 'title' => 'Someone was just arrested', 'sub' => 'The first 72 hours', 'icon' => 'handcuffs', 'url' => '/what-happens-after-arrest-memphis/', 'hot' => true ),
		'warrant'       => array( 'title' => 'There\'s a warrant out', 'sub' => 'Missed court, capias recall', 'icon' => 'document', 'url' => '/capias-bench-warrant-shelby-county/', 'hot' => true ),
		'bond'          => array( 'title' => 'Trying to make bond', 'sub' => 'Before you pay a bondsman', 'icon' => 'prisonbars', 'url' => '/how-does-bond-work-memphis/', 'hot' => true ),
		'howlong'       => array( 'title' => 'How long will this take?', 'sub' => 'The Shelby County timeline', 'icon' => 'courthouse', 'url' => '/how-long-does-a-criminal-case-take-memphis/', 'hot' => false ),
		'cost'          => array( 'title' => 'What a defense lawyer costs', 'sub' => 'Flat fees, published', 'icon' => 'scales', 'url' => '/how-much-does-a-criminal-defense-lawyer-cost-memphis/', 'hot' => false ),
		'cost_dui'      => array( 'title' => 'What a DUI lawyer costs', 'sub' => 'Flat fees, published', 'icon' => 'scales', 'url' => '/how-much-does-a-dui-lawyer-cost-memphis/', 'hot' => false ),
		'cost_drug'     => array( 'title' => 'What a drug lawyer costs', 'sub' => 'Flat fees, published', 'icon' => 'scales', 'url' => '/how-much-does-a-drug-charge-lawyer-cost-memphis/', 'hot' => false ),
		'cost_theft'    => array( 'title' => 'What a theft lawyer costs', 'sub' => 'From $750, published', 'icon' => 'scales', 'url' => '/how-much-does-a-theft-lawyer-cost-memphis/', 'hot' => false ),
		'cost_domestic' => array( 'title' => 'What a domestic case costs', 'sub' => 'Flat fees, published', 'icon' => 'scales', 'url' => '/how-much-does-a-domestic-assault-lawyer-cost-memphis/', 'hot' => false ),
		'cost_traffic'  => array( 'title' => 'What a ticket lawyer costs', 'sub' => 'Flat fees, published', 'icon' => 'scales', 'url' => '/how-much-does-a-traffic-ticket-lawyer-cost-memphis/', 'hot' => false ),
		'cost_cdl'      => array( 'title' => 'What a CDL lawyer costs', 'sub' => 'Flat fees, published', 'icon' => 'scales', 'url' => '/how-much-does-a-cdl-ticket-lawyer-cost-memphis/', 'hot' => false ),
		'expunge'       => array( 'title' => 'Clearing your record', 'sub' => 'Diversion and expungement', 'icon' => 'document', 'url' => '/expungement/', 'hot' => false ),
		'drugcourt'     => array( 'title' => 'Shelby County Drug Court', 'sub' => 'Treatment instead of jail', 'icon' => 'pills', 'url' => '/shelby-county-drug-court/', 'hot' => false ),
		'dui_tests'     => array( 'title' => 'Breath and blood tests', 'sub' => 'What they prove — and don\'t', 'icon' => 'stoplight', 'url' => '/dui-breath-blood-tests/', 'hot' => false ),
		'suspended'     => array( 'title' => 'Suspended or revoked license', 'sub' => 'The path back to valid', 'icon' => 'stoplight', 'url' => '/suspended-license/', 'hot' => false ),
		'cdl'           => array( 'title' => 'CDL holders', 'sub' => 'Protecting your livelihood', 'icon' => 'semi', 'url' => '/cdl-defense/', 'hot' => false ),
		'traffic'       => array( 'title' => 'Traffic charges', 'sub' => 'We appear for you', 'icon' => 'semi', 'url' => '/traffic/', 'hot' => false ),
		'theft'         => array( 'title' => 'Theft and shoplifting', 'sub' => 'Property and merchandise', 'icon' => 'scales', 'url' => '/theft/', 'hot' => false ),
		'theft_jail'    => array( 'title' => 'Will I go to jail?', 'sub' => 'First-offense reality', 'icon' => 'prisonbars', 'url' => '/will-i-go-to-jail-for-a-theft-charge-memphis/', 'hot' => false ),
		'theft_record'  => array( 'title' => 'Keeping it off your record', 'sub' => 'Diversion and retirement', 'icon' => 'document', 'url' => '/first-offense-theft-tennessee-diversion/', 'hot' => false ),
		'domestic'      => array( 'title' => 'Domestic assault charges', 'sub' => 'Charges and consequences', 'icon' => 'shield', 'url' => '/domestic-violence/', 'hot' => false ),
		'op'            => array( 'title' => 'Orders of protection', 'sub' => 'Defending and filing', 'icon' => 'shield', 'url' => '/order-of-protection/', 'hot' => false ),
		'assault'       => array( 'title' => 'Assault charges', 'sub' => 'Simple to aggravated', 'icon' => 'shield', 'url' => '/assault/', 'hot' => false ),
		'burglary'      => array( 'title' => 'Burglary', 'sub' => 'Home and building entry', 'icon' => 'courthouse', 'url' => '/burglary/', 'hot' => false ),
		'weapon'        => array( 'title' => 'Weapon charges', 'sub' => 'Possession and carry', 'icon' => 'shield', 'url' => '/unlawful-weapon/', 'hot' => false ),
		'pi'            => array( 'title' => 'Personal injury', 'sub' => 'No consultation fee', 'icon' => 'shield', 'url' => '/personal-injury/', 'hot' => false ),
		'wd'            => array( 'title' => 'Wrongful death', 'sub' => 'No consultation fee', 'icon' => 'courthouse', 'url' => '/wrongful-death-2/', 'hot' => false ),
		'civillit'      => array( 'title' => 'Civil litigation', 'sub' => 'Disputes and lawsuits', 'icon' => 'scales', 'url' => '/civil-litigation/', 'hot' => false ),
		'contact'       => array( 'title' => 'Talk to the firm', 'sub' => 'Office and directions', 'icon' => 'pin', 'url' => '/contact-updated/', 'hot' => false ),
	);

	/**
	 * Filter the tile library.
	 *
	 * @param array $tiles id => tile.
	 */
	return apply_filters( 'brooks_law_tile_library', $tiles );
}

/**
 * Named tile sets. Five ids each, so four survive when one is dropped as a
 * self-link. Only the first four render.
 *
 * @return array[] set => tile ids.
 */
function brooks_law_tile_sets() {
	return apply_filters(
		'brooks_law_tile_sets',
		array(
			'criminal' => array( 'arrest', 'warrant', 'bond', 'cost', 'expunge' ),
			'process'  => array( 'arrest', 'warrant', 'bond', 'howlong', 'cost' ),
			'dui'      => array( 'arrest', 'bond', 'cost_dui', 'dui_tests', 'expunge' ),
			'drug'     => array( 'arrest', 'drugcourt', 'cost_drug', 'bond', 'expunge' ),
			'theft'    => array( 'cost_theft', 'theft_jail', 'theft_record', 'theft', 'arrest' ),
			'domestic' => array( 'arrest', 'op', 'cost_domestic', 'bond', 'weapon' ),
			'op'       => array( 'domestic', 'assault', 'theft', 'burglary', 'weapon' ),
			'traffic'  => array( 'cost_traffic', 'suspended', 'cdl', 'warrant', 'bond' ),
			'cdl'      => array( 'cost_cdl', 'suspended', 'traffic', 'warrant', 'bond' ),
			'injury'   => array( 'wd', 'pi', 'civillit', 'contact' ),
		)
	);
}

/**
 * Which set each page uses. Pages not listed fall back by matter:
 * criminal pages get 'criminal', traffic pages get 'traffic', and everything
 * else — including all civil pages except the two listed here — gets nothing.
 *
 * @return array slug => set.
 */
function brooks_law_tile_map() {
	return apply_filters(
		'brooks_law_tile_map',
		array(
			// DUI cluster.
			'dui'                        => 'dui',
			'felony-dui'                 => 'dui',
			'underage-dui'               => 'dui',
			'drug-dui'                   => 'dui',
			'dui-breath-blood-tests'     => 'dui',
			'how-much-does-a-dui-lawyer-cost-memphis' => 'dui',

			// Drug cluster.
			'drug-offense'               => 'drug',
			'cocaine'                    => 'drug',
			'heroin'                     => 'drug',
			'marijuana'                  => 'drug',
			'methamphetamine'            => 'drug',
			'fentanyl'                   => 'drug',
			'ecstasy'                    => 'drug',
			'prescription-pills'         => 'drug',
			'drug-conspiracy'            => 'drug',
			'drug-testing-methodology-defense' => 'drug',
			'shelby-county-drug-court'   => 'drug',
			'how-much-does-a-drug-charge-lawyer-cost-memphis' => 'drug',

			// Theft cluster.
			'theft'                      => 'theft',
			'robbery'                    => 'theft',
			'burglary'                   => 'theft',
			'white-collar-crime-defense' => 'theft',
			'theft-of-property-under-1000-memphis'    => 'theft',
			'theft-of-merchandise-under-1000-memphis' => 'theft',
			'how-much-does-a-theft-lawyer-cost-memphis' => 'theft',

			// Domestic and protection orders.
			'domestic-violence'          => 'domestic',
			'how-much-does-a-domestic-assault-lawyer-cost-memphis' => 'domestic',
			'order-of-protection'        => 'op',
			'file-order-of-protection'   => 'op',

			// Process pages route to each other.
			'what-happens-after-arrest-memphis'  => 'process',
			'capias-bench-warrant-shelby-county' => 'process',
			'how-does-bond-work-memphis'         => 'process',
			'arraignment-shelby-county'          => 'process',
			'preliminary-hearing-shelby-county'  => 'process',
			'how-long-does-a-criminal-case-take-memphis' => 'process',
			'electronic-monitoring'              => 'process',

			// CDL pages.
			'cdl-defense'                => 'cdl',
			'cdl-defense-2'              => 'cdl',
			'germantown-cdl-ticket'      => 'cdl',
			'how-much-does-a-cdl-ticket-lawyer-cost-memphis' => 'cdl',

			// The two civil pages that participate.
			'personal-injury'            => 'injury',
			'wrongful-death-2'           => 'injury',
		)
	);
}

/**
 * The tiles for the page being viewed, resolved and self-links removed.
 *
 * @return array[] Up to four tiles: title, sub, icon, url, hot.
 */
function brooks_law_page_action_tiles() {
	if ( ! is_singular( 'page' ) ) {
		return array();
	}

	$slug = get_post_field( 'post_name', get_queried_object_id() );
	if ( ! $slug ) {
		return array();
	}

	$map = brooks_law_tile_map();

	if ( isset( $map[ $slug ] ) ) {
		$set = $map[ $slug ];
	} else {
		$matter = function_exists( 'brooks_law_page_matter' ) ? brooks_law_page_matter() : '';
		if ( 'criminal' === $matter ) {
			$set = 'criminal';
		} elseif ( 'traffic' === $matter ) {
			$set = 'traffic';
		} else {
			return array();
		}
	}

	$sets    = brooks_law_tile_sets();
	$library = brooks_law_tile_library();
	$out     = array();

	foreach ( isset( $sets[ $set ] ) ? $sets[ $set ] : array() as $id ) {
		if ( ! isset( $library[ $id ] ) ) {
			continue;
		}

		$tile = $library[ $id ];

		// A tile pointing at the page it sits on helps nobody.
		if ( trim( (string) $tile['url'], '/' ) === $slug ) {
			continue;
		}

		$tile['icon'] = brooks_law_sanitize_bubble_icon( $tile['icon'] );
		if ( 0 === strpos( $tile['url'], '/' ) ) {
			$tile['url'] = home_url( $tile['url'] );
		}

		$out[] = $tile;
		if ( count( $out ) >= 4 ) {
			break;
		}
	}

	/**
	 * Filter the resolved tiles for a page.
	 *
	 * @param array  $out  Tiles.
	 * @param string $slug Page slug.
	 */
	return apply_filters( 'brooks_law_page_action_tiles', $out, $slug );
}

/**
 * Render the compact tile row under a page hero.
 *
 * Prints nothing when the switch is off, the page has no set, or fewer than
 * two tiles survive — a one-tile row looks like a mistake.
 */
function brooks_law_page_action_row() {
	if ( ! get_theme_mod( 'acp_enable', true ) ) {
		return;
	}

	$tiles = brooks_law_page_action_tiles();
	if ( count( $tiles ) < 2 ) {
		return;
	}

	$heading = get_theme_mod( 'acp_heading', 'Helpful right now' );
	$icons   = brooks_law_sa_icons();
	$uid     = 'acp-' . wp_unique_id();
	?>
	<section class="ac-row band-limestone" aria-labelledby="<?php echo esc_attr( $uid ); ?>">
		<div class="wrap">
			<h2 class="ac-row-heading" id="<?php echo esc_attr( $uid ); ?>"><?php echo esc_html( $heading ); ?></h2>
			<ul class="ac-grid ac-grid--row">
				<?php foreach ( $tiles as $tile ) : ?>
					<li class="ac-item">
						<a class="ac-tile<?php echo $tile['hot'] ? ' ac-tile--hot' : ''; ?>" href="<?php echo esc_url( $tile['url'] ); ?>">
							<span class="ac-disc" aria-hidden="true">
								<svg class="ac-icon" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><?php echo isset( $icons[ $tile['icon'] ]['svg'] ) ? $icons[ $tile['icon'] ]['svg'] : ''; // phpcs:ignore WordPress.Security.EscapeOutput -- static SVG from brooks_law_sa_icons(). ?></svg>
							</span>
							<span class="ac-text">
								<?php if ( $tile['hot'] && '' !== trim( (string) get_theme_mod( 'ac_hot_tag', 'Time-sensitive' ) ) ) : ?>
									<span class="ac-tag"><?php echo esc_html( get_theme_mod( 'ac_hot_tag', 'Time-sensitive' ) ); ?></span>
								<?php endif; ?>
								<span class="ac-title"><?php echo esc_html( $tile['title'] ); ?></span>
								<?php if ( '' !== trim( (string) $tile['sub'] ) ) : ?>
									<span class="ac-sub"><?php echo esc_html( $tile['sub'] ); ?></span>
								<?php endif; ?>
							</span>
						</a>
					</li>
				<?php endforeach; ?>
			</ul>
		</div>
	</section>
	<?php
}

/**
 * Customizer: the two page-row controls, filed with the Action Center.
 *
 * @param WP_Customize_Manager $wp_customize Manager.
 */
function brooks_law_page_tiles_customize( $wp_customize ) {
	$wp_customize->add_setting( 'acp_enable', array(
		'default'           => true,
		'sanitize_callback' => 'brooks_law_sanitize_checkbox',
	) );
	$wp_customize->add_control( 'acp_enable', array(
		'section'     => 'brooks_law_action_center',
		'label'       => __( 'Show tile rows on interior pages', 'brooks-law-30-pro' ),
		'description' => __( 'A compact, page-relevant row under the hero on criminal, traffic, personal injury, and wrongful death pages.', 'brooks-law-30-pro' ),
		'type'        => 'checkbox',
	) );

	$wp_customize->add_setting( 'acp_heading', array(
		'default'           => 'Helpful right now',
		'sanitize_callback' => 'sanitize_text_field',
	) );
	$wp_customize->add_control( 'acp_heading', array(
		'section' => 'brooks_law_action_center',
		'label'   => __( 'Interior row heading', 'brooks-law-30-pro' ),
		'type'    => 'text',
	) );
}
add_action( 'customize_register', 'brooks_law_page_tiles_customize', 25 );
