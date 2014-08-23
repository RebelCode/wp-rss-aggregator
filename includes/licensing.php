<?php


/**
 * Calls the EDD Software Licensing API to perform licensing tasks on the addon's store server.
 *
 * @since 4.4.5
 */
function wprss_edd_licensing_api( $addon, $license_key = NULL, $action = 'check_license' ) {
	// If no license argument was given
	if ( $license_key === NULL ) {
		// Get the license key
		$license_key = wprss_get_license_key( $addon );
	}
	// Get the license status from the DB
	$license_status = wprss_get_license_status( $addon );

	// Prepare constants
	$item_name = strtoupper( $addon );
	$item_name_constant = constant( "WPRSS_{$item_name}_SL_ITEM_NAME" );
	$store_url_constant = constant( "WPRSS_{$item_name}_SL_STORE_URL" );

	// data to send in our API request
	$api_params = array(
		'edd_action'	=> $action,
		'license'		=> $license_key,
		'item_name'		=> urlencode( $item_name_constant )
	);

	// Call the custom API.
	$response = wp_remote_get( add_query_arg( $api_params, $store_url_constant ) );

	// If the response is an error, return the value in the DB
	if ( is_wp_error( $response ) ) return $license_status;

	// decode the license data
	$license_data = json_decode( wp_remote_retrieve_body( $response ) );

	// Update the DB option
	$license_statuses["{$addon}_license_status"] = $license_data->license;
	update_option( 'wprss_settings_license_statuses', $license_statuses );

	// Return TRUE if it is 'active', FALSE otherwise
	return $license_data->license;
}


/**
 * Returns the license status. Also updates the status in the DB.
 *
 * @since 4.4.5
 */
function wprss_edd_check_license( $addon, $license_key = NULL ) {
	return wprss_edd_licensing_api( $addon, $license_key, 'check_license' );
}


/**
 * Activates an addon's license.
 *
 * @since 4.4.5
 */
function wprss_edd_activate_license( $addon, $license_key = NULL ) {
	return wprss_edd_licensing_api( $addon, $license_key, 'activate_license' );
}


/**
 * Deactivates an addon's license.
 *
 * @since 4.4.5
 */
function wprss_edd_deactivate_license( $addon, $license_key = NULL ) {
	return wprss_edd_licensing_api( $addon, $license_key, 'deactivate_license' );
}


/**
* Returns an array of the default license settings. Used for plugin activation.
*
* @since 4.4.5
*
*/
function wprss_default_license_settings( $addon ) {
	// Set up the default license settings
	$settings = apply_filters(
		'wprss_default_license_settings',
		array(
			"{$addon}_license_key"		=> FALSE,
			"{$addon}_license_status"	=> 'invalid'
		)
	);

	// Return the default settings
	return $settings;
}

/**
 * Returns the saved license code.
 *
 * @since 4.4.5
 */
function wprss_get_license_key( $addon ) {
	// Get default and current options
	$defaults = wprss_default_license_settings( $addon );
	$keys = get_option( 'wprss_settings_license_keys', array() );
	// Prepare the array key and target
	$k = "{$addon}_license_key";
	// Return the appropriate value
	return isset( $keys["{$addon}_license_key"] )? $keys[$k] : $defaults[$k];
}


/**
 * Returns the saved license code.
 *
 * @since 4.4.5
 */
function wprss_get_license_status( $addon ) {
	// Get the default and current options
	$defaults = wprss_default_license_settings( $addon );
	$statuses = get_option( 'wprss_settings_license_statuses', array() );
	// Prepare the key
	$k = "{$addon}_license_status";
	// Return the appropriate value
	return isset( $statuses["{$addon}_license_status"] )? $statuses[$k] : $defaults[$k];
}


/**
 * Returns the license status. Also updates the status in the DB.
 *
 * @since 2.9.6
 */
function wprss_check_edd_license_status( $addon ) {
	// Get the license key
	$license_key = wprss_get_license_key( $addon );
	// Get the license status from the DB
	$license_status = wprss_get_license_status( $addon );

	// Prepare constants
	$item_name = strtoupper( $addon );
	$item_name_constant = constant( "WPRSS_{$item_name}_SL_ITEM_NAME" );
	$store_url_constant = constant( "WPRSS_{$item_name}_SL_STORE_URL" );

	// data to send in our API request
	$api_params = array(
		'edd_action'	=> 'check_license',
		'license'		=> $license_key,
		'item_name'		=> urlencode( $item_name_constant )
	);

	// Call the custom API.
	$response = wp_remote_get( add_query_arg( $api_params, $store_url_constant ) );

	// If the response is an error, return the value in the DB
	if ( is_wp_error( $response ) ) return $license_status;

	// decode the license data
	$license_data = json_decode( wp_remote_retrieve_body( $response ) );

	// Update the DB option
	$license_statuses["{$addon}_license_status"] = $license_data->license;
	update_option( 'wprss_settings_license_statuses', $license_statuses );

	// Return TRUE if it is 'active', FALSE otherwise
	return $license_data->license;
}
