<?php
// If uninstall not called from WordPress exit
if( ! defined( 'WP_UNINSTALL_PLUGIN' ) )
exit ();

// Remove capabilities
if ( function_exists( 'wprss_remove_caps' ) )
	wprss_remove_caps();

// Delete option from options table	
delete_option( 'wprss_options' );
delete_option( 'wprss_settings' );
delete_option( 'wprss_settings_general' );
delete_option( 'wprss_db_version' );
delete_option( 'wprss_settings_notices' );