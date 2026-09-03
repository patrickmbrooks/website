<?php
/**
 * Brooks Law 3.1 — Text/Call contact toggle.
 *
 * One control, two sides: text on the left (the side that converts), call on
 * the right. A row of matter chips above the pill sets which prefilled message
 * the text side opens, so an incoming text already says what it is about.
 *
 * Renders in two places:
 *   - brooks_law_contact_toggle()  the full control (hero, contact section)
 *   - brooks_law_call_bar()        the phone-only sticky bar (footer, all pages)
 *
 * Text-click analytics need no work here: the Brooks Law Essentials plugin
 * already listens for sms: clicks and fires a text_click event.
 *
 * @package Brooks_Law
 * @since   3.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* -------------------------------------------------------------------------
 * Data helpers
 * ---------------------------------------------------------------------- */

/**
 * Build an sms: href with an optional prefilled body.
 *
 * The "?&body=" form is deliberate — iOS wants the ampersand, Android is
 * happy either way, so this one string works on both.
 *
 * @param string $number Display or dial-format number.
 * @param string $body   Message to prefill.
 * @return string Empty string if the number has no digits.
 */
function brooks_law_sms( $number, $body = '' ) {
	$tel = brooks_law_tel( $number );
	if ( '' === $tel ) {
		return '';
	}

	$body = trim( (string) $body );
	if ( '' === $body ) {
		return 'sms:' . $tel;
	}

	return 'sms:' . $tel . '?&body=' . rawurlencode( $body );
}

/**
 * The matter chips, in order, skipping any left blank in the Customizer.
 *
 * @return array[] Each: key, label, body.
 */
function brooks_law_matters() {
	$matters = array();

	for ( $i = 1; $i <= 3; $i++ ) {
		$label = trim( (string) brooks_law_get_option( "matter_{$i}_label" ) );
		if ( '' === $label ) {
			continue;
		}

		$matters[] = array(
			'key'   => 'm' . $i,
			'label' => $label,
			'body'  => (string) brooks_law_get_option( "matter_{$i}_body" ),
		);
	}

	return $matters;
}

/**
 * Build a mailto: href with an optional subject.
 *
 * @param string $email   Address.
 * @param string $subject Subject line.
 * @return string Empty string if the address is not usable.
 */
function brooks_law_mailto( $email, $subject = '' ) {
	$email = sanitize_email( (string) $email );
	if ( '' === $email || ! is_email( $email ) ) {
		return '';
	}

	$subject = trim( (string) $subject );
	if ( '' === $subject ) {
		return 'mailto:' . $email;
	}

	return 'mailto:' . $email . '?subject=' . rawurlencode( $subject );
}

/**
 * Does the page being viewed cover a matter with no consultation fee?
 *
 * Wrongful death and personal injury are handled without one; every other
 * civil matter carries the hourly consultation fee.
 *
 * @return bool
 */
function brooks_law_is_free_consult_page() {
	$slugs = apply_filters(
		'brooks_law_free_consult_slugs',
		array( 'wrongful-death-2', 'personal-injury' )
	);

	if ( ! is_singular() ) {
		return false;
	}

	return in_array( get_post_field( 'post_name', get_queried_object_id() ), (array) $slugs, true );
}

/**
 * The fine-print line for a civil toggle.
 *
 * @return string
 */
function brooks_law_civil_note() {
	return brooks_law_get_option(
		brooks_law_is_free_consult_page() ? 'civil_nofee_note' : 'civil_fee_note'
	);
}

/* -------------------------------------------------------------------------
 * Which matter a page is about
 * ---------------------------------------------------------------------- */

/**
 * Slug map for every published page as of the 3.1.1 build.
 *
 * Generated from the site's own page list rather than guessed, so the chip a
 * page opens on is a decision recorded here, not an accident of string
 * matching. Pages under 'none' get no toggle at all — a divorce or personal
 * injury page has no business offering a criminal-charge text.
 *
 * @return array criminal, traffic, none — each an array of slugs.
 */
