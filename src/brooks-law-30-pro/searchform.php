<?php
/**
 * Brooks Law v2 — accessible search form.
 *
 * @package Brooks_Law
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$brooks_search_id = wp_unique_id( 'search-' );
?>
<form role="search" method="get" class="search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
	<label class="screen-reader-text" for="<?php echo esc_attr( $brooks_search_id ); ?>"><?php esc_html_e( 'Search this site', 'brooks-law-30-pro' ); ?></label>
	<input type="search" id="<?php echo esc_attr( $brooks_search_id ); ?>" class="search-field" placeholder="<?php esc_attr_e( 'Search…', 'brooks-law-30-pro' ); ?>" value="<?php echo get_search_query(); ?>" name="s">
	<button type="submit" class="search-submit btn btn-brass"><?php esc_html_e( 'Search', 'brooks-law-30-pro' ); ?></button>
</form>
