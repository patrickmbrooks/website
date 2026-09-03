<?php
/**
 * Schema repair — render-time, automatic, no plugin required.
 *
 * Some posts have FAQPage JSON-LD that was broken by wpautop injecting
 * <br /> tags inside the <script type="application/ld+json"> blocks.
 * This filter repairs those blocks at render time, on every post and
 * page, so the HTML that browsers, Google, and AI crawlers receive
 * always carries valid schema — including any post where the same
 * breakage happens again in the future.
 *
 * The database is never modified: the stored content stays exactly as
 * it is, and deactivating this theme removes the repair with it.
 *
 * Contract, mirrored from the audited one-time fix:
 *   - A block that already parses as valid JSON is left untouched.
 *   - A broken block is only replaced when the cleaned version parses.
 *   - Anything else passes through byte-for-byte.
 *
 * @package brooks-law
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Repair <br>-broken JSON-LD script blocks in a content string.
 *
 * @param string $content Post content HTML.
 * @return string
 */
function brooks_law_repair_jsonld( $content ) {

	// Fast exit for the overwhelmingly common case.
	if ( ! is_string( $content ) || false === strpos( $content, 'ld+json' ) ) {
		return $content;
	}

	$result = preg_replace_callback(
		'#(<script[^>]*application/ld\+json[^>]*>)(.*?)(</script>)#is',
		function ( $m ) {

			$inner = $m[2];

			// Already valid? Leave it alone.
			json_decode( $inner );
			if ( JSON_ERROR_NONE === json_last_error() ) {
				return $m[0];
			}

			$cleaned = preg_replace( '#<br\s*/?>#i', '', $inner );

			// Only swap in the cleaned version if it actually parses.
			json_decode( $cleaned );
			if ( JSON_ERROR_NONE !== json_last_error() ) {
				return $m[0];
			}

			return $m[1] . $cleaned . $m[3];
		},
		$content
	);

	// preg_replace_callback returns null on engine error; never lose content.
	return is_string( $result ) ? $result : $content;
}

/*
 * Priority 99: after wpautop (10) and shortcodes (11), so the repair
 * sees the final markup — including any <br /> tags autop just added —
 * and nothing runs after it to re-break the blocks.
 */
add_filter( 'the_content', 'brooks_law_repair_jsonld', 99 );
