<?php

/**
 * Gets the singleton instance of the Settings class, creating it if it doesn't exist.
 *
 * @return Aventura\Wprss\Core\Licensing\Settings
 */
function wprss_licensing_get_settings() {
	static $instance = null;
	return is_null( $instance )
            ? $instance = new Aventura\Wprss\Core\Licensing\Settings()
            : $instance;
}


/**
 * Gets the singleton instance of the Manager class, creating it if it doesn't exist.
 *
 * @return Aventura\Wprss\Core\Licensing\Manager
 */
function wprss_licensing_get_manager() {
	static $instance = null;
	return is_null( $instance )
            ? $instance = new Aventura\Wprss\Core\Licensing\Manager()
            : $instance;
}

/**
 * Gets the singleton instance of the AjaxController class, creating it if it doesn't exist.
 *
 * @return Aventura\Wprss\Core\Licensing\AjaxController
 */
function wprss_licensing_get_ajax_controller() {
	static $instance = null;
	return is_null( $instance )
            ? $instance = new Aventura\Wprss\Core\Licensing\AjaxController()
            : $instance;
}

/**
 * Returns all registered addons.
 *
 * @since 4.4.5
 */
function wprss_get_addons($noCache = false) {
	static $addons = null;
	return is_null( $addons ) || $noCache
            ? $addons = apply_filters( 'wprss_register_addon', array() )
            : $addons;
}
