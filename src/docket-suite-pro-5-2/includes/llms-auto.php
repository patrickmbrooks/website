<?php
/**
 * /llms.txt automatic generation.
 *
 * WHAT THIS SOLVES
 * ----------------
 * llms-txt.php serves a hand-maintained body from an option. That body was
 * last edited in August and listed 50 URLs while the site had grown to 157 —
 * so the file that tells AI assistants what is worth reading was pointing at
 * roughly a third of the site, and missing the newest, most commercially
 * targeted clusters entirely. A hand-maintained list of a growing site is a
 * list that is always out of date.
 *
 * This module generates the body from the live site instead. Publish a page
 * and it appears; unpublish it and it goes away. Nothing to remember.
 *
 * HOW IT WORKS
 * ------------
 *   - Mode is chosen on Settings → LLMs.txt: Automatic (default for new
 *     installs) or Manual (the existing editable box, unchanged).
 *   - The header block — firm description, address, phones, hours — stays
 *     hand-written and editable, because no generator can write that well.
 *     Only the page listing is generated.
 *   - Pages are grouped into sections by URL pattern, so the output reads
 *     like the curated file rather than a flat dump. Anything that matches
 *     no pattern lands in a general section; nothing is silently dropped.
 *   - Each entry's description is its Docket SEO description, its Yoast
 *     description, its excerpt, or a trimmed opening — first one that exists.
 *   - noindexed and password-protected pages are excluded, matching the
 *     sitemap's behaviour.
 *   - The result is cached in a transient and rebuilt automatically whenever
 *     any page or post is saved, so serving /llms.txt never runs the query.
 *
 * Switching to Manual preserves whatever was last generated as the starting
 * point, so nothing is lost by experimenting.
 *
 * @package Docket_Suite
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Option: 'auto' or 'manual'. */
if ( ! defined( 'BROOKS_LLMS_MODE' ) ) {
	define( 'BROOKS_LLMS_MODE', 'brooks_llms_mode' );
}

/** Option: the hand-written header block used in automatic mode. */
if ( ! defined( 'BROOKS_LLMS_HEADER' ) ) {
	define( 'BROOKS_LLMS_HEADER', 'brooks_llms_header' );
}

/** Transient: the generated body. */
if ( ! defined( 'BROOKS_LLMS_CACHE' ) ) {
	define( 'BROOKS_LLMS_CACHE', 'brooks_llms_generated' );
}

/**
 * Current mode. Defaults to automatic.
 *
 * @return string 'auto' or 'manual'.
 */
function brooks_llms_mode() {
	/*
	 * Default is MANUAL, deliberately.
	 *
	 * llms.txt rewards curation, not completeness. It is a guide for a reader
	 * with limited attention, so a short hand-written list with real
	 * descriptions — published flat fees, court-by-court pricing, the facts an
	 * assistant needs to cite the firm accurately — outperforms an exhaustive
	 * generated dump of every page. Automatic mode exists for sites with no
	 * curated file, or when a list has grown too large to maintain by hand.
	 * It should not silently replace a good one.
	 */
	$mode = get_option( BROOKS_LLMS_MODE, 'manual' );

	return ( 'auto' === $mode ) ? 'auto' : 'manual';
}

/**
 * The default header block: everything above the page listing.
 *
 * @return string
 */
function brooks_llms_default_header() {
	return <<<'BROOKS_LLMS_HEADER_DEFAULT'
# Brooks Law Firm — Memphis, Tennessee

> Brooks Law Firm is a criminal defense practice in Midtown Memphis, Tennessee, serving Memphis, Shelby County, and the surrounding counties of West Tennessee (Fayette, Tipton, Lauderdale, Haywood). The firm defends DUI, drug charges, domestic assault, theft, traffic and CDL tickets, probation violations, and every other criminal charge, and also handles civil litigation, personal injury, and divorce matters. Attorneys: Patrick Brooks and Beth Brooks. Free initial consultation. Se habla Español.

Office: 2299 Union Avenue, Memphis, TN 38104
Phone: (901) 324-5000 (office) · 901-412-2973 (criminal line, call or text)
Email: patrick@patrickbrookslaw.com
Hours: Monday–Friday, 8:00 a.m. – 5:30 p.m.
BROOKS_LLMS_HEADER_DEFAULT;
}

