<?php
/**
 * Brooks Essentials — robots.txt manager (v3.0, replaces crawlers.php).
 *
 * WHY THE REWRITE
 * ---------------
 * The live /robots.txt was serving flattened, malformed lines ("Allow: /
 * User-agent: OAI-SearchBot", "Disallow: Sitemap: ...") even though the
 * old crawlers.php joined its lines with "\n" correctly. On this site's
 * stack (Cloudflare edge in front of everything), the virtual robots.txt
 * response is passing through layers that can mangle it, and leftover
 * fragments (an orphaned Yoast block) were riding along in the output.
 * Lighthouse failed the file; crawlers were parsing garbage.
 *
 * THE v3 ANSWER: MANAGED PHYSICAL MODE (default)
 * ----------------------------------------------
 * The plugin now WRITES a real robots.txt file to the site root and keeps
 * it correct. A static file is served directly by the web server —
 * no PHP, no plugin filter chain, no middleware between the bytes on disk
 * and the crawler — so nothing can flatten it again, and it also fixes
 * the situation where a broken physical file was already overriding the
 * virtual one. The file is:
 *
 *   - regenerated on every settings save,
 *   - drift-checked once a day (and self-healed if something rewrote it),
 *   - built line-by-line and passed through a syntax LINTER that drops
 *     any line carrying two directives, so a malformed file can
 *     structurally never ship,
 *   - stamped with a marker comment so the plugin only ever overwrites
 *     files it owns — a hand-made robots.txt without the marker is left
 *     alone and reported in the settings screen instead.
 *
 * The sitemap line is auto-detected (manual override available) and the
 * URL is verified reachable daily; a dead sitemap is dropped, not
 * advertised. Virtual modes remain available for stacks without root
 * write access, and "leave" still means leave.
 *
 * Settings keys used (all in brooks_ess_options):
 *   robots_mode  managed | virtual | append | leave   (default managed)
 *   ai_crawlers  allow | disallow | omit              (unchanged key)
 *   sitemap_url  manual override, blank = auto
 *   robots_extra literal extra lines (linted)
 *
 * @package Brooks_Essentials
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Marker proving a physical robots.txt is plugin-managed. */
define( 'BROOKS_ESS_ROBOTS_MARK', '# Managed by Brooks Law Essentials — edit in Settings > Site Essentials' );

/**
 * The crawlers worth naming explicitly.
 *
 * @return array Agent => human-readable purpose.
 */
function brooks_ess_ai_agents() {
	$agents = array(
		'GPTBot'             => __( 'OpenAI — training and browsing', 'docket-suite' ),
		'OAI-SearchBot'      => __( 'OpenAI — ChatGPT search results', 'docket-suite' ),
		'ChatGPT-User'       => __( 'OpenAI — user-initiated page fetches', 'docket-suite' ),
		'ClaudeBot'          => __( 'Anthropic — Claude training/index', 'docket-suite' ),
		'Claude-User'        => __( 'Anthropic — user-initiated page fetches', 'docket-suite' ),
		'Claude-SearchBot'   => __( 'Anthropic — Claude search results', 'docket-suite' ),
		'PerplexityBot'      => __( 'Perplexity — index', 'docket-suite' ),
		'Perplexity-User'    => __( 'Perplexity — user-initiated fetches', 'docket-suite' ),
		'Google-Extended'    => __( 'Google — Gemini training', 'docket-suite' ),
		'Applebot-Extended'  => __( 'Apple Intelligence', 'docket-suite' ),
		'Bytespider'         => __( 'ByteDance', 'docket-suite' ),
		'meta-externalagent' => __( 'Meta AI', 'docket-suite' ),
		'cohere-ai'          => __( 'Cohere', 'docket-suite' ),
		'Amazonbot'          => __( 'Amazon — Alexa/Rufus', 'docket-suite' ),
	);

	/**
	 * Filter the AI crawler list.
	 *
	 * @param array $agents Agent => purpose.
	 */
	return apply_filters( 'brooks_ess_ai_agents', $agents );
}

/**
 * Resolve the sitemap URL: manual override → Yoast → this site's known
 * live index → core sitemaps. Verified reachable (HTTP 200) at most once
 * a day; a failing URL is dropped rather than advertised.
 *
 * @return string Empty string when nothing valid is available.
 */