function brooks_law_page_matter_map() {
	$map = array(
		// Criminal-defense pages — chip 1.
		'criminal' => array(
			'accessory-after-the-fact',
			'arlington-criminal-defense',
			'arraignment-shelby-county',
			'assault',
			'bartlett-criminal-defense',
			'beer-and-liquor-board-penalties',
			'burglary',
			'capias-bench-warrant-shelby-county',
			'civil-asset-forfeiture-defense',
			'cocaine',
			'collierville-criminal-defense',
			'contributing-to-delinquency-of-a-minor',
			'cordova-criminal-defense',
			'courts-we-serve',
			'criminal-appeal',
			'criminal-defense-2',
			'defensa-de-inmigracion',
			'disorderly-conduct',
			'domestic-violence',
			'drug-conspiracy',
			'drug-dui',
			'drug-offense',
			'drug-testing-methodology-defense',
			'dui',
			'dui-breath-blood-tests',
			'ecstasy',
			'electronic-monitoring',
			'electronic-surveillance-defense',
			'employment-criminal-defense',
			'expungement',
			'false-offense-report',
			'fayette-county-criminal-defense',
			'federal-criminal-defense',
			'felony-defense',
			'felony-dui',
			'fentanyl',
			'file-order-of-protection',
			'germantown-criminal-defense',
			'haywood-county-criminal-defense',
			'heroin',
			'how-does-bond-work-memphis',
			'how-long-does-a-criminal-case-take-memphis',
			'caught-shoplifting-wolfchase-what-happens',
			'civil-demand-letter-shoplifting-tennessee',
			'first-offense-theft-tennessee-diversion',
			'how-much-does-a-criminal-defense-lawyer-cost-memphis',
			'how-much-does-a-theft-lawyer-cost-memphis',
			'theft-of-merchandise-under-1000-memphis',
			'theft-of-property-under-1000-memphis',
			'will-i-go-to-jail-for-a-theft-charge-memphis',
			'how-much-does-a-criminal-lawyer-cost-bartlett-germantown-collierville',
			'how-much-does-a-domestic-assault-lawyer-cost-memphis',
			'how-much-does-a-drug-charge-lawyer-cost-memphis',
			'how-much-does-a-dui-lawyer-cost-memphis',
			'immigration-defense',
			'juvenile-defense',
			'lakeland-criminal-defense',
			'lauderdale-county-criminal-defense',
			'legislative-updates-tennessee-criminal-law',
			'marijuana',
			'methamphetamine',
			'millington-criminal-defense',
			'misdemeanor-defense',
			'misdemeanors',
			'order-of-protection',
			'patronizing-prostitution-defense',
			'preliminary-hearing-shelby-county',
			'prescription-pills',
			'probation-violation',
			'probation-violation-shelby-suburbs',
			'probation-violation-tri-county',
			'protest-civil-disobedience-defense',
			'public-intoxication',
			'robbery',
			'selling-alcohol-to-a-minor',
			'selling-alcohol-to-minor',
			'sexual-offenses',
			'shelby-county-drug-court',
			'taking-contraband-into-a-penal-facility',
			'theft',
			'tipton-county-criminal-defense',
			'underage-dui',
			'unlawful-weapon',
			'veterans-criminal-defense',
			'warrant',
			'what-happens-after-arrest-memphis',
			'white-collar-crime-defense',
		),

		// Traffic and CDL pages — chip 2.
		'traffic' => array(
			'cdl-defense',
			'cdl-defense-2',
			'failure-to-maintain-proper-lookout',
			'germantown-cdl-ticket',
			'habitual-motor-vehicle-offender',
			'how-much-does-a-cdl-ticket-lawyer-cost-memphis',
			'how-much-does-a-traffic-ticket-lawyer-cost-memphis',
			'leaving-scene-of-an-accident',
			'reckless-driving',
			'speeding-tickets',
			'suspended-license',
			'traffic',
			'waiving-court-costs-license-reinstatement',
		),

		// Civil pages — email Beth Brooks, or call the office.
		'civil' => array(
			'beth-brooks-profile',
			'business-litigation-2',
			'civil-litigation',
			'contested-divorce',
			'divorce',
			'high-income-divorce',
			'intellectual-property',
			'maritime-law',
			'music-artist-representation',
			'personal-injury',
			'uncontested-divorce',
			'wrongful-death-2',
		),

		// Profile, blog, and utility pages — no toggle.
		'none' => array(
			'blog',
			'contact-updated',
			'firm-profile-3',
			'immigration-resources',
			'patrick-brooks-profile',
			'privacy-policy',
			'recursos-legales-de-inmigracion',
			'resources-updated',
			'robert-brooks',
			'welcome',
		),
	);

	/**
	 * Filter the page-to-matter map.
	 *
	 * Add a slug to a bucket, or move one between buckets:
	 *
	 *     add_filter( 'brooks_law_page_matter_map', function ( $map ) {
	 *         $map['traffic'][] = 'new-cdl-page';
	 *         return $map;
	 *     } );
	 *
	 * @param array $map Slug buckets.
	 */
	return apply_filters( 'brooks_law_page_matter_map', $map );
}

