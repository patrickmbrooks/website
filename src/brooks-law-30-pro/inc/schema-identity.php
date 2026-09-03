<?php
/**
 * Firm identity — one source of truth for the machine-readable copy.
 *
 * Everything the structured-data graph says about the firm is derived here,
 * from the same Customizer fields the visible page renders. Before this file
 * existed the graph carried its own hardcoded locality, region, opening hours
 * and attorney names, so editing the office hours changed what visitors read
 * and left what Google indexed saying something else — with nothing anywhere
 * to indicate the two had diverged.
 *
 * The parsers below are deliberately strict. Where a human-entered string
 * cannot be understood with confidence, the corresponding structured claim is
 * omitted rather than guessed: a missing `openingHoursSpecification` costs a
 * little rich-result eligibility, while a wrong one is a published
 * misstatement about when a law office is open.
 *
 * @package Brooks_Law
 * @since   5.3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Resolve a stored link to an absolute URL.
 *
 * The Customizer accepts either a site-relative path or a full URL for
 * practice-area links and attorney profiles. Wrapping the stored value in
 * home_url() unconditionally — which the graph used to do — turns an absolute
 * URL into "https://example.com/https://other.example/". The rendered page
 * escaped the same value correctly, so only the machine-readable copy was
 * wrong, which is the kind of defect that survives for years.
 *
 * @param string $value Stored path or URL.
 * @return string Absolute URL, or '' when the value is unusable.
 */
function brooks_law_resolve_url( $value ) {
	$value = trim( (string) $value );

	if ( '' === $value ) {
		return '';
	}

	// Already absolute: return as-is.
	if ( preg_match( '#^https?://#i', $value ) ) {
		return esc_url_raw( $value );
	}

	// Protocol-relative: adopt the site's own scheme rather than trusting it.
	if ( 0 === strpos( $value, '//' ) ) {
		return esc_url_raw( set_url_scheme( 'https:' . $value ) );
	}

	return esc_url_raw( home_url( '/' . ltrim( $value, '/' ) ) );
}

/**
 * Split "Memphis, Tennessee 38104" into its structured parts.
 *
 * Understands "City, Region ZIP", "City, Region", and a bare "City". Region
 * is normalised to the two-letter code schema.org expects when the full state
 * name is spelled out.
 *
 * @param string $line City/state/ZIP line as entered.
 * @return array{locality:string,region:string,postal:string}
 */
function brooks_law_parse_locality( $line ) {
	$out = array(
		'locality' => '',
		'region'   => '',
		'postal'   => '',
	);

	$line = trim( (string) $line );
	if ( '' === $line ) {
		return $out;
	}

	// Postal code first, so it does not confuse the region match.
	if ( preg_match( '/\b(\d{5})(?:-\d{4})?\b/', $line, $zip ) ) {
		$out['postal'] = $zip[1];
		$line          = trim( str_replace( $zip[0], '', $line ) );
	}

	$line  = trim( $line, " \t\n\r\0\x0B," );
	$parts = array_values( array_filter( array_map( 'trim', explode( ',', $line ) ) ) );

	if ( empty( $parts ) ) {
		return $out;
	}

	$out['locality'] = $parts[0];

	if ( isset( $parts[1] ) && '' !== $parts[1] ) {
		$out['region'] = brooks_law_state_code( $parts[1] );
	}

	return $out;
}

/**
 * Normalise a US state name to its two-letter code.
 *
 * Anything already two letters, or not recognised, is returned untouched —
 * schema.org accepts a spelled-out region, so an unknown value is passed
 * through rather than dropped.
 *
 * @param string $state State name or code.
 * @return string
 */
function brooks_law_state_code( $state ) {
	$state = trim( (string) $state );

	if ( 2 === strlen( $state ) ) {
		return strtoupper( $state );
	}

	$map = array(
		'alabama' => 'AL', 'alaska' => 'AK', 'arizona' => 'AZ', 'arkansas' => 'AR',
		'california' => 'CA', 'colorado' => 'CO', 'connecticut' => 'CT', 'delaware' => 'DE',
		'district of columbia' => 'DC', 'florida' => 'FL', 'georgia' => 'GA', 'hawaii' => 'HI',
		'idaho' => 'ID', 'illinois' => 'IL', 'indiana' => 'IN', 'iowa' => 'IA',
		'kansas' => 'KS', 'kentucky' => 'KY', 'louisiana' => 'LA', 'maine' => 'ME',
		'maryland' => 'MD', 'massachusetts' => 'MA', 'michigan' => 'MI', 'minnesota' => 'MN',
		'mississippi' => 'MS', 'missouri' => 'MO', 'montana' => 'MT', 'nebraska' => 'NE',
		'nevada' => 'NV', 'new hampshire' => 'NH', 'new jersey' => 'NJ', 'new mexico' => 'NM',
		'new york' => 'NY', 'north carolina' => 'NC', 'north dakota' => 'ND', 'ohio' => 'OH',
		'oklahoma' => 'OK', 'oregon' => 'OR', 'pennsylvania' => 'PA', 'rhode island' => 'RI',
		'south carolina' => 'SC', 'south dakota' => 'SD', 'tennessee' => 'TN', 'texas' => 'TX',
		'utah' => 'UT', 'vermont' => 'VT', 'virginia' => 'VA', 'washington' => 'WA',
		'west virginia' => 'WV', 'wisconsin' => 'WI', 'wyoming' => 'WY',
	);

	$key = strtolower( $state );

	return isset( $map[ $key ] ) ? $map[ $key ] : $state;
}