function brooks_ess_sitemap_url() {
	$manual = trim( (string) brooks_ess_get( 'sitemap_url' ) );

	if ( '' !== $manual ) {
		$url = esc_url_raw( $manual );
	} elseif ( defined( 'WPSEO_VERSION' ) ) {
		$url = home_url( '/sitemap_index.xml' );
	} elseif ( defined( 'DOCKET_SUITE_SEO_ACTIVE' ) ) {
		// Suite Pro 5: the Suite's own SEO half is running the sitemap.
		$url = home_url( '/sitemap.xml' );
	} else {
		// This site's index is live at /sitemap_index.xml even without
		// Yoast active; prefer it, fall through to core sitemaps below
		// via the reachability check.
		$url = home_url( '/sitemap_index.xml' );
	}

	if ( 'yes' === brooks_ess_url_ok( $url ) ) {
		return $url;
	}

	// Fallback: WordPress core sitemaps.
	$core = home_url( '/wp-sitemap.xml' );
	if ( (bool) apply_filters( 'wp_sitemaps_enabled', true ) && 'yes' === brooks_ess_url_ok( $core ) ) {
		return $core;
	}

	return '';
}

/**
 * Cached daily reachability check.
 *
 * @param string $url URL to verify.
 * @return string 'yes' | 'no'
 */
function brooks_ess_url_ok( $url ) {
	if ( '' === $url ) {
		return 'no';
	}
	$cache_key = 'brooks_ess_url_ok_' . md5( $url );
	$ok        = get_transient( $cache_key );

	if ( false === $ok ) {
		$response = wp_remote_head( $url, array( 'timeout' => 4, 'redirection' => 2 ) );
		$code     = is_wp_error( $response ) ? 0 : (int) wp_remote_retrieve_response_code( $response );
		if ( in_array( $code, array( 403, 405, 501 ), true ) ) {
			$response = wp_remote_get( $url, array( 'timeout' => 4, 'redirection' => 2 ) );
			$code     = is_wp_error( $response ) ? 0 : (int) wp_remote_retrieve_response_code( $response );
		}
		$ok = ( 200 === $code ) ? 'yes' : 'no';
		set_transient( $cache_key, $ok, DAY_IN_SECONDS );
	}

	return $ok;
}

/**
 * Build the robots.txt content as an array of lines.
 *
 * @return string[]
 */
function brooks_ess_robots_lines() {
	$lines   = array();
	$lines[] = BROOKS_ESS_ROBOTS_MARK;
	$mode    = brooks_ess_get( 'ai_crawlers' );

	// Back-compat: the old 'leave' value for ai_crawlers meant "no AI
	// rules"; map it to omit here (file-level leave is robots_mode now).
	if ( 'leave' === $mode ) {
		$mode = 'omit';
	}

	if ( 'omit' !== $mode ) {
		$lines[] = '';
		$lines[] = '# AI assistant and training crawlers';
		$rule    = ( 'disallow' === $mode ) ? 'Disallow: /' : 'Allow: /';
		foreach ( array_keys( brooks_ess_ai_agents() ) as $agent ) {
			$lines[] = 'User-agent: ' . $agent;
			$lines[] = $rule;
			$lines[] = '';
		}
	} else {
		$lines[] = '';
	}

	$lines[] = '# All other crawlers';
	$lines[] = 'User-agent: *';
	$lines[] = 'Disallow: /wp-admin/';
	$lines[] = 'Allow: /wp-admin/admin-ajax.php';
	$lines[] = '';

	$extra = trim( (string) brooks_ess_get( 'robots_extra' ) );
	if ( '' !== $extra ) {
		foreach ( preg_split( '/\r\n|\r|\n/', $extra ) as $line ) {
			$lines[] = trim( $line );
		}
		$lines[] = '';
	}

	$sitemap = brooks_ess_sitemap_url();
	if ( '' !== $sitemap ) {
		$lines[] = 'Sitemap: ' . $sitemap;
	}

	return $lines;
}

/**
 * Lint: keep only valid robots.txt lines (directive, comment, blank),
 * and drop any line carrying two directives mashed together — the exact
 * failure mode found live. Collapses doubled blanks, trims edges.
 *
 * @param string[] $lines Candidate lines.
 * @return string[]
 */
function brooks_ess_robots_lint( $lines ) {
	$valid = array();
	$re    = '/^(User-agent|Allow|Disallow|Sitemap|Crawl-delay|Host)\s*:/i';

	foreach ( (array) $lines as $line ) {
		$line = rtrim( (string) $line );

		if ( '' === $line || '#' === substr( $line, 0, 1 ) ) {
			$valid[] = $line;
			continue;
		}
		if ( ! preg_match( $re, $line ) ) {
			continue; // Not a directive at all.
		}
		$rest = substr( $line, strpos( $line, ':' ) + 1 );
		if ( preg_match( '/\b(User-agent|Allow|Disallow|Sitemap)\s*:/i', $rest ) ) {
			continue; // Two directives on one line — drop it.
		}
		$valid[] = $line;
	}

	$out  = array();
	$prev = null;
	foreach ( $valid as $line ) {
		if ( '' === $line && '' === $prev ) {
			continue;
		}
		$out[] = $line;
		$prev  = $line;
	}
	while ( $out && '' === end( $out ) ) {
		array_pop( $out );
	}
	while ( $out && '' === $out[0] ) {
		array_shift( $out );
	}

	return $out;
}