/**
 * Keyword fallback for pages added after this build.
 *
 * Traffic is tested first so "cdl-ticket-lawyer-cost" does not get pulled into
 * criminal by the word "lawyer". A slug matching neither returns '' and shows
 * no toggle, which is the safe direction to be wrong in.
 *
 * @param string $slug Page slug.
 * @return string criminal, traffic, or ''.
 */
function brooks_law_guess_matter( $slug ) {
	$rules = apply_filters(
		'brooks_law_matter_keywords',
		array(
			'traffic'  => array( 'traffic', 'cdl', 'ticket', 'speeding', 'reckless-driving', 'suspended-license', 'revoked', 'motor-vehicle', 'license-reinstatement' ),
			'criminal' => array( 'criminal', 'defense', 'dui', 'drug', 'assault', 'theft', 'felony', 'misdemeanor', 'charge', 'arrest', 'warrant', 'probation', 'expungement', 'bond', 'offense', 'weapon', 'robbery', 'burglary', 'juvenile' ),
		)
	);

	foreach ( $rules as $matter => $keywords ) {
		foreach ( $keywords as $keyword ) {
			if ( false !== strpos( $slug, $keyword ) ) {
				return $matter;
			}
		}
	}

	return '';
}

/**
 * What the page currently being viewed is about.
 *
 * @return string criminal, traffic, or '' for pages that should not show it.
 */
function brooks_law_page_matter() {
	if ( ! is_singular() ) {
		return '';
	}

	$slug = get_post_field( 'post_name', get_queried_object_id() );
	if ( ! $slug ) {
		return '';
	}

	$map = brooks_law_page_matter_map();

	foreach ( array( 'none', 'civil', 'traffic', 'criminal' ) as $bucket ) {
		if ( ! empty( $map[ $bucket ] ) && in_array( $slug, (array) $map[ $bucket ], true ) ) {
			return ( 'none' === $bucket ) ? '' : $bucket;
		}
	}

	return brooks_law_guess_matter( $slug );
}

/**
 * Zero-based chip index for a matter name.
 *
 * @param string $matter criminal or traffic.
 * @return int
 */
function brooks_law_matter_index( $matter ) {
	return ( 'traffic' === $matter ) ? 1 : 0;
}

/**
 * Is the page being viewed a civil page?
 *
 * @return bool
 */
function brooks_law_is_civil_page() {
	return 'civil' === brooks_law_page_matter();
}

/**
 * Which chip a page opens on, as an index.
 *
 * Reads the same map the page toggle uses, so the sticky bar and the pill can
 * never disagree about what a page is about.
 *
 * @return int Zero-based index into brooks_law_matters().
 */
function brooks_law_default_matter_index() {
	$matters = brooks_law_matters();
	if ( count( $matters ) < 2 ) {
		return 0;
	}

	$matter = brooks_law_page_matter();
	if ( '' === $matter || 'civil' === $matter ) {
		return 0;
	}

	return brooks_law_matter_index( $matter );
}

/**
 * Numbers used by both the pill and the sticky bar.
 *
 * The text side falls back to the criminal line (the number that receives
 * texts); the call side falls back to the office.
 *
 * @return array text_display, text_number, call_display, call_number.
 */
