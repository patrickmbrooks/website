<?php
/**
 * Brooks Essentials — early exact-match redirects.
 *
 * Why this exists
 * ---------------
 * WordPress can resolve an unknown legacy URL to a real page by matching only
 * its last slug segment ("canonical guessing"), and in some hosting stacks it
 * serves that page with a 200 at the WRONG url instead of 301'ing. Because that
 * is NOT a 404, the 404-gated fallback in redirects.php never fires, so a good
 * rule like:
 *
 *     /memphis-criminal-attorney/criminal-defense/drug-offense => /drug-offense/
 *
 * sits there unused while the fossil url keeps returning 200 (duplicate content
 * against the real page).
 *
 * What this does
 * --------------
 * 1. Turns OFF WordPress's guess-a-page-by-last-slug behaviour, so any legacy
 *    path that is not an explicit rule 404s honestly and reaches the 404-gated
 *    handler in redirects.php.
 * 2. Runs the SAME rule map (brooks_ess_redirect_map) EARLY, on template_redirect
 *    priority 0 — ahead of WordPress's own redirect_canonical (priority 10) and
 *    the 404-gated fallback (priority 1) — so an exact-match legacy url is 301'd
 *    before a page is ever rendered for it.
 *
 * Safety
 * ------
 * The early pass fires ONLY on an exact source match AND only when no real,
 * published page/post actually lives at that exact path. It can never shadow a
 * live page: the full-hierarchical-path check means a fossil like the one above
 * (whose real page lives at /drug-offense/, parent 0) does not count as "live"
 * at the fossil path, while a genuine page at its own url always does. This
 * preserves the original design intent — the bulk rule set stays 404-gated —
 * and only force-handles the specific paths that would otherwise wrongly 200.
 *
 * @package Brooks_Essentials
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Stop WordPress from guessing a page by its last url segment.
 *
 * With this off, a legacy path that is not an exact rule match 404s honestly,
 * which lets the 404-gated handler in redirects.php take over instead of a
 * silent 200 on the wrong url.
 */
add_filter( 'do_redirect_guess_404_permalink', '__return_false' );

/**
 * Is there a real, published page or post at EXACTLY this path?
 *
 * Uses a full hierarchical path match for pages (not a last-slug guess), so a
 * legacy url like /memphis-criminal-attorney/criminal-defense/drug-offense
 * returns false even though a page with slug "drug-offense" exists at
 * /drug-offense/. For posts, it only counts as live when the request path is
 * the post's real permalink path.
 *
 * @param string $path Normalized path: leading slash, no trailing slash, lowercase.
 * @return bool
 */
function brooks_ess_path_is_live_page( $path ) {
	$slug = trim( (string) $path, '/' );

	if ( '' === $slug ) {
		return true; // Home page: never touch.
	}

	// Pages resolve by full ancestor path — but on some setups get_page_by_path()
	// (or a canonical guess) can hand back a page whose REAL permalink is a
	// different, shorter url. So a match only counts as "live here" when the
	// page's own permalink path equals this exact path. That protects a genuine
	// page at its own url while still letting a legacy url that merely happens to
	// resolve to a page (e.g. /old/path/drug-offense -> the /drug-offense/ page)
	// be redirected.
	$page = get_page_by_path( $slug, OBJECT, 'page' );
	if ( $page instanceof WP_Post && 'publish' === $page->post_status ) {
		$page_path = brooks_ess_normalize_path( (string) wp_parse_url( get_permalink( $page ), PHP_URL_PATH ) );
		if ( $page_path === $path ) {
			return true;
		}
	}

	// A post lives here only if this path is its actual permalink.
	$leaf = $slug;
	$pos  = strrpos( $slug, '/' );
	if ( false !== $pos ) {
		$leaf = substr( $slug, $pos + 1 );
	}

	$post = get_page_by_path( $leaf, OBJECT, 'post' );
	if ( $post instanceof WP_Post && 'publish' === $post->post_status ) {
		$post_path = brooks_ess_normalize_path( (string) wp_parse_url( get_permalink( $post ), PHP_URL_PATH ) );
		if ( $post_path === $path ) {
			return true;
		}
	}

	return false;
}

/**
 * Apply the rule map to the exact request path, early, so wrong-200 legacy urls
 * are 301'd before WordPress renders a page for them.
 */
function brooks_ess_early_redirect() {
	if ( is_admin() ) {
		return;
	}

	$request = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
	$path    = brooks_ess_normalize_path( $request );

	if ( '/' === $path ) {
		return;
	}

	$map = brooks_ess_redirect_map();
	if ( empty( $map ) || ! isset( $map[ $path ] ) ) {
		return;
	}

	// Never shadow a real, published page/post that legitimately lives here.
	if ( brooks_ess_path_is_live_page( $path ) ) {
		return;
	}

	$target = brooks_ess_destination_url( $map[ $path ] );

	// Preserve any query string the visitor arrived with.
	$query = wp_parse_url( (string) $request, PHP_URL_QUERY );
	if ( $query ) {
		$target = add_query_arg( wp_parse_args( $query ), $target );
	}

	// Never redirect a path to itself.
	if ( brooks_ess_normalize_path( (string) wp_parse_url( $target, PHP_URL_PATH ) ) === $path ) {
		return;
	}

	wp_safe_redirect( $target, 301, 'Brooks Essentials (early)' );
	exit;
}
// Priority 0: ahead of redirect_canonical (10) and the 404-gated fallback (1).
add_action( 'template_redirect', 'brooks_ess_early_redirect', 0 );
