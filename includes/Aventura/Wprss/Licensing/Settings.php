<?php

namespace Aventura\Wprss\Licensing;

function get_settings() {
	static $instance = null;
	return is_null( $instance )? $instance = new Settings() : $instance;
}

/**
 * The licensing settings class.
 *
 * Manages registraion, rendering and validation of the licensing settings as well as handling AJAX requests
 * from the client. This class uses the Manager class internally to retrieve, update and manage licenses.
 */
class Settings {

	/**
	 * What to print in place of license code chars.
	 * This must not be a symbol that is considered to be a valid license key char.
	 * 
	 * @since 4.6.10
	 */
	const LICENSE_KEY_MASK_CHAR = '•';

	/**
	 * How many characters of the license code to print as is.
	 * Use negative value to indicate that characters at the end of the key are excluded.
	 * 
	 * @since 4.6.10
	 */
	const LICENSE_KEY_MASK_EXCLUDE_AMOUNT = -4;

	/**
	 * The Licensing Manager instance.
	 * 
	 * @var Manager
	 */
	protected $_manager;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->_manager = get_manager();
		$this->_setupHooks();
	}

	/**
	 * Registers the WordPress settings.
	 */
	public function registerSettings() {
		$addons = \wprss_get_addons();
		foreach( $addons as $addonId => $addonName ) {
			// Settings Section
			add_settings_section(
				sprintf( 'wprss_settings_%s_licenses_section', $addonId ),
				sprintf( '%s %s', $addon_name, __( 'License', WPRSS_TEXT_DOMAIN ) ),
				'__return_empty_string',
				'wprss_settings_license_keys'
			);
			// License key field
			add_settings_field(
				sprintf( 'wprss_settings_%s_license', $addonId ),
				__( 'License Key', WPRSS_TEXT_DOMAIN ),
				$this->method( 'renderLicenseKeyField' ),
				'wprss_settings_license_keys',
				sprintf( 'wprss_settings_%s_licenses_section', $addonId ),
				array( $addonId )
			);
			// Activate license button
			add_settings_field(
				sprintf( 'wprss_settings_%s_activate_license', $addonId ),
				__( 'Activate License', WPRSS_TEXT_DOMAIN ),
				$this->method( 'renderActivateLicenseButton' ),
				'wprss_settings_license_keys',
				sprintf( 'wprss_settings_%s_licenses_section', $addonId ),
				array( $addonId )
			);
		}
	}

	/**
	 * Renders the license field for a particular add-on.
	 *
	 * @since 4.4.5
	 */
	public function renderLicenseKeyField( $args ) {
		if ( count( $args ) < 1 ) return;
		// Addon ID is the first arg
		$addonId = $args[0];
		// Get the addon's license
		$license = $this->_manager->getLicense( $addonId );
		$licenseKey = $license->getKey();
		$licenseKeyLen = strlen( $licenseKey );
		// Masking character
		$maskChar = self::LICENSE_KEY_MASK_CHAR;
		// How many chars to show
		$maskExcludeAmount = self::LICENSE_KEY_MASK_EXCLUDE_AMOUNT;
		// In case the mask exclude amount is greater than the license key length
		$maskExcludeAmount = abs( $maskExcludeAmount ) > ( $licenseKeyLength - 1 )
				? ( $licenseKeyLength - 1 ) * ( $maskExcludeAmount < 0 ? -1 : 1 ) // Making sure to preserve position of mask
				: $maskExcludeAmount;
		// How many chars to mask. Always at least one char will be masked.
		$maskLength = $licenseKeyLength - abs( $maskExcludeAmount );
		// Create the mask
		$mask = $maskLength > 0 ? str_repeat( $maskChar, $maskLength ) : '';
		// The starting index: if negative mask exclude amount, start from the back. otherwise start from 0
		$startIndex = $maskExcludeAmount < 0 ? $maskLength : 0;
		// Extract the excluded characters
		$excludedChars = WPRSS_MBString::mb_substr( $licenseKey, $startIndex, abs( $maskExcludeAmount ) );
		// Generate the displayed key
		$displayedKey = sprintf( $maskExcludeAmount > 0 ? '%1$s%2$s' : '%2$s%1$s', $excludedChars, $mask );
		// Render the markup ?>
		<input id="wprss-<?php echo $addonId ?>-license-key" name="wprss_settings_license_keys[<?php echo $addonId ?>_license_key]"
			   type="text" value="<?php echo esc_attr( $displayedKey ) ?>" style="width: 300px;"
		/>
		<label class="description" for="wprss-<?php echo $addonId ?>-license-key">
			<?php _e( 'Enter your license key', WPRSS_TEXT_DOMAIN ) ?>
		</label><?php
	}

	/**
	 * Renders the activate/deactivate license button for a particular add-on.
	 *
	 * @since 4.4.5
	 */
	public function renderActivateLicenseButton( $args ) {
		$addonId = $args[0];
		$data = $this->_manager->checkLicense( $addonId, 'ALL' );
		$status = is_string( $data ) ? $data : $data->license;
		if ( $status === 'site_inactive' ) $status = 'inactive';
		if ( $status === 'item_name_mismatch' ) $status = 'invalid';

		$valid = $status == 'valid';
		$btnText = $valid ? 'Deactivate License' : 'Activate License';
		$btnName = "wprss_{$addonId}_license_" . ( $valid? 'deactivate' : 'activate' );
		$btnClass = "button-" . ( $valid ? 'deactivate' : 'activate' ) . "-license";
		wp_nonce_field( "wprss_{$addonId}_license_nonce", "wprss_{$addonId}_license_nonce", false ); ?>

		<input type="button" class="<?php echo $btnClass; ?> button-process-license button-secondary" name="<?php echo $btnName; ?>" value="<?php _e( $btnText, WPRSS_TEXT_DOMAIN ); ?>" />
		<span id="wprss-<?php echo $addonId; ?>-license-status-text">
			<strong><?php _e('Status', WPRSS_TEXT_DOMAIN); ?>:
			<span class="wprss-<?php echo $addonId; ?>-license-<?php echo $status; ?>">
					<?php _e( ucfirst($status), WPRSS_TEXT_DOMAIN ); ?>
					<?php if ( $status === 'valid' ) : ?>
						<i class="fa fa-check"></i>
					<?php elseif( $status === 'invalid' || $status === 'expired' ): ?>
						<i class="fa fa-times"></i>
					<?php elseif( $status === 'inactive' ): ?>
						<i class="fa fa-warning"></i>
					<?php endif; ?>
				</strong>
			</span>
		</span>

		<p>
			<?php
				$licenseKey = $this->_manager->getLicense( $addonId );
				if ( ! empty( $licenseKey ) ) :
					if ( is_object( $data ) ) :
						$currentActivations = $data->site_count;
						$activationsLeft = $data->activations_left;
						$activationsLimit = $data->license_limit;
						$expires = $data->expires;
						$expires = substr( $expires, 0, strpos( $expires, " " ) );

						// If the license key is garbage, don't show any of the data.
						if ( !empty($data->payment_id) && !empty($data->license_limit ) ) :
						?>
						<small>
							<?php if ( $status !== 'valid' && $activationsLeft === 0 ) : ?>
								<?php $accountUrl = 'https://www.wprssaggregator.com/account/?action=manage_licenses&payment_id=' . $data->payment_id; ?>
								<a href="<?php echo $accountUrl; ?>"><?php _e("No activations left. Click here to manage the sites you've activated licenses on.", WPRSS_TEXT_DOMAIN); ?></a>
								<br/>
							<?php endif; ?>
							<?php if ( strtotime($expires) < strtotime("+2 weeks") ) : ?>
								<?php $renewalUrl = esc_attr(WPRSS_SL_STORE_URL . '/checkout/?edd_license_key=' . $licenseKey); ?>
								<a href="<?php echo $renewalUrl; ?>"><?php _e('Renew your license to continue receiving updates and support.', WPRSS_TEXT_DOMAIN); ?></a>
								<br/>
							<?php endif; ?>
							<strong><?php _e('Activations', WPRSS_TEXT_DOMAIN); ?>:</strong>
								<?php echo $currentActivations.'/'.$activationsLimit; ?> (<?php echo $activationsLeft; ?> left)
							<br/>
							<strong><?php _e('Expires on', WPRSS_TEXT_DOMAIN); ?>:</strong>
								<code><?php echo $expires; ?></code>
							<br/>
							<strong><?php _e('Registered to', WPRSS_TEXT_DOMAIN); ?>:</strong>
								<?php echo $data->customer_name; ?> (<code><?php echo $data->customer_email; ?></code>)
						</small>
						<?php endif; ?>
					<?php else: ?>
						<small><?php _e('Failed to get license information. This is a temporary problem. Check your internet connection and try again later.', WPRSS_TEXT_DOMAIN); ?></small>
					<?php endif; ?>
				<?php endif;
			?>
		</p>

		<style type="text/css">
			.wprss-<?php echo $addonId; ?>-license-valid {
				color: green;
			}
			.wprss-<?php echo $addonId; ?>-license-invalid, .wprss-<?php echo $addonId; ?>-license-expired {
				color: #b71919;
			}
			.wprss-<?php echo $addonId; ?>-license-inactive {
				color: #d19e5b;
			}
			#wprss-<?php echo $addonId; ?>-license-status-text {
				margin-left: 8px;
				line-height: 27px;
				vertical-align: middle;
			}
		</style>
		<?php
	}

	/**
	 * Handles the activation/deactivation process
	 *
	 * @since 1.0
	 */
	public function handleLicenseStatusChange() {
		$addons = wprss_get_addons();

		// Get for each registered addon
		foreach( $addons as $id => $name ) {
			// listen for our activate button to be clicked
			if(  isset( $_POST["wprss_{$id}_license_activate"] ) || isset( $_POST["wprss_{$id}_license_deactivate"] ) ) {
				// run a quick security check
				if ( ! check_admin_referer( "wprss_{$id}_license_nonce", "wprss_{$id}_license_nonce" ) )
					continue; // get out if we didn't click the Activate/Deactivate button
			}

			// retrieve the license
			$license = $this->_manager->getLicense( $id );

			// If the license is not saved in DB, but is included in POST
			if ( $license == '' && ! empty( $_POST['wprss_settings_license_keys'][$id.'_license_key'] ) ) {
				// Use the license given in POST
				$license->setKey( $_POST['wprss_settings_license_keys'][ $id.'_license_key' ] );
			}

			// Prepare the action to take
			if ( isset( $_POST["wprss_{$id}_license_activate"] ) ) {
				$this->_manager->activateLicense( $id )
			}
			elseif ( isset( $_POST["wprss_{$id}_license_deactivate"] ) ) {
				$this->_manager->deactivateLicense( $id );
			}
		}
	}

	/**
	 * Gets the HTML markup for the activate license button.
	 * 
	 * @param  string $addonId The ID of the addon for which the button will be related to.
	 * @return string          The HTML markup of the button.
	 */
	public function getActivateLicenseButtonHtml( $addonId ) {
		ob_start();
		$this->renderActivateLicenseButton( array( $addonId ) );
		return ob_get_clean();
	}

	protected function _method( $method ) {
		return array( $this, $method );
	}

	protected function _setupHooks() {
		add_action( 'wprss_admin_init', $this->_method( 'registerSettings' ), 100 );
		add_action( 'admin_init', $this->method( 'handleLicenseStatusChange', 10 );
	}
	
}
