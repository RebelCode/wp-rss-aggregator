<?php
	/**
	 * Version check and update functionality.
	 */

	/* Hook our version check to 'init'. */
	add_action( 'init', 'wprss_version_check' );

	/**
	 * Checks the version number and runs install or update functions if needed.
	 *
	 * @since 1.2
	 */
	function wprss_version_check() {

		/* Get the old database version. */
		$old_db_version = get_option( 'wprss_db_version' );

		/* Get the plugin settings. */
		$settings = get_option( 'wprss_settings' );

		/* If there is no old database version, run the install. */
		if ( empty( $old_db_version ) && false === $settings )
			wprss_install();

		/* Temporary check b/c version 1.1 didn't have an upgrade path. */
		elseif ( empty( $old_db_version ) && !empty( $settings ) )
			wprss_update();

		/* If the old version is less than the new version, run the update. */
		elseif ( intval( $old_db_version ) < intval( WPRSS_DB_VERSION ) )
			wprss_update();
	}

	/**
	 * Adds the plugin settings on install.
	 *
	 * @since 1.2
	 */
	function wprss_install() {

		/* Add the database version setting. */
		add_option( 'wprss_db_version', WPRSS_DB_VERSION );

		/* Add the default plugin settings. */
		add_option( 'wprss_settings', wprss_get_default_settings() );
	}

	/**
	 * Updates plugin settings if there are new settings to add.
	 *
	 * @since 1.2
	 */
	function wprss_update() {

		/* Update the database version setting. */
		update_option( 'wprss_db_version', WPRSS_DB_VERSION );

		/* Get the settings from the database. */
		$settings = get_option( 'wprss_settings' );

		/* Get the default plugin settings. */
		$default_settings = wprss_get_default_settings();

		/* Loop through each of the default plugin settings. */
		foreach ( $default_settings as $setting_key => $setting_value ) {

			/* If the setting didn't previously exist, add the default value to the $settings array. */
			if ( !isset( $settings[$setting_key] ) )
				$settings[$setting_key] = $setting_value;
		}

		/* Update the plugin settings. */
		update_option( 'wprss_settings', $settings );
	}

	/**
	 * Returns an array of the default plugin settings.  These are only used on initial setup.
	 *
	 * @since 1.2
	 */
	function wprss_get_default_settings() {

		/* Set up the default plugin settings. */
		$settings = array(

			// Version 1.1
			'open_dd' => 'New window',
			'follow_dd' => 'No follow'			
		);

		/* Return the default settings. */
		return $settings;
	}

?>