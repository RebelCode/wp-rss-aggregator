<?php

namespace Aventura\Wprss\Licensing;
use \Aventura\Wprss\Licensing\License\Status;

/**
 * Gets the singleton instance of the AjaxController class, creating it if it doesn't exist.
 * 	
 * @return AjaxController
 */
function get_ajax_controller() {
	static $instance = null;
	return is_null( $instance )? $instance = new AjaxController() : $instance;
}

/**
 * AJAX controller class for licensing AJAX operations.
 */
class AjaxController {

	// Pattern for ajax handler methods
	const AJAX_MANAGE_LICENSE_METHOD_PATTERN = 'handleAjaxLicense%s';

	protected $_manager;
	protected $_settings;

	public function __construct() {
		$this->_settings = get_settings();
		$this->_manager = get_manager();
		$this->_setupHooks();
	}

	protected function _setupHooks() {
		add_action( 'wp_ajax_wprss_ajax_manage_license', $this->_method( 'handleAjaxManageLicense' ) );
		add_action( 'wp_ajax_wprss_ajax_fetch_license', $this->_method( 'handleAjaxFetchLicense' ) );
	}

	/**
	 * Echoes an AJAX error response along with activate/deactivate license button HTML markup and then dies.
	 * 
	 * @param  string $message The error message to send.
	 * @param  string $addonId Optional addon ID related to the error, used for sending the activate/deactivate license button.
	 */
	protected function _sendErrorResponse( $message, $addonId = '' ) {
		$response = array(
			'error'		=>	$msg,
			'html'		=>	$this->_settings->getActivateLicenseButtonHtml($addonId)
		);

		echo json_encode( $response );
		die();
	}

	/**
	 * Handles AJAX requests for managing licenses (activation, deactivation, etc..)
	 */
	public function handleAjaxManageLicense() {
		// Get data from request
		$nonce = empty( $_GET['nonce'] )? null : sanitize_text_field( $_GET['nonce'] );
		$addon = empty( $_GET['addon'] )? null : sanitize_text_field( $_GET['addon'] );
		$event = empty( $_GET['event'] )? null : sanitize_text_field( $_GET['event'] );
		$licenseKey = empty( $_GET['license'] )? null : sanitize_text_field( $_GET['license'] );

		// If no nonce, stop
		if ( $nonce === null ) $this->_sendErrorResponse( __( 'No nonce', WPRSS_TEXT_DOMAIN ), $addon );
		// Generate the nonce id
		$nonce_id = sprintf( 'wprss_%s_license_nonce', $addon );
		// Verify the nonce. If verification fails, stop
		if ( ! wp_verify_nonce( $nonce, $nonce_id ) ) {
			$this->_sendErrorResponse( __( 'Bad nonce', WPRSS_TEXT_DOMAIN ), $addon );
		}

		// Check addon, event and license
		if ( $addon === null ) $this->_sendErrorResponse( __( 'No addon ID', WPRSS_TEXT_DOMAIN ) );
		if ( $event === null ) $this->_sendErrorResponse( __( 'No event specified', WPRSS_TEXT_DOMAIN ), $addon );
		if ( $licenseKey === null ) $this->_sendErrorResponse( __( 'No license', WPRSS_TEXT_DOMAIN ), $addon );

		// Check if the license key was obfuscated on the client's end.
		if ( $this->_settings->isLicenseKeyObfuscated( $licenseKey ) ) {
			// If so, use the stored license key since obfuscation signifies that the key was not modified
			// and is equal to the one saved in db
			$licenseKey = $this->_manager->getLicense( $addon );
		} else {
			// Otherwise, update the value in db
			$license = $this->_manager->getLicense( $addon );
			if ( $license === null ) {
				$license = $this->_manager->createLicense( $addon );
			}
			$license->setKey( $licenseKey );
			$this->_manager->saveLicenseKeys();
		}

		// uppercase first letter of event
		$event = ucfirst( $event );
		// Generate method name
		$eventMethod = sprintf( self::AJAX_MANAGE_LICENSE_METHOD_PATTERN, $event );
		// check if the event is handle-able
		if ( ! method_exists( $this, $eventMethod ) ) {
			$this->_sendErrorResponse( __( 'Invalid event specified', WPRSS_TEXT_DOMAIN ), $addon);
		}

		// Call the appropriate handler method
		$returnValue = call_user_func_array( $this->_method( $eventMethod ), array( $addon ) );

		// Prepare the response
		$partialResponse = array(
			'addon'				=>	$addon,
			'html'				=>	$this->_settings->getActivateLicenseButtonHtml( $addon ),
			'licensedAddons'	=>	array_keys( $this->_manager->getLicensesWithStatus( Status::VALID ) )
		);
		// Merge the returned value(s) from the handler method to generate the final resposne
		$response = array_merge( $partialResponse, $returnValue );

		// Return the JSON data.
		echo json_encode( $response );
		die();
	}

	/**
	 * Handles the AJAX request to fetch license information.
	 */
	public function handleAjaxFetchLicense() {
		// If not addon ID in the request, stop
		if ( empty( $_GET['addon']) )
			$this->_sendErrorResponse( __( 'No addon ID', WPRSS_TEXT_DOMAIN ) );
		// Get and sanitize the addon ID
		$addon = sanitize_text_field( $_GET['addon'] );
		// Get the license information from EDD
		$response = $this->_manager->checkLicense( $addon, 'ALL' );
		// Send response as JSON
		echo json_encode( $response );
		die();
	}

	/**
	 * Handles the activation AJAX request to activate the license for the given addon.
	 * 
	 * @param  string $addonId The addon ID.
	 * @return array           The data to add to the AJAX response, containing the license status after activation.
	 */
	public function handleAjaxLicenseActivate( $addonId ) {
		return array(
			'validity'	=>	$this->_manager->normalizeLicenseApiStatus( $this->_manager->activateLicense( $addonId ) )
		);
	}

	/**
	 * Handles the deactivation AJAX request to deactivate the license for the given addon.
	 * 
	 * @param  string $addonId The addon ID.
	 * @return array           The data to add to the AJAX response, containing the license status after activation.
	 */
	public function handleAjaxLicenseDeactivate( $addonId ) {
		return array(
			'validity'	=>	$this->_manager->normalizeLicenseApiStatus( $this->_manager->deactivateLicense( $addonId ) )
		);
	}

	protected function _method( $method ) {
		return array( $this, $method );
	}

}
