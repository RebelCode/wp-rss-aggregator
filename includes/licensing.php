<?php

/**
 * Gets the singleton instance of the Settings class, creating it if it doesn't exist.
 *
 * @since [*next-version*]
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
 * @since [*next-version*]
 * @return Aventura\Wprss\Core\Licensing\Manager
 */
function wprss_licensing_get_manager() {
	static $manager = null;

	if ( is_null( $manager ) ) {
        $manager = new Aventura\Wprss\Core\Licensing\Manager();
        $manager->setExpirationNoticePeriod( wprss_get_general_setting( 'expiration_notice_period' ) );
        $manager->setDefaultAuthorName( 'Jean Galea' );
    }

    return $manager;
}

/**
 * Gets the singleton instance of the AjaxController class, creating it if it doesn't exist.
 *
 * @since [*next-version*]
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
 * @param bool $noCache If true, the add-ons filter will be ran again; if false, it will only be ran if not ran before.
 * @return array Array of add-on codes.
 */
function wprss_get_addons($noCache = false) {
	static $addons = null;
	return is_null( $addons ) || $noCache
            ? $addons = apply_filters( 'wprss_register_addon', array() )
            : $addons;
}
