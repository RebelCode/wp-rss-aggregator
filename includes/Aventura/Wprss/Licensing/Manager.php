<?php

namespace Aventura\Wprss\Licensing;
use \Aventura\Wprss\Licensing\License\Status;

/**
 * Gets the singleton instance of the Manager class, creating it if it doesn't exist.
 * 	
 * @return Manager
 */
function get_manager() {
	static $instance = null;
	return ( $instance === null )? $instance = new Manager() : $instance;
}

/**
 * Manager class for license handling.
 *
 * @version 1.0
 * @since [<next-version>]
 */
class Manager {

	// The default updater class
	const DEFAULT_UPDATER_CLASS = '\EDD_SL_Plugin_Updater';

	// Name of license keys option in DB
	const DB_LICENSE_KEYS_OPTION_NAME = 'wprss_settings_license_keys';
	// Name of license statuses option in DB
	const DB_LICENSE_STATUSES_OPTION_NAME = 'wprss_settings_license_statuses';
	// Regex pattern for keys in license option
	const DB_LICENSE_KEYS_OPTION_PATTERN = '%s_license_key';
	// Regex pattern for statuses in license option
	const DB_LICENSE_STATUSES_OPTION_PATTERN = '%s_license_%s';

	// The time before expiration during which a notice will be displayed to the user, informing them that a license will expire soon
	const EXPIRATION_NOTICE_PERIOD = '2 weeks';

	/**
	 * License instance.
	 * 
	 * @var array
	 */
	protected $_licenses;