/**
 * The stored header, falling back to the default.
 *
 * @return string
 */
function brooks_llms_header() {
	$header = get_option( BROOKS_LLMS_HEADER, '' );

	return ( is_string( $header ) && '' !== trim( $header ) ) ? $header : brooks_llms_default_header();
}

/**
 * Section definitions, in output order.
 *
 * Each section matches slugs by substring. Order matters: the first match
 * wins, so narrower sections are listed before broader ones. A page matching
 * nothing falls through to "More From the Firm" rather than disappearing.
 *
 * Filterable so the map can be adjusted without editing this file.
 *
 * @return array<string,array<int,string>> Heading => list of slug fragments.
 */
function brooks_llms_sections() {
	$map = array(
		'Start Here — What Happens Next' => array(
			'what-happens-after-arrest', 'arraignment', 'preliminary-hearing',
			'how-does-bond-work', 'how-long-does-a-criminal-case-take',
			'capias-bench-warrant', 'warrant',
		),
		'What It Costs' => array( 'how-much-does-a' ),
		'DUI Defense' => array( 'dui' ),
		'Drug Charges' => array(
			'drug-offense', 'drug-conspiracy', 'drug-arrest', 'drug-testing',
			'cocaine', 'fentanyl', 'marijuana', 'methamphetamine', 'heroin',
			'ecstasy', 'prescription-pills', 'shelby-county-drug-court',
			'drug-charge-lawyer',
		),
		'Theft and Property Crimes' => array(
			'theft', 'shoplifting', 'burglary', 'robbery', 'civil-demand',
		),
		'Domestic Assault and Orders of Protection' => array(
			'domestic-violence', 'domestic-assault', 'order-of-protection',
			'file-order-of-protection', 'division-10',
		),
		'Traffic, CDL, and Driver’s License' => array(
			'traffic', 'cdl', 'speeding', 'reckless-driving', 'suspended-license',
			'habitual-motor-vehicle', 'failure-to-maintain', 'leaving-scene',
			'waiving-court-costs',
		),
		'Felonies and Misdemeanors' => array(
			'felony', 'misdemeanor', 'assault', 'unlawful-weapon',
			'sexual-offenses', 'juvenile-defense', 'expungement',
			'accessory-after-the-fact', 'false-offense-report',
			'taking-contraband', 'contributing-to-delinquency',
			'disorderly-conduct', 'public-intoxication', 'probation-violation',
			'electronic-monitoring', 'criminal-charges-we-defend',
			'white-collar', 'federal-criminal-defense', 'criminal-appeal',
			'veterans-criminal-defense', 'employment-criminal-defense',
			'civil-asset-forfeiture', 'electronic-surveillance',
			'patronizing-prostitution', 'protest-civil-disobedience',
			'selling-alcohol', 'minor-in-possession', 'beer-and-liquor',
			'legislative-updates',
		),
		'Where We Practice' => array(
			'criminal-defense-2', 'courts-we-serve', 'germantown', 'bartlett',
			'collierville', 'cordova', 'millington', 'arlington', 'lakeland',
			'fayette', 'tipton', 'lauderdale', 'haywood', 'oakland-rossville',
		),
		'The Firm' => array(
			'firm-profile', 'patrick-brooks', 'beth-brooks', 'contact',
			'resources', 'blog',
		),
		'Other Practice Areas' => array(
			'divorce', 'civil-litigation', 'business-litigation',
			'personal-injury', 'wrongful-death', 'immigration', 'inmigracion',
			'intellectual-property', 'music-artist', 'maritime',
		),
	);

	return (array) apply_filters( 'brooks_llms_sections', $map );
}

/**
 * A one-line description for a page.
 *
 * Docket field, then Yoast field, then excerpt, then trimmed content.
 *
 * @param WP_Post $post Post object.
 * @return string
 */
function brooks_llms_describe( $post ) {
	$desc = trim( (string) get_post_meta( $post->ID, '_docket_seo_desc', true ) );

	if ( '' === $desc ) {
		$yoast = trim( (string) get_post_meta( $post->ID, '_yoast_wpseo_metadesc', true ) );
		if ( '' !== $yoast && false === strpos( $yoast, '%%' ) ) {
			$desc = $yoast;
		}
	}

	if ( '' === $desc && '' !== trim( (string) $post->post_excerpt ) ) {
		$desc = $post->post_excerpt;
	}

	if ( '' === $desc ) {
		$desc = $post->post_content;
	}

	$desc = wp_strip_all_tags( strip_shortcodes( (string) $desc ) );
	$desc = preg_replace( '/\s+/', ' ', $desc );
	$desc = trim( (string) $desc );

	if ( function_exists( 'mb_substr' ) && function_exists( 'mb_strlen' ) ) {
		if ( mb_strlen( $desc ) > 160 ) {
			$desc = rtrim( mb_substr( $desc, 0, 157 ), " \t\n\r\0\x0B.,;:" ) . '…';
		}
	} elseif ( strlen( $desc ) > 160 ) {
		$desc = rtrim( substr( $desc, 0, 157 ), " \t\n\r\0\x0B.,;:" ) . '…';
	}

	return $desc;
}

/**
 * Build the full llms.txt body from live content.
 *
 * @return string
 */
function brooks_llms_generate() {
	$posts = get_posts(
		array(
			'post_type'        => array( 'page', 'post' ),
			'post_status'      => 'publish',
			'numberposts'      => -1,
			'orderby'          => 'title',
			'order'            => 'ASC',
			'has_password'     => false,
			'suppress_filters' => false,
			// Prime the meta cache in one query. brooks_llms_describe() reads
			// two meta keys per post; without this the generator would issue
			// two queries per page (an N+1 across 157 pages). Explicit rather
			// than relying on the default, because the default is easy to
			// lose in a later edit.
			'update_post_meta_cache' => true,
			'update_post_term_cache' => false, // Terms are never read here.
		)
	);

	$sections = brooks_llms_sections();
	$buckets  = array_fill_keys( array_keys( $sections ), array() );
	$overflow = array();
	$front_id = (int) get_option( 'page_on_front' );

	foreach ( $posts as $post ) {
		if ( $post->ID === $front_id ) {
			continue; // The header already introduces the site.
		}

		// Respect noindex, however it was set.
		if ( class_exists( 'Docket_SEO' ) && Docket_SEO::instance()->is_noindexed( $post->ID ) ) {
			continue;
		}

		$url = get_permalink( $post );
		if ( ! $url ) {
			continue;
		}

		$line = sprintf(
			'- [%s](%s): %s',
			wp_strip_all_tags( get_the_title( $post ) ),
			$url,
			brooks_llms_describe( $post )
		);

		$slug   = (string) $post->post_name;
		$placed = false;

		foreach ( $sections as $heading => $fragments ) {
			foreach ( $fragments as $fragment ) {
				if ( false !== strpos( $slug, $fragment ) ) {
					$buckets[ $heading ][] = $line;
					$placed                = true;
					break 2;
				}
			}
		}

		if ( ! $placed ) {
			$overflow[] = $line;
		}
	}

	$out = trim( brooks_llms_header() ) . "\n";

	foreach ( $sections as $heading => $fragments ) {
		if ( empty( $buckets[ $heading ] ) ) {
			continue;
		}
		$out .= "\n## " . $heading . "\n\n" . implode( "\n", $buckets[ $heading ] ) . "\n";
	}

	if ( $overflow ) {
		$out .= "\n## More From the Firm\n\n" . implode( "\n", $overflow ) . "\n";
	}

	$out .= "\n---\n\nGenerated automatically from published pages on " . gmdate( 'Y-m-d' ) . ".\n";

	return $out;
}

/**
 * The generated body, from cache when available.
 *
 * @return string
 */
function brooks_llms_generated_content() {
	$cached = get_transient( BROOKS_LLMS_CACHE );

	if ( is_string( $cached ) && '' !== $cached ) {
		return $cached;
	}

	$content = brooks_llms_generate();
	set_transient( BROOKS_LLMS_CACHE, $content, WEEK_IN_SECONDS );

	return $content;
}

/**
 * Drop the cache whenever content changes, so the next request rebuilds.
 */
function brooks_llms_flush_cache() {
	delete_transient( BROOKS_LLMS_CACHE );
}
add_action( 'save_post', 'brooks_llms_flush_cache' );
add_action( 'deleted_post', 'brooks_llms_flush_cache' );
add_action( 'trashed_post', 'brooks_llms_flush_cache' );
add_action( 'untrashed_post', 'brooks_llms_flush_cache' );
