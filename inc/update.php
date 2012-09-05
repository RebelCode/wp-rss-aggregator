<?php

    /** 
     * Contains all the functions related to updating the plugin from
     * one version to another
     *         
     * @package WPRSSAggregator
     */

	/**
	 * Checks the version number and runs install or update functions if needed.
	 *
	 * @since 2.0
	 */
	function wprss_version_check() {

		// Get the old database version.
		$old_db_version = get_option( 'wprss_db_version' );
		
		// Get the plugin settings. 
		$settings = get_option( 'wprss_settings' );

		// Get the plugin options 
		$options = get_option( 'wprss_options' ); 

		// For fresh installs
		// If there is no old database version and no settings, run the install. 
		if ( empty( $old_db_version ) && false === $settings && false === $options ) {
			wprss_install();
		}

		// For version 1.0 to 2.0
		// If there is no old database version and no settings, but only options
		elseif ( empty( $old_db_version ) && false === $settings && !empty( $options ) ) {
			wprss_install();
			wprss_migrate();		
		}

		// For version 1.1 to 2.0 
		// If there is no old database version, but only settings and options
		elseif ( empty( $old_db_version ) && !empty( $settings ) && !empty( $options ) ) {
			wprss_update();
			wprss_migrate();
		}

		// For any future versions where DB changes 
		// If the old version is less than the new version, run the update.
		elseif ( intval( $old_db_version ) < intval( WPRSS_DB_VERSION ) ) {
			wprss_update();
		}
	}
	
	add_action('init', 'wprss_version_check' );


	/**
	 * Adds the plugin settings on install.
	 *
	 * @since 2.0
	 */
	function wprss_install() {

		// Add the database version setting. 
		add_option( 'wprss_db_version', WPRSS_DB_VERSION );

		// Add the default plugin settings.
		add_option( 'wprss_settings', wprss_get_default_settings() );
	}


	/**
	 * Updates plugin settings if there are new settings to add.
	 *
	 * @since 2.0
	 */
	function wprss_update() {

		// Update the database version setting. 
		update_option( 'wprss_db_version', WPRSS_DB_VERSION );

		// Get the settings from the database. 
		$settings = get_option( 'wprss_settings' );

		// Get the default plugin settings.
		$default_settings = wprss_get_default_settings();

		// Loop through each of the default plugin settings. 
		foreach ( $default_settings as $setting_key => $setting_value ) {

			// If the setting didn't previously exist, add the default value to the $settings array. 
			if ( !isset( $settings[$setting_key] ) )
				$settings[$setting_key] = $setting_value;
		}

		// Update the plugin settings.
		update_option( 'wprss_settings', $settings );
	}


	/**
	 * Migrates the feed sources from the wprss_options field to the wp_posts table
	 *
	 * @since 2.0
	 */	
	function wprss_migrate() {
		
		// Get the plugin options 
		$options = get_option( 'wprss_options' ); 

        $feed_sources = array_chunk( $options, 2 );
        
        foreach ( $feed_sources as $feed_source ) { 
            $feed_title = $feed_source[0];
            $feed_url = $feed_source[1];
            
            // Create post object
            $feed_item = array(
                'post_title' 	=> $feed_title,
                'post_content' 	=> '',
                'post_status' 	=> 'publish',
                'post_type' 	=> 'wprss_feed'
            );             
            
            $inserted_ID = wp_insert_post( $feed_item, $wp_error );                              
            // insert post meta
            update_post_meta( $inserted_ID, 'wprss_url', $feed_url );      
        }   		
        // delete unneeded option
        delete_option( 'wprss_options' );
	}
	

	/**
	 * Returns an array of the default plugin settings. These are only used on initial setup.
	 *
	 * @since 2.0
	 */
	function wprss_get_default_settings() {

		// Set up the default plugin settings
		$settings = array(

			// from version 1.1
			'open_dd' => 'New window',
			'follow_dd' => 'No follow',
			// from version 2.0
			'feed_limit' => 10			
		);

		// Return the default settings
		return $settings;
	}

?>