	/**
	 * The class to use for updating addons.
	 * 
	 * @var string
	 */
	protected static $_updaterClass = self::DEFAULT_UPDATER_CLASS;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->_loadLicenses();
		$this->_setupHooks();
		$this->_construct();
	}

	/**
	 * Internal secondary constructor, used by classes that extend this class.
	 */
	protected function _construct() {}

	/**
	 * Sets up the WordPress hooks.
	 */
	protected function _setupHooks() {
		add_action( 'admin_init', $this->method( 'initUpdaterInstances' ) );
	}

	/**
	 * Gets the name of the class used to update the addons.
	 * 
	 * @return string
	 */
	public static function getUpdaterClass() {
		return self::$_updaterClass;
	}

	/**
	 * Sets the class used to update the addons.
	 * 
	 * @param  string $updaterClass The name of the updater class.
	 * @return self
	 */
	public static function setUpdaterClass( $updaterClass ) {
		self::$_updaterClass = $updaterClass;
		return self;
	}

	/**
	 * Creates a new license.
	 *
	 * This does not save the license to the database. You will need to call Manager::saveLicenses() to save to db.
	 * 
	 * @param  string  $addonId The addon ID
	 * @return License          The created license
	 */
	public function createLicense( $addonId ) {
		return $this->_licenses[ $addonId ] = new License();
	}

	/**
	 * Gets all license key settings.
	 * 
	 * @return array
	 */
	public function getLicenses() {
		return $this->_licenses;
	}

	/**
	 * Gets the license key for a specific addon.
	 * 
	 * @param  string $addonId The addon id.
	 * @return array
	 */
	public function getLicense( $addonId ) {
		if ( ! $this->licenseExists( $addonId ) ) {
			return null;
		}
		return $this->_licenses[ $addonId ];
	}

	/**
	 * Checks if an addon license key
	 * 
	 * @param  string  $addonId The addon id
	 * @return boolean          True if the license key for the given addon id exists, false if not.
	 */
	public function licenseExists( $addonId ) {
		return isset( $this->_licenses[ $addonId ] );
	}

	/**
	 * Gets all licenses with the given status.
	 * 
	 * @param  string  $status   The status to search for.
	 * @param  boolean $negation If true, the method will search for licenses that do NOT have the given status.
	 *                           If false, the method will search for licenses with the given status.
	 *                           Default: false
	 * @return array             An array of matching licenses. Array keys contain the addon IDs and array values contain the license objects.
	 */
	public function getLicensesWithStatus( $status, $negation = false ) {
		$licenses = array();
		foreach ( $this->_licenses as $_addonId => $_license ) {
			if ( $_license->getStatus() === $status xor $negation === true ) {
				$licenes[ $_addonId ] = $_license;
			}
		}
		return $licenses;
	}

	/**
	 * Checks if a license with the given status exists, stopping at the first match.
	 * 
	 * @param  string  $status   The status to search for.
	 * @param  boolean $negation If true, the method will search for licenses that do NOT have the given status.
	 *                           If false, the method will search for licenses with the given status.
	 *                           Default: false
	 * @return boolean           True if a license with or without (depending on $negation) the given status exists, false otherwise.
	 */
	public function licenseWithStatusExists( $status, $negation = true ) {
		return count( $this->getLicensesWithStatus( $status, $negation ) ) > 0;
	}

	/**
	 * Gets the licenses that are soon to be expired.
	 *
	 * @uses self::_calculateSteTimestamp To calculate the minimum date for classifying licenses as "soon-to-expire".
	 * 
	 * @return array An assoc array with addon IDs as array keys and License instances as array values.
	 */
	public function getExpiringLicenses() {
		// Calculate soon-to-expiry (ste) date
		$ste = self::_calculateSteTimestamp();
		// Prepare the list
		$expiringLicences = array();
		// Iterate all licenses
		foreach ( $this->_licenses as $addonId => $license ) {
			// Get expiry
			$expires = $license->getExpiry();
			// Split using space and get first part only (date only)
			$parts = explode( ' ', $expires );
			$dateOnly = strtotime( $parts[0] );
			// Check if the expiry date is zero, or is within the expiration notice period
			if ( $dateOnly == 0 || $dateOnly < $ste ) {
				$expiringLicences[ $addonId ] = $license;
			}
		}
		return $expiringLicences;
	}

	/**
	 * Checks if there are licenses that will soon expire.
	 *
	 * @uses self::_calculateSteTimestamp To calculate the minimum date for classifying licenses as "soon-to-expire".
	 * 
	 * @return bool True if there are licenses that will soon expire, false otherwise.
	 */
	public function expiringLicensesExist() {
		return count( $this->getExpiringLicenses() ) > 0;
	}

	/**
	 * Activates an add-on's license.
	 *
	 * @uses self::sendApiRequest() Sends the request with $action set as 'activate_license'.
	 * 
	 * @param  string $addonId The ID of the addon.
	 * @param  string $return  What to return from the response.
	 * @return mixed
	 */
	public function activateLicense( $addonId, $return = 'license') {
		return $this->sendApiRequest( $addonId, 'activate_license', $return );
	}

	/**
	 * Deactivates an add-on's license.
	 *
	 * @uses self::sendApiRequest() Sends the request with $action set as 'deactivate_license'.
	 * 
	 * @param  string $addonId The ID of the addon.
	 * @param  string $return  What to return from the response.
	 * @return mixed
	 */
	public function deactivateLicense( $addonId, $return = 'license') {
		return $this->sendApiRequest( $addonId, 'deactivate_license', $return );
	}

	/**
	 * Checks an add-on's license's status with the server.
	 *
	 * @uses self::sendApiRequest() Sends the request with $action set as 'check_license'.
	 * 
	 * @param  string $addonId The ID of the addon.
	 * @param  string $return  What to return from the response.
	 * @return mixed
	 */
	public function checkLicense( $addonId, $return = 'license') {
		return $this->sendApiRequest( $addonId, 'check_license', $return );
	}

	/**
	 * Calls the EDD Software Licensing API to perform licensing tasks on the addon's store server.
	 *
	 * @param string $addonId The ID of the addon
	 * @param string $action  The action to perform on the license.
	 * @param string $return  What to return from the response. If 'ALL', the entire license status object is returned,
	 *                        Otherwise, the property with name $return will be returned, or null if it doesn't exist.
	 * @return mixed
	 */
	public function sendApiRequest( $addonId, $action = 'check_license', $return = 'license' ) {
		// Get the license for the addon
		$license = $this->getLicense( $addonId );
		// Use blank license if addon license does not exist
		if ( $license === null ) {
			$license = new License();
		}

		// Addon Uppercase ID
		$addonUid = strtoupper( $addonId );
		// Prepare constants
		$itemName = constant( "WPRSS_{$addonUid}_SL_ITEM_NAME" );
		$storeUrl = constant( "WPRSS_{$addonUid}_SL_STORE_URL" );

		// data to send in our API request
		$apiParams = array(
			'edd_action'	=> $action,
			'license'		=> sanitize_text_field( $license->getKey() ),
			'item_name'		=> urlencode( $itemName ),
			'url'			=> urlencode( network_site_url() ),
			'time'			=> time(),
		);

		// Send the request to the API
		$response = wp_remote_get( add_query_arg( $apiParams, $storeUrl ) );

		// If the response is an error, return the value in the DB
		if ( is_wp_error( $response ) ) {
			wprss_log( sprintf( 'Licensing API request failed: %1$s', $response->get_error_message() ), __FUNCTION__, WPRSS_LOG_LEVEL_WARNING );
			return $license->getStatus();
		}

		// decode the license data
		$licenseData = json_decode( wp_remote_retrieve_body( $response ) );
		
		// Could not decode response JSON
		if ( is_null( $licenseData ) ) {
			wprss_log( sprintf( 'Licensing API: Failed to decode response JSON' ), __FUNCTION__, WPRSS_LOG_LEVEL_WARNING );
			return $license->getStatus();
		}

		// Update the DB option
		$license->setStatus( $licenseData->license );
		$license->setExpiry( $licenseData->expires );
		$this->saveLicenseStatuses();

		// Return the data
		if ( strtoupper( $return ) === 'ALL' ) {
			return $licenseData;
		} else {
			return isset( $licenseData->{$return} ) ? $licenseData->{$return} : null;
		}
	}

	/**
	 * Sets up the EDD updater for all registered add-ons.
	 *
	 * @since 4.6.3
	 */
	public function initUpdaterInstances() {
		// Stop if doing autosave or ajax
		if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) return;

		// Get all registered addons
		$addons = wprss_get_addons();

		// Get the updater class
		$updaterClass = self::getUpdaterClass();

		// setup the updater
		if ( !class_exists( 'EDD_SL_Plugin_Updater' ) ) {
			// load our custom updater
			include ( WPRSS_INC . 'libraries/EDD_licensing/EDD_SL_Plugin_Updater.php' );
		}

		// Iterate the addons
		foreach( $addons as $id => $name ) {
			// Prepare the data
			$license = $this->getLicense( $id );
			// If the addon doesn't have a license or the license is not valid, skip this addon
			if ( $license === null || $license->getStatus() !== Status::VALID ) continue;
			$uid = strtoupper( $id );
			$name = constant("WPRSS_{$uid}_SL_ITEM_NAME");
			$version = constant("WPRSS_{$uid}_VERSION");
			$path = constant("WPRSS_{$uid}_PATH");
			// Set up an updater
			$eddUpdater = new $updaterClass( WPRSS_SL_STORE_URL, $path, array(
				'version'   =>	$version,				// current version number
				'license'   =>	$license->getKey(),		// license key (used get_option above to retrieve from DB)
				'item_name' =>	$name,					// name of this plugin
				'author'    =>	'Jean Galea'			// author of this plugin
			));
		}
	}

	/**
	 * Normalizes the license status received by the API into the license statuses that we use locally in our code.
	 * 
	 * @param  string $status The status to normalize.
	 * @return string         The normalized status.
	 */
	public function normalizeLicenseApiStatus( $status ) {
		if ( $status === 'site_inactive' ) $status = 'inactive';
		if ( $status === 'item_name_mismatch' ) $status = 'invalid';
		return $status;
	}

	/**
	 * Loads the licenses from db and prepares the internal licenses array
	 */
	protected function _loadLicenses() {
		$this->_licenses = array();
		$options = self::_normalizeLicenseOptions( self::_getLicenseKeysDbOption(), self::_getLicenseStatusesDbOption() );
		foreach ( $options as $addonId => $_data ) {
			$this->_licenses[ $addonId ] = new License( $_data );
		}
	}

	/**
	 * Saves the licenses and their statuses to the db.
	 */
	public function _saveLicenses() {
		$this->saveLicenseKeys();
		$this->saveLicenseStatuses();
	}

	/**
	 * Saves the license keys to the db.
	 */
	public function saveLicenseKeys() {
		$keys = array();
		foreach ( $this->_licenses as $_addonId => $_license ) {
			$_key = sprintf( self::DB_LICENSE_KEYS_OPTION_PATTERN, $_addonId );
			$keys[ $_key ] = $_license->getKey();
		}
		update_option( self::DB_LICENSE_KEYS_OPTION_NAME, $keys );
	}

	/**
	 * Saves the license statuses (and expirations) to the db.
	 */
	public function saveLicenseStatuses() {
		$statuses = array();
		foreach ( $this->_licenses as $_addonId => $_license ) {
			$_status = sprintf( self::DB_LICENSE_STATUSES_OPTION_PATTERN, $_addonId, 'status' );
			$_expires = sprintf( self::DB_LICENSE_STATUSES_OPTION_PATTERN, $_addonId, 'expires' );
			$statuses[ $_status ] = $_license->getStatus();
			$statuses[ $_expires ] = $_license->getExpiry();
		}
		update_option( self::DB_LICENSE_STATUSES_OPTION_NAME, $statuses );
	}

	/**
	 * Retrieves the licenses keys db option.
	 * 
	 * @return array
	 */
	protected static function _getLicenseKeysDbOption() {
		return get_option( self::DB_LICENSE_KEYS_OPTION_NAME, array() );
	}

	/**
	 * Retrieves the licenses statuses db option.
	 * 
	 * @return array
	 */
	protected static function _getLicenseStatusesDbOption() {
		return get_option( self::DB_LICENSE_STATUSES_OPTION_NAME, array() );
	}

	/**
	 * Normalizes the given db options into a format that the Manager can use to compile a list of License instances.
	 * 
	 * @return array
	 */
	protected static function _normalizeLicenseOptions( $keys, $statuses ) {
		// Prepare result array
		$normalized = array();
		// Prepare regex pattern outside of iterations
		$licenseKeysOptionPattern = self::_formatStringToDbOptionPattern( self::DB_LICENSE_KEYS_OPTION_PATTERN );
		$licenseStatusesOptionPattern = self::_formatStringToDbOptionPattern( self::DB_LICENSE_STATUSES_OPTION_PATTERN );
		
		// Prepare the license keys into the normalized array
		foreach ( $keys as $_key => $_value ) {
			// Regex match for pattern of array keys
			preg_match( $licenseKeysOptionPattern, $_key, $_matches );
			if ( count( $_matches ) < 2 ) continue;
			// Addon id is the first match (excluding whole string match at $_matches[0])
			$_addonId = $_matches[1];
			// check if entry for add-on exists in normalized array, otherwise create it
			if ( ! isset( $normalized[ $_addonId ] ) )
				$normalized[ $_addonId ] = array();
			// Insert the license key inot the normalized array
			$normalized[ $_addonId ][ 'key' ] = $_value;
		}
		// Now iterate and insert the statuses
		foreach ( $statuses as $_key => $_value ) {
			// Regex match for pattern of array keys
			preg_match( $licenseStatusesOptionPattern, $_key, $_matches );
			// continue to next iteration if there are no matches
			if ( count( $_matches ) < 3 ) continue;
			// The addon ID: first match
			$_addonId = $_matches[1];
			// Property name: second match
			$_property = $_matches[2];
			// if entry for add-on doesn't exist in normalized array, continue
			if ( ! isset( $normalized[ $_addonId ] ) ) continue;
			// Add the property to the normalized array for the addon's entry
			$normalized[ $_addonId ][ $_property ] = $_value;
		}
		
		return $normalized;
	}

	/**
	 * Converts the given format string into a regex patter, replacing all instances of '%s' with
	 * '([^_]+)'. The pattern can be used by PHP regex functions to match db license options.
	 * 
	 * @param  string $formatString
	 * @return string
	 */
	protected static function _formatStringToDbOptionPattern( $formatString ) {
		return sprintf( '/\\A%s\\Z/', str_replace( '%s', '([^_]+)', $formatString ) );
	}

	/**
	 * Calculates the "soon-to-expire" timestamp.
	 *
	 * @uses self::EXPIRATION_NOTICE_PERIOD
	 * @return integer The timestamp for a future date, for which addons whose license's expiry lies between this date and the present are considered "soon-to-expire".
	 */
	protected static function _calculateSteTimestamp() {
		return strtotime( sprintf( '+%s', self::EXPIRATION_NOTICE_PERIOD ) );
	}

	/**
	 * Creates a callable array for this instance and the method with the given name.
	 * 
	 * @param  string $name The method name
	 * @return array
	 */
	public function method( $name ) {
		return array( $this, $name );
	}

}
