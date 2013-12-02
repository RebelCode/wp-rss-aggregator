<?php  
	/**
	 * Contains the secure reset functionality.
	 *
	 * @package WP PRSS Aggregator
	 */ 


	add_action( 'plugins_loaded', 'wprss_check_secure_reset' );
	/**
	 * 
	 * 
	 * @since 3.7.1
	 */
	function wprss_check_secure_reset() {
		// Get the GET parameters
		$wprss_action = ( isset( $_GET['wprss_action'] ) )? $_GET['wprss_action'] : NULL;
		$wprss_security_code = ( isset( $_GET['wprss_security_code'] ) )? $_GET['wprss_security_code'] : NULL;

		// If at least one of them is not specified, exit
		if ( $wprss_action === NULL || $wprss_security_code === NULL ) {
			return;
		}

		// Get the code from the Database
		$DB_CODE = get_option( 'wprss_secure_reset_code', '' );

		// Check if the code is empty
		if ( $DB_CODE === '' || strlen( $DB_CODE ) === 0 ) {
			return;
		}

		// Check if the code in $_GET matches the one in the Database
		if ( $DB_CODE !== $wprss_security_code ) {
			// If not exit
			return;
		}

		// Do a reset of settings
		if ( $wprss_action === 'reset' || $wprss_action === 'reset_and_deactivate' ) {
			delete_option( 'wprss_settings_general' );
			delete_option( 'wprss_db_version' );
			delete_option( 'wprss_settings_license_keys' );
			delete_option( 'wprss_settings_license_statuses' );
			delete_option( 'wprss_addon_notices' );
			delete_option( 'wprss_settings_notices' );
			delete_option( 'wprss_pwsv' );
		}

		// Deactivate the plugin
		if ( $wprss_action === 'deactivate' || $wprss_action === 'reset_and_deactivate' ) {
			require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
			deactivate_plugins( WPRSS_FILE_CONSTANT, TRUE );
		}

	}