/**
 * Day names, in schema.org order, keyed by the lowercase full name.
 *
 * @return array<string,string>
 */
function brooks_law_days() {
	return array(
		'monday'    => 'Monday',
		'tuesday'   => 'Tuesday',
		'wednesday' => 'Wednesday',
		'thursday'  => 'Thursday',
		'friday'    => 'Friday',
		'saturday'  => 'Saturday',
		'sunday'    => 'Sunday',
	);
}

/**
 * Parse a 12- or 24-hour clock time into "HH:MM".
 *
 * @param string $raw Time as written, e.g. "8:00 a.m." or "17:30".
 * @return string Empty string when it cannot be read with confidence.
 */
function brooks_law_parse_time( $raw ) {
	$raw = strtolower( trim( (string) $raw ) );
	$raw = str_replace( array( '.', ' ' ), '', $raw );

	if ( ! preg_match( '/^(\d{1,2})(?::(\d{2}))?(am|pm)?$/', $raw, $m ) ) {
		return '';
	}

	$hour   = (int) $m[1];
	$minute = isset( $m[2] ) && '' !== $m[2] ? (int) $m[2] : 0;
	$suffix = isset( $m[3] ) ? $m[3] : '';

	if ( 'pm' === $suffix && $hour < 12 ) {
		$hour += 12;
	} elseif ( 'am' === $suffix && 12 === $hour ) {
		$hour = 0;
	}

	if ( $hour > 23 || $minute > 59 ) {
		return '';
	}

	return sprintf( '%02d:%02d', $hour, $minute );
}

/**
 * Derive an openingHoursSpecification from the human-readable hours line.
 *
 * Handles the shapes an office actually writes:
 *
 *   Monday – Friday, 8:00 a.m. – 5:30 p.m.
 *   Monday to Friday, 8am - 5:30pm
 *   Mon-Fri 9:00-17:00
 *
 * Returns an empty array when the line cannot be parsed, so the graph simply
 * omits the claim instead of publishing invented hours.
 *
 * @param string $line Hours line as entered in the Customizer.
 * @return array[] Zero or one OpeningHoursSpecification entries.
 */
function brooks_law_parse_hours( $line ) {
	$line = trim( (string) $line );

	if ( '' === $line ) {
		return array();
	}

	// Normalise the dash family and the various "to" spellings to one token.
	$normal = str_replace( array( '–', '—', '−', ' to ', ' through ' ), array( '-', '-', '-', '-', '-' ), $line );
	$normal = strtolower( $normal );

	$days     = brooks_law_days();
	$day_keys = array_keys( $days );

	// Day range: two day names either side of a dash.
	if ( ! preg_match( '/([a-z]{3,9})\s*-\s*([a-z]{3,9})/', $normal, $day_match ) ) {
		return array();
	}

	$from = brooks_law_match_day( $day_match[1], $day_keys );
	$to   = brooks_law_match_day( $day_match[2], $day_keys );

	if ( '' === $from || '' === $to ) {
		return array();
	}

	$start = array_search( $from, $day_keys, true );
	$end   = array_search( $to, $day_keys, true );

	if ( false === $start || false === $end || $end < $start ) {
		return array();
	}

	$day_names = array();
	for ( $i = $start; $i <= $end; $i++ ) {
		$day_names[] = $days[ $day_keys[ $i ] ];
	}

	// Time range: two clock times either side of a dash.
	if ( ! preg_match( '/(\d{1,2}(?::\d{2})?\s*(?:a\.?m\.?|p\.?m\.?)?)\s*-\s*(\d{1,2}(?::\d{2})?\s*(?:a\.?m\.?|p\.?m\.?)?)/', $normal, $time_match ) ) {
		return array();
	}

	$opens  = brooks_law_parse_time( $time_match[1] );
	$closes = brooks_law_parse_time( $time_match[2] );

	if ( '' === $opens || '' === $closes || $opens === $closes ) {
		return array();
	}

	return array(
		array(
			'@type'     => 'OpeningHoursSpecification',
			'dayOfWeek' => $day_names,
			'opens'     => $opens,
			'closes'    => $closes,
		),
	);
}

/**
 * Match an abbreviated or full day name against the known list.
 *
 * @param string   $needle   Candidate, lowercase.
 * @param string[] $day_keys Lowercase full day names.
 * @return string Matched key, or '' when ambiguous or unknown.
 */
