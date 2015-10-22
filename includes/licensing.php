<?php

add_action( 'init', function() {
	require( WPRSS_INC . 'Aventura\\Wprss\\Licensing\\Manager.php' );
	require( WPRSS_INC . 'Aventura\\Wprss\\Licensing\\Settings.php' );
	require( WPRSS_INC . 'Aventura\\Wprss\\Licensing\\AjaxController.php' );
	require( WPRSS_INC . 'Aventura\\Wprss\\Licensing\\License.php' );
	require( WPRSS_INC . 'Aventura\\Wprss\\Licensing\\License\Status.php' );

	\Aventura\Wprss\Licensing\get_settings();
	\Aventura\Wprss\Licensing\get_manager();
	\Aventura\Wprss\Licensing\get_ajax_controller();
} );

/**
 * Gets the singleton instance of the Settings class, creating it if it doesn't exist.
 * 	
 * @return Aventura\Wprss\Core\Licensing\Settings
 */
function wprss_get_licensing_settings() {
	static $instance = null;
	return is_null( $instance )? $instance = new Aventura\Wprss\Core\Licensing\Settings() : $instance;
}


/**
 * Gets the singleton instance of the Manager class, creating it if it doesn't exist.
 * 	
 * @return Aventura\Wprss\Core\Licensing\Manager
 */
function wprss_get_licensing_manager() {
	static $instance = null;
	return ( $instance === null )? $instance = new Aventura\Wprss\Core\Licensing\Manager() : $instance;
}

/**
 * Gets the singleton instance of the AjaxController class, creating it if it doesn't exist.
 * 	
 * @return Aventura\Wprss\Core\Licensing\AjaxController
 */
function wprss_get_licensing_ajax_controller() {
	static $instance = null;
	return is_null( $instance )? $instance = new Aventura\Wprss\Core\Licensing\AjaxController() : $instance;
}

/**
 * Returns all registered addons.
 *
 * @since 4.4.5
 */
}