/**
 * Final robots.txt content string (LF endings).
 *
 * @return string
 */
function brooks_ess_robots_content() {
	return implode( "\n", brooks_ess_robots_lint( brooks_ess_robots_lines() ) ) . "\n";
}

/* -------------------------------------------------------------------------
 * Managed physical file
 * ---------------------------------------------------------------------- */

/**
 * May we write the physical file? Only when the mode says managed AND the
 * root file is either absent, ours (has the marker), or the takeover of a
 * foreign file has been explicitly approved in settings via
 * robots_takeover (one-shot flag).
 *
 * @return string 'ok' | 'foreign' | 'unwritable' | 'off'
 */
function brooks_ess_robots_write_status() {
	if ( 'managed' !== brooks_ess_get( 'robots_mode' ) ) {
		return 'off';
	}

	$path = ABSPATH . 'robots.txt';

	if ( file_exists( $path ) ) {
		$head = (string) file_get_contents( $path, false, null, 0, 512 ); // phpcs:ignore
		if ( false === strpos( $head, 'Brooks Law Essentials' ) && ! brooks_ess_get( 'robots_takeover' ) ) {
			return 'foreign';
		}
		return is_writable( $path ) ? 'ok' : 'unwritable';
	}

	return is_writable( ABSPATH ) ? 'ok' : 'unwritable';
}

/**
 * Write (or heal) the physical robots.txt. Only writes when content
 * differs, so repeated calls are free.
 *
 * @return bool True when the file on disk now matches the built content.
 */
function brooks_ess_robots_write() {
	$status = brooks_ess_robots_write_status();
	if ( 'ok' !== $status ) {
		return false;
	}

	$path    = ABSPATH . 'robots.txt';
	$content = brooks_ess_robots_content();

	if ( file_exists( $path ) && (string) file_get_contents( $path ) === $content ) { // phpcs:ignore
		return true;
	}

	$written = file_put_contents( $path, $content, LOCK_EX ); // phpcs:ignore
	return false !== $written;
}

/**
 * Regenerate on every settings save (also purges the Brooks Law 3.0
 * theme's page cache if that theme is active, so changes are live now).
 */
function brooks_ess_robots_on_save() {
	delete_transient( 'brooks_ess_robots_checked' );
	brooks_ess_robots_write();

	if ( function_exists( 'brooks_law_cache_purge' ) ) {
		brooks_law_cache_purge();
	}
}
add_action( 'update_option_' . BROOKS_ESS_OPTION, 'brooks_ess_robots_on_save', 20 );

/**
 * Daily drift check without cron dependence: piggybacks on admin visits.
 */
function brooks_ess_robots_drift_check() {
	if ( ! is_admin() || wp_doing_ajax() ) {
		return;
	}
	if ( get_transient( 'brooks_ess_robots_checked' ) ) {
		return;
	}
	set_transient( 'brooks_ess_robots_checked', 1, DAY_IN_SECONDS );
	brooks_ess_robots_write();
}
add_action( 'admin_init', 'brooks_ess_robots_drift_check', 20 );

/* -------------------------------------------------------------------------
 * Virtual modes (fallback / no-root-access stacks)
 * ---------------------------------------------------------------------- */

/**
 * The robots_txt filter. In managed mode this ALSO emits the same clean
 * content, purely as belt-and-suspenders for the moment before the first
 * physical write, or if the file is ever deleted by hand.
 *
 * @param string $output Existing robots.txt body.
 * @param bool   $public Site visibility flag.
 * @return string
 */
function brooks_ess_robots_txt( $output, $public ) {
	if ( '0' === (string) $public ) {
		return $output;
	}

	$mode = brooks_ess_get( 'robots_mode' );

	if ( 'leave' === $mode ) {
		return $output;
	}

	if ( 'append' === $mode ) {
		$existing = preg_split( '/\r\n|\r|\n/', (string) $output );
		$merged   = array_merge( $existing, array( '' ), brooks_ess_robots_lines() );
		return implode( "\n", brooks_ess_robots_lint( $merged ) ) . "\n";
	}

	// managed + virtual: full clean replacement.
	return brooks_ess_robots_content();
}
add_filter( 'robots_txt', 'brooks_ess_robots_txt', 99, 2 );

/**
 * Is there a physical robots.txt in the site root?
 *
 * @return bool
 */
function brooks_ess_has_physical_robots() {
	return file_exists( ABSPATH . 'robots.txt' );
}

/**
 * Is the physical file (if any) plugin-managed?
 *
 * @return bool
 */
function brooks_ess_robots_is_ours() {
	$path = ABSPATH . 'robots.txt';
	if ( ! file_exists( $path ) ) {
		return false;
	}
	$head = (string) file_get_contents( $path, false, null, 0, 512 ); // phpcs:ignore
	return false !== strpos( $head, 'Brooks Law Essentials' );
}
