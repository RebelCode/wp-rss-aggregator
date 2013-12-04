<?php
    /** 
     * Contains all the functions related to updating the plugin from
     * one version to another
     *         
     * @package WP RSS Aggregator
     */

    add_action( 'init', 'wprss_version_check' );  
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

		// For version 1.0 to 3.0
		// If there is no old database version and no settings, but only options
		elseif ( empty( $old_db_version ) && false === $settings && !empty( $options ) ) {
			wp_clear_scheduled_hook( 'wprss_generate_hook' );
			wprss_install();
			wprss_migrate();	
			wprss_fetch_insert_all_feed_items();	
		}

		// For version 1.1 to 3.0 
		// If there is no old database version, but only settings and options
		elseif ( empty( $old_db_version ) && !empty( $settings ) && !empty( $options ) ) {
			wp_clear_scheduled_hook( 'wprss_generate_hook' );
			wprss_update();
			wprss_migrate();
			wprss_fetch_insert_all_feed_items(); 
		}

		// For version 2+ to 3.0
		// We check if wprss_settings option exists, as this only exists prior to version 3.0
		// Settings field changed, and another added
		elseif ( intval( $old_db_version ) < intval( WPRSS_DB_VERSION ) && ( FALSE != get_option( 'wprss_settings' ) ) ) {			
			wprss_upgrade_30();
			wprss_update();
			wprss_fetch_insert_all_feed_items(); 
		}

		// For any future versions where DB changes 
		// If the old version is less than the new version, run the update.		
		elseif ( intval( $old_db_version ) < intval( WPRSS_DB_VERSION ) ) {
			wprss_update();
			wprss_fetch_insert_all_feed_items();

			// NO FOLLOW CHANGE FIX
			$options = get_option( 'wprss_settings_general' );
			if ( $options['follow_dd'] === __( "No Follow", 'wprss' ) ) {
				$options['follow_dd'] = 'no_follow';
			} elseif ( $options['follow_dd'] === __( "Follow", 'wprss' ) ) {
				$options['follow_dd'] = 'follow';
			}
		}
		
	}


	/**
	 * Adds the plugin settings on install.
	 *
	 * @since 2.0
	 */
	function wprss_install() {

		// Add the database version setting. 
		add_option( 'wprss_db_version', WPRSS_DB_VERSION );

		// Add the default plugin settings.
		add_option( 'wprss_settings_general', wprss_get_default_settings_general() );
	}


	/**
	 * Update settings of plugin to reflect new version
	 *
	 * @since 2.0
	 */
	function wprss_update() {

		// Update the database version setting. 
		update_option( 'wprss_db_version', WPRSS_DB_VERSION );
		// Initialize settings
		wprss_settings_initialize();
	}


	/**
	 * Initialize settings to default ones if they are not yet set
	 *
	 * @since 3.0
	 */
	function wprss_settings_initialize() {
		// Get the settings from the new field in the database
		$settings = get_option( 'wprss_settings_general' );

		// Get the default plugin settings.
		$default_settings = wprss_get_default_settings_general();

		// Loop through each of the default plugin settings. 
		foreach ( $default_settings as $setting_key => $setting_value ) {

			// If the setting didn't previously exist, add the default value to the $settings array. 
			if ( ! isset( $settings[ $setting_key ] ) )
				$settings[ $setting_key ] = $setting_value;
		}

		// Update the plugin settings.
		update_option( 'wprss_settings_general', $settings );		
	}
	

	/**
	 * Takes care of cron and DB changes between versions 2+ and 3
	 *
	 * @since 3.0
	 */	
	function wprss_upgrade_30() {
		wp_clear_scheduled_hook( 'wprss_fetch_feeds_hook' );	

		// Get the settings from the database. 
		$settings = get_option( 'wprss_settings' );

		// Put them into our new field
		update_option( 'wprss_settings_general', $settings );

		// Remove old options field, we are now using wprss_settings_general
		delete_option( 'wprss_settings' );				
	}


	/**
	 * Migrates the feed sources from the wprss_options field to the wp_posts table (for older versions)
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
	function wprss_get_default_settings_general() {

		// Set up the default plugin settings
		$settings = apply_filters(
			'wprss_default_settings_general',
			array(
				// from version 1.1
				'open_dd' 					=> __( 'New window' ),
				'follow_dd' 				=> 'no_follow',
				
				// from version 2.0
				'feed_limit'				=> 15, 
				
				// from version 3.0
				'date_format'				=> 'Y-m-d',
				'limit_feed_items_db' 		=> 200,
				'cron_interval' 			=> 'hourly',
				'styles_disable'    		=> 0,
				'title_link'				=> 1,
				'title_limit'				=> '',
				'source_enable'     		=> 1,
				'text_preceding_source' 	=> 'Source:',
				'date_enable'				=> 1,
				'text_preceding_date' 		=> 'Published on',

				// from version 3.1
				'limit_feed_items_imported' => 200,

				// from version 3.3
				'custom_feed_url'			=> 'wprss',
				'custom_feed_limit'			=> '',
				'source_link'				=> 0,
				
				// from version 3.4
				'video_link'				=> 'false',

				// from version 3.8
				'limit_feed_items_age'		=> '',
				'limit_feed_items_age_unit'	=> 'days',

				// tracking
				'tracking'					=> 0,
			)			
		);

		// Return the default settings
		return $settings;
	}



	/**
	 * Returns the default tracking settings.
	 * 
	 * @since 3.6
	 */
	function wprss_get_default_tracking_settings() {
		return apply_filters(
			'wprss_default_tracking_settings',
			array(
				'use_presstrends'				=>	'false',
				'tracking_notice'				=>	''
			)
		);
	}