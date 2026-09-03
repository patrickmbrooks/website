<?php
/**
 * Brooks Law v2 — homepage section data.
 *
 * The three practice divisions keep the firm's I · II · III motif from v1.
 * Titles, descriptions, and links are Customizer-editable so slug changes
 * never require a code edit.
 *
 * @package Brooks_Law
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Practice cards for the front page.
 *
 * @return array[] Each: numeral, title, desc, url.
 */
function brooks_law_practice_cards() {
	$numerals = array( 'I', 'II', 'III' );
	$cards    = array();

	for ( $i = 1; $i <= 3; $i++ ) {
		$title = brooks_law_get_option( "practice_{$i}_title" );
		$url   = brooks_law_get_option( "practice_{$i}_url" );

		if ( '' === trim( (string) $title ) || '' === trim( (string) $url ) ) {
			continue;
		}

		$cards[] = array(
			'numeral' => $numerals[ $i - 1 ],
			'title'   => $title,
			'desc'    => brooks_law_get_option( "practice_{$i}_desc" ),
			'url'     => $url,
		);
	}

	return $cards;
}

/**
 * Testimonials that are actually filled in.
 *
 * @return array[] Each: quote, name.
 */
function brooks_law_testimonials() {
	$items = array();
	for ( $i = 1; $i <= 4; $i++ ) {
		$quote = trim( (string) brooks_law_get_option( "testimonial_{$i}_quote", '' ) );
		if ( '' === $quote ) {
			continue;
		}
		$items[] = array(
			'quote' => $quote,
			'name'  => trim( (string) brooks_law_get_option( "testimonial_{$i}_name", '' ) ),
		);
	}
	return $items;
}

/**
 * Case results parsed from the Customizer textarea.
 * One result per line; an optional "|" splits charge from outcome.
 *
 * @return array[] Each: charge, outcome.
 */
function brooks_law_case_results() {
	$raw = (string) brooks_law_get_option( 'results_items', '' );
	if ( '' === trim( $raw ) ) {
		return array();
	}

	$items = array();
	foreach ( preg_split( '/\r\n|\r|\n/', $raw ) as $line ) {
		$line = trim( $line );
		if ( '' === $line ) {
			continue;
		}
		if ( false !== strpos( $line, '|' ) ) {
			list( $charge, $outcome ) = array_map( 'trim', explode( '|', $line, 2 ) );
		} else {
			$charge  = $line;
			$outcome = '';
		}
		$items[] = array(
			'charge'  => $charge,
			'outcome' => $outcome,
		);
	}
	return $items;
}