function brooks_law_contact_numbers() {

	// An override typed into this section is also the number we dial, so the
	// +1 dial format from Firm Info is not silently attached to a different line.
	$text_display = trim( (string) brooks_law_get_option( 'toggle_text_number' ) );
	if ( '' !== $text_display ) {
		$text_number = $text_display;
	} else {
		$text_display = brooks_law_get_option( 'firm_cell' );
		$text_number  = trim( (string) brooks_law_get_option( 'firm_cell_link' ) );
		if ( '' === $text_number ) {
			$text_number = $text_display;
		}
	}

	$call_display = trim( (string) brooks_law_get_option( 'toggle_call_number' ) );
	if ( '' !== $call_display ) {
		$call_number = $call_display;
	} else {
		$call_display = brooks_law_get_option( 'firm_phone' );
		$call_number  = trim( (string) brooks_law_get_option( 'firm_phone_link' ) );
		if ( '' === $call_number ) {
			$call_number = $call_display;
		}
	}

	return array(
		'text_display' => $text_display,
		'text_number'  => $text_number,
		'call_display' => $call_display,
		'call_number'  => $call_number,
	);
}

/* -------------------------------------------------------------------------
 * The control
 * ---------------------------------------------------------------------- */

/**
 * Render the text/call pill with its matter chips.
 *
 * @param array $args {
 *     @type string $context  'hero' (on the dark hero) or 'light'. Default 'hero'.
 *     @type int    $matter   Zero-based chip to preselect. Default: auto by page.
 *     @type bool   $chips    Show the matter chips. Default true.
 *     @type bool   $fine     Show the fine-print line. Default true.
 * }
 */
function brooks_law_contact_toggle( $args = array() ) {
	if ( ! brooks_law_get_option( 'toggle_enable' ) ) {
		return;
	}

	$args = wp_parse_args(
		$args,
		array(
			'context'   => 'hero',
			// 'text' pairs the office line with the criminal text line.
			// 'email' pairs it with Beth Brooks for civil intake.
			'mode'      => 'text',
			'matter'    => null,
			'chips'     => true,
			'fine'      => true,
			'fine_text' => null,
		)
	);

	$is_email  = ( 'email' === $args['mode'] );
	$numbers   = brooks_law_contact_numbers();
	$call_href = brooks_law_tel( $numbers['call_number'] );

	if ( $is_email ) {
		$primary_href = brooks_law_mailto(
			brooks_law_get_option( 'firm_civil_email' ),
			brooks_law_get_option( 'civil_email_subject' )
		);
		$primary_label = brooks_law_get_option( 'toggle_email_label' );
		$primary_value = brooks_law_get_option( 'civil_contact_name' );
		$call_label    = brooks_law_get_option( 'toggle_call_label_civil' );
		$show_chips    = false;
		$matters       = array();
		$selected      = 0;
	} else {
		$matters      = brooks_law_matters();
		$selected     = ( null === $args['matter'] ) ? brooks_law_default_matter_index() : (int) $args['matter'];
		$selected     = isset( $matters[ $selected ] ) ? $selected : 0;
		$show_chips   = $args['chips'] && count( $matters ) > 1;
		$active_body  = isset( $matters[ $selected ]['body'] ) ? $matters[ $selected ]['body'] : '';
		$primary_href = ( '' === brooks_law_tel( $numbers['text_number'] ) )
			? ''
			: brooks_law_sms( $numbers['text_number'], $active_body );
		$primary_label = brooks_law_get_option( 'toggle_text_label' );
		$primary_value = $numbers['text_display'];
		$call_label    = brooks_law_get_option( 'toggle_call_label' );
	}

	if ( '' === $primary_href && '' === $call_href ) {
		return;
	}

	$fine = ( null === $args['fine_text'] )
		? ( $is_email ? brooks_law_civil_note() : brooks_law_get_option( 'toggle_fine_print' ) )
		: $args['fine_text'];

	$uid   = 'ct-' . wp_unique_id();
	$class = 'contact-toggle contact-toggle--' . sanitize_html_class( $args['context'] );
	if ( $is_email ) {
		$class .= ' contact-toggle--civil';
	}
	?>
	<div class="<?php echo esc_attr( $class ); ?>" data-contact-toggle>

		<?php if ( $show_chips ) : ?>
			<p class="ct-ask" id="<?php echo esc_attr( $uid ); ?>-ask">
				<?php echo esc_html( brooks_law_get_option( 'toggle_ask' ) ); ?>
			</p>
			<div class="ct-chips" role="group" aria-labelledby="<?php echo esc_attr( $uid ); ?>-ask">
				<?php foreach ( $matters as $index => $matter ) : ?>
					<button type="button" class="ct-chip"
						aria-pressed="<?php echo $index === $selected ? 'true' : 'false'; ?>"
						data-sms="<?php echo esc_url( brooks_law_sms( $numbers['text_number'], $matter['body'] ) ); ?>">
						<?php echo esc_html( $matter['label'] ); ?>
					</button>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>

		<div class="ct-rail">

			<?php if ( '' !== $primary_href ) : ?>
				<a class="ct-side <?php echo $is_email ? 'ct-email' : 'ct-text'; ?>"
					<?php echo $is_email ? '' : 'data-ct-text'; ?>
					href="<?php echo esc_url( $primary_href ); ?>"
					data-number="<?php echo esc_attr( $is_email ? brooks_law_get_option( 'firm_civil_email' ) : $numbers['text_display'] ); ?>">
					<span class="ct-icon" aria-hidden="true">
						<?php if ( $is_email ) : ?>
							<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" focusable="false">
								<rect x="2.5" y="4.5" width="19" height="15" rx="2"/><path d="m3 6 9 6.5L21 6"/>
							</svg>
						<?php else : ?>
							<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" focusable="false">
								<path d="M21 11.5a8.4 8.4 0 0 1-9 8.4 8.5 8.5 0 0 1-3.8-.9L3 21l1.9-5.1A8.4 8.4 0 0 1 12 3a8.4 8.4 0 0 1 9 8.5Z"/>
							</svg>
						<?php endif; ?>
					</span>
					<span class="ct-copy">
						<span class="ct-label" data-ct-text-label><?php echo esc_html( $primary_label ); ?></span>
						<span class="ct-value"><?php echo esc_html( $primary_value ); ?></span>
					</span>
				</a>
			<?php endif; ?>

			<?php if ( '' !== $call_href ) : ?>
				<a class="ct-side ct-call" href="tel:<?php echo esc_attr( $call_href ); ?>">
					<span class="ct-icon" aria-hidden="true">
						<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" focusable="false">
							<path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1 1 .3 1.9.6 2.8a2 2 0 0 1-.5 2.1L8.1 9.9a16 16 0 0 0 6 6l1.3-1.1a2 2 0 0 1 2.1-.5c.9.3 1.8.5 2.8.6a2 2 0 0 1 1.7 2Z"/>
						</svg>
					</span>
					<span class="ct-copy">
						<span class="ct-label"><?php echo esc_html( $call_label ); ?></span>
						<span class="ct-value"><?php echo esc_html( $numbers['call_display'] ); ?></span>
					</span>
				</a>
			<?php endif; ?>

		</div>

		<?php if ( $args['fine'] && '' !== trim( (string) $fine ) ) : ?>
			<p class="ct-fine"><?php echo esc_html( $fine ); ?></p>
		<?php endif; ?>

	</div>
	<?php
}

