<?php
/**
 * Brooks Essentials — uninstall.
 *
 * Runs only when the plugin is deleted from the Plugins screen, and only
 * removes data if that box was ticked in the settings.
 *
 * @package Brooks_Essentials
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

$brooks_ess_options = get_option( 'brooks_ess_options', array() );

if ( is_array( $brooks_ess_options ) && ! empty( $brooks_ess_options['delete_on_uninstall'] ) ) {
	delete_option( 'brooks_ess_options' );
	delete_option( 'brooks_ess_404_log' );
}
