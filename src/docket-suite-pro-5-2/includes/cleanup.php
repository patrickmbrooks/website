<?php
/**
 * Brooks Essentials — comments and hardening.
 *
 * A law firm site has no use for a comment system, and an open one is a spam
 * and moderation liability. The hardening switches are modest but free.
 *
 * @package Brooks_Essentials
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* -------------------------------------------------------------------------
 * Comments
 * ---------------------------------------------------------------------- */

/**
 * Close comments and pings everywhere.
 *
 * @return bool
 */
function brooks_ess_comments_closed() {
	return false;
}

/**
 * Wire up the comment shutdown if enabled.
 */
function brooks_ess_disable_comments() {
	if ( ! brooks_ess_get( 'disable_comments' ) ) {
		return;
	}

	add_filter( 'comments_open', 'brooks_ess_comments_closed', 20 );
	add_filter( 'pings_open', 'brooks_ess_comments_closed', 20 );

	// Hide any comments that already exist rather than deleting them.
	add_filter( 'comments_array', '__return_empty_array', 20 );

	// Remove the admin menu item and dashboard widget.
	add_action( 'admin_menu', 'brooks_ess_remove_comments_menu' );
	add_action( 'wp_dashboard_setup', 'brooks_ess_remove_comments_dashboard' );

	// Remove the toolbar link.
	add_action( 'admin_bar_menu', 'brooks_ess_remove_comments_toolbar', 999 );

	// Turn off support on post types.
	add_action( 'init', 'brooks_ess_remove_comment_support', 100 );
}
add_action( 'plugins_loaded', 'brooks_ess_disable_comments' );

/**
 * Drop the Comments admin menu.
 */
function brooks_ess_remove_comments_menu() {
	remove_menu_page( 'edit-comments.php' );
}

/**
 * Drop the recent comments dashboard widget.
 */
function brooks_ess_remove_comments_dashboard() {
	remove_meta_box( 'dashboard_recent_comments', 'dashboard', 'normal' );
}

/**
 * Drop the toolbar comments bubble.
 *
 * @param WP_Admin_Bar $wp_admin_bar Toolbar object.
 */
function brooks_ess_remove_comments_toolbar( $wp_admin_bar ) {
	if ( is_object( $wp_admin_bar ) ) {
		$wp_admin_bar->remove_node( 'comments' );
	}
}

/**
 * Remove comment support from post types.
 */
function brooks_ess_remove_comment_support() {
	foreach ( get_post_types() as $post_type ) {
		if ( post_type_supports( $post_type, 'comments' ) ) {
			remove_post_type_support( $post_type, 'comments' );
			remove_post_type_support( $post_type, 'trackbacks' );
		}
	}
}

/* -------------------------------------------------------------------------
 * Hardening
 * ---------------------------------------------------------------------- */

/**
 * Block the built-in theme and plugin file editors.
 *
 * Editing live PHP from a browser with no version control is how sites go
 * white-screen at 11pm. Files still change fine over SFTP.
 */
function brooks_ess_disable_file_edit() {
	if ( ! brooks_ess_get( 'disable_file_edit' ) ) {
		return;
	}

	if ( ! defined( 'DISALLOW_FILE_EDIT' ) ) {
		define( 'DISALLOW_FILE_EDIT', true );
	}
}
add_action( 'plugins_loaded', 'brooks_ess_disable_file_edit' );

/**
 * Turn off XML-RPC.
 *
 * Off by default: it is a real brute-force surface, but it is also what the
 * WordPress mobile app and Jetpack use. Only enable if neither is in play.
 */
function brooks_ess_disable_xmlrpc() {
	if ( ! brooks_ess_get( 'disable_xmlrpc' ) ) {
		return;
	}

	add_filter( 'xmlrpc_enabled', '__return_false' );
	add_filter( 'wp_headers', 'brooks_ess_remove_pingback_header' );
}
add_action( 'plugins_loaded', 'brooks_ess_disable_xmlrpc' );

/**
 * Strip the X-Pingback header when XML-RPC is off.
 *
 * @param array $headers Response headers.
 * @return array
 */
function brooks_ess_remove_pingback_header( $headers ) {
	if ( is_array( $headers ) && isset( $headers['X-Pingback'] ) ) {
		unset( $headers['X-Pingback'] );
	}

	return $headers;
}