/**
 * Render the toggle in a page hero, but only where it belongs.
 *
 * Called from page.php and page-templates/practice-area.php. Prints nothing on
 * pages the map marks as neither criminal nor traffic, so civil, family, and
 * profile pages are left exactly as they were.
 */
function brooks_law_page_contact_toggle() {
	if ( ! brooks_law_get_option( 'toggle_on_pages' ) ) {
		return;
	}

	$matter = brooks_law_page_matter();
	if ( '' === $matter ) {
		return;
	}

	if ( 'civil' === $matter ) {
		// The consultation-fee line is the whole point on a civil page, so
		// unlike the criminal heroes this one keeps its fine print.
		brooks_law_contact_toggle(
			array(
				'context' => 'hero',
				'mode'    => 'email',
			)
		);
		return;
	}

	brooks_law_contact_toggle(
		array(
			'context' => 'hero',
			'matter'  => brooks_law_matter_index( $matter ),
			// The hero already carries a breadcrumb and an H1; the fine-print
			// line is one element too many in that stack.
			'fine'    => false,
		)
	);
}

/* -------------------------------------------------------------------------
 * Sticky bar
 * ---------------------------------------------------------------------- */

/**
 * Phone-only sticky bar: text on the left, call on the right.
 *
 * Same function name as the 3.0 call bar so footer.php needs no edit and a
 * rollback is a straight file swap. The text side carries the prefill for
 * whichever matter the current page is about.
 */