function brooks_law_match_day( $needle, $day_keys ) {
	$needle = trim( (string) $needle );

	if ( strlen( $needle ) < 3 ) {
		return '';
	}

	foreach ( $day_keys as $key ) {
		if ( 0 === strpos( $key, $needle ) || 0 === strpos( $needle, substr( $key, 0, 3 ) ) ) {
			return $key;
		}
	}

	return '';
}

/**
 * The firm's attorneys, as configured.
 *
 * Reads the attorney_N_* fields, falling back to the legacy
 * schema_patrick_* / schema_beth_* keys so a site that filled those in before
 * 5.3 keeps its data without a migration step.
 *
 * @return array[] Each: name, title, alumni, url, knows[].
 */
function brooks_law_attorneys() {
	$legacy = array(
		1 => array( 'schema_patrick_url', 'schema_patrick_alumni' ),
		2 => array( 'schema_beth_url', 'schema_beth_alumni' ),
	);

	$people = array();

	for ( $i = 1; $i <= 2; $i++ ) {
		$name = trim( (string) brooks_law_get_option( "attorney_{$i}_name" ) );

		if ( '' === $name ) {
			continue;
		}

		$url    = trim( (string) brooks_law_get_option( "attorney_{$i}_url", '' ) );
		$alumni = trim( (string) brooks_law_get_option( "attorney_{$i}_alumni", '' ) );

		if ( '' === $url && isset( $legacy[ $i ] ) ) {
			$url = trim( (string) brooks_law_get_option( $legacy[ $i ][0], '' ) );
		}
		if ( '' === $alumni && isset( $legacy[ $i ] ) ) {
			$alumni = trim( (string) brooks_law_get_option( $legacy[ $i ][1], '' ) );
		}

		$knows_raw = (string) brooks_law_get_option( "attorney_{$i}_knows" );
		$knows     = array_values( array_filter( array_map( 'trim', explode( ',', $knows_raw ) ) ) );

		$people[] = array(
			'slug'   => sanitize_title( $name ),
			'name'   => $name,
			'title'  => trim( (string) brooks_law_get_option( "attorney_{$i}_title" ) ),
			'url'    => $url,
			'alumni' => $alumni,
			'knows'  => $knows,
		);
	}

	/**
	 * Filter the attorney list used by the structured-data graph.
	 *
	 * @since 5.3.0
	 *
	 * @param array[] $people Attorneys.
	 */
	return apply_filters( 'brooks_law_attorneys', $people );
}

/**
 * Customizer fields for the identity values the graph reads.
 *
 * Filed under SEO & Schema next to the other structured-data controls, so the
 * relationship between "what the page says" and "what search engines read" is
 * visible in one place.
 *
 * @param WP_Customize_Manager $wp_customize Manager.
 */
function brooks_law_identity_customize( $wp_customize ) {
	$defaults = brooks_law_defaults();

	$fields = array(
		'attorney_1_name'   => __( 'Attorney 1 — full name', 'brooks-law-30-pro' ),
		'attorney_1_title'  => __( 'Attorney 1 — job title', 'brooks-law-30-pro' ),
		'attorney_1_url'    => __( 'Attorney 1 — profile page path', 'brooks-law-30-pro' ),
		'attorney_1_alumni' => __( 'Attorney 1 — law school', 'brooks-law-30-pro' ),
		'attorney_1_knows'  => __( 'Attorney 1 — focus areas (comma separated)', 'brooks-law-30-pro' ),
		'attorney_2_name'   => __( 'Attorney 2 — full name', 'brooks-law-30-pro' ),
		'attorney_2_title'  => __( 'Attorney 2 — job title', 'brooks-law-30-pro' ),
		'attorney_2_url'    => __( 'Attorney 2 — profile page path', 'brooks-law-30-pro' ),
		'attorney_2_alumni' => __( 'Attorney 2 — law school', 'brooks-law-30-pro' ),
		'attorney_2_knows'  => __( 'Attorney 2 — focus areas (comma separated)', 'brooks-law-30-pro' ),
	);

	foreach ( $fields as $key => $label ) {
		$wp_customize->add_setting(
			$key,
			array(
				'default'           => isset( $defaults[ $key ] ) ? $defaults[ $key ] : '',
				'sanitize_callback' => 'sanitize_text_field',
			)
		);
		$wp_customize->add_control(
			$key,
			array(
				'label'   => $label,
				'section' => 'brooks_law_seo',
				'type'    => 'text',
			)
		);
	}

	$wp_customize->add_setting(
		'service_area',
		array(
			'default'           => isset( $defaults['service_area'] ) ? $defaults['service_area'] : '',
			'sanitize_callback' => 'sanitize_text_field',
		)
	);
	$wp_customize->add_control(
		'service_area',
		array(
			'label'       => __( 'Areas served (comma separated)', 'brooks-law-30-pro' ),
			'description' => __( 'Cities and counties the firm appears in. Used for the areaServed claim in structured data.', 'brooks-law-30-pro' ),
			'section'     => 'brooks_law_seo',
			'type'        => 'text',
		)
	);
}
add_action( 'customize_register', 'brooks_law_identity_customize', 22 );