function brooks_law_call_bar() {
	if ( ! brooks_law_get_option( 'toggle_enable' ) ) {
		return;
	}

	/**
	 * Filter whether the sticky bar shows on this page.
	 *
	 * @param bool $show Default true.
	 */
	if ( ! apply_filters( 'brooks_law_show_call_bar', true ) ) {
		return;
	}

	$numbers = brooks_law_contact_numbers();
	$matter  = brooks_law_page_matter();
	$call_href = brooks_law_tel( $numbers['call_number'] );

	if ( 'civil' === $matter ) {
		// Texting the criminal line about a divorce helps nobody.
		$primary_href  = brooks_law_mailto(
			brooks_law_get_option( 'firm_civil_email' ),
			brooks_law_get_option( 'civil_email_subject' )
		);
		$primary_class = 'call-bar-email';
		$primary_label = brooks_law_get_option( 'callbar_email_label' );
		$primary_value = brooks_law_get_option( 'civil_contact_name' );
		$call_label    = brooks_law_get_option( 'callbar_civil_call_label' );
	} else {
		if ( '' === $matter ) {
			// A profile or utility page should not open a text that says
			// "I have a criminal charge". Neutral wording instead.
			$body = brooks_law_get_option( 'toggle_generic_body' );
		} else {
			$matters = brooks_law_matters();
			$index   = brooks_law_matter_index( $matter );
			$body    = isset( $matters[ $index ]['body'] ) ? $matters[ $index ]['body'] : '';
		}

		$primary_href  = brooks_law_sms( $numbers['text_number'], $body );
		$primary_class = 'call-bar-text';
		$primary_label = brooks_law_get_option( 'callbar_text_label' );
		$primary_value = $numbers['text_display'];
		$call_label    = brooks_law_get_option( 'callbar_call_label' );
	}

	if ( '' === $primary_href && '' === $call_href ) {
		return;
	}
	?>
	<div class="call-bar">
		<?php if ( '' !== $primary_href ) : ?>
			<a class="<?php echo esc_attr( $primary_class ); ?>" href="<?php echo esc_url( $primary_href ); ?>">
				<span class="call-bar-label"><?php echo esc_html( $primary_label ); ?></span>
				<span class="call-bar-value"><?php echo esc_html( $primary_value ); ?></span>
			</a>
		<?php endif; ?>

		<?php if ( '' !== $call_href ) : ?>
			<a class="call-bar-call" href="tel:<?php echo esc_attr( $call_href ); ?>">
				<span class="call-bar-label"><?php echo esc_html( $call_label ); ?></span>
				<span class="call-bar-value"><?php echo esc_html( $numbers['call_display'] ); ?></span>
			</a>
		<?php endif; ?>
	</div>
	<?php
}

/* -------------------------------------------------------------------------
 * Customizer
 * ---------------------------------------------------------------------- */

/**
 * Register the "Text / Call Buttons" section inside the Brooks Law panel.
 *
 * @param WP_Customize_Manager $wp_customize Manager.
 */
function brooks_law_toggle_customize( $wp_customize ) {
	$defaults = brooks_law_defaults();

	$wp_customize->add_section(
		'brooks_law_toggle',
		array(
			'title'       => __( 'Text / Call Buttons', 'brooks-law-30-pro' ),
			'panel'       => 'brooks_law',
			'description' => __( 'The split text/call button in the hero and the sticky bar at the bottom of every page on phones.', 'brooks-law-30-pro' ),
			'priority'    => 15,
		)
	);

	$field = function ( $key, $label, $type = 'text', $description = '' ) use ( $wp_customize, $defaults ) {
		$sanitize = ( 'textarea' === $type ) ? 'sanitize_textarea_field' : 'sanitize_text_field';
		if ( 'checkbox' === $type ) {
			$sanitize = 'brooks_law_sanitize_checkbox';
		}

		$wp_customize->add_setting(
			$key,
			array(
				'default'           => isset( $defaults[ $key ] ) ? $defaults[ $key ] : '',
				'sanitize_callback' => $sanitize,
			)
		);

		$wp_customize->add_control(
			$key,
			array(
				'label'       => $label,
				'description' => $description,
				'section'     => 'brooks_law_toggle',
				'type'        => $type,
			)
		);
	};

	$field( 'toggle_enable', __( 'Show the text/call buttons', 'brooks-law-30-pro' ), 'checkbox' );
	$field(
		'toggle_on_pages',
		__( 'Also show them on criminal, traffic, and CDL pages', 'brooks-law-30-pro' ),
		'checkbox',
		__( 'Adds the buttons under the heading on those pages. Civil, family, and profile pages are never affected.', 'brooks-law-30-pro' )
	);

	$field(
		'toggle_text_number',
		__( 'Text number (display)', 'brooks-law-30-pro' ),
		'text',
		__( 'Leave blank to use the criminal line from Firm Info. This must be a line that can receive texts.', 'brooks-law-30-pro' )
	);
	$field(
		'toggle_call_number',
		__( 'Call number (display)', 'brooks-law-30-pro' ),
		'text',
		__( 'Leave blank to use the office phone from Firm Info.', 'brooks-law-30-pro' )
	);

	$field( 'toggle_ask', __( 'Question above the chips', 'brooks-law-30-pro' ) );
	$field( 'toggle_call_label', __( 'Call button label (criminal and traffic pages)', 'brooks-law-30-pro' ) );
	$field( 'toggle_text_label', __( 'Text button label', 'brooks-law-30-pro' ) );

	for ( $i = 1; $i <= 3; $i++ ) {
		$field(
			"matter_{$i}_label",
			/* translators: %d: chip number. */
			sprintf( __( 'Chip %d — label', 'brooks-law-30-pro' ), $i ),
			'text',
			1 === $i ? __( 'Leave a chip blank to hide it. Chip 1 is the one a page opens on unless the page is about another matter.', 'brooks-law-30-pro' ) : ''
		);
		$field(
			"matter_{$i}_body",
			/* translators: %d: chip number. */
			sprintf( __( 'Chip %d — prefilled message', 'brooks-law-30-pro' ), $i ),
			'textarea',
			1 === $i ? __( 'What the text message already says when it opens on their phone.', 'brooks-law-30-pro' ) : ''
		);
	}

	$field( 'toggle_fine_print', __( 'Line under the buttons (criminal and traffic)', 'brooks-law-30-pro' ), 'textarea' );

	/* ---- Civil pages ---- */
	$field(
		'firm_civil_email',
		__( 'Civil email address', 'brooks-law-30-pro' ),
		'text',
		__( 'Shown on civil pages in place of the text button. Civil calls always go to the office number.', 'brooks-law-30-pro' )
	);
	$field( 'civil_contact_name', __( 'Name shown on the email button', 'brooks-law-30-pro' ) );
	$field( 'civil_email_subject', __( 'Subject line the email opens with', 'brooks-law-30-pro' ) );
	$field( 'toggle_email_label', __( 'Email button label', 'brooks-law-30-pro' ) );
	$field( 'toggle_call_label_civil', __( 'Call button label (civil pages)', 'brooks-law-30-pro' ) );
	$field(
		'civil_fee_note',
		__( 'Consultation-fee line (civil pages)', 'brooks-law-30-pro' ),
		'textarea',
		__( 'Shown under the buttons on every civil page except the no-fee ones below.', 'brooks-law-30-pro' )
	);
	$field(
		'civil_nofee_note',
		__( 'Line for matters with no consultation fee', 'brooks-law-30-pro' ),
		'textarea',
		__( 'Used on the wrongful death and personal injury pages.', 'brooks-law-30-pro' )
	);
	$field( 'callbar_email_label', __( 'Sticky bar — email label', 'brooks-law-30-pro' ) );
	$field( 'callbar_civil_call_label', __( 'Sticky bar — call label on civil pages', 'brooks-law-30-pro' ) );
	$field(
		'toggle_generic_body',
		__( 'Prefilled message on other pages', 'brooks-law-30-pro' ),
		'textarea',
		__( 'Used by the sticky bar on pages that are not about a criminal or traffic matter.', 'brooks-law-30-pro' )
	);
	$field( 'callbar_text_label', __( 'Sticky bar — text label', 'brooks-law-30-pro' ) );
	$field( 'callbar_call_label', __( 'Sticky bar — call label', 'brooks-law-30-pro' ) );
}
add_action( 'customize_register', 'brooks_law_toggle_customize', 20 );
