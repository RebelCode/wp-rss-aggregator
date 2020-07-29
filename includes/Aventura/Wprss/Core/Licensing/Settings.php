<?php

namespace Aventura\Wprss\Core\Licensing;
use \Aventura\Wprss\Core\Licensing\License\Status;
use \WPRSS_MBString;

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
        $this->_setManager( wprss_licensing_get_manager() );
        // Only load notices if on admin side
        if ( is_main_site() && is_admin() ) {
            $this->_initNotices();
        }
    }

    /**
     * Sets the license manager for this settings controller to use.
     *
     * @param \Aventura\Wprss\Core\Licensing\Manager $manager An instance of the license manager.
     * @return \Aventura\Wprss\Core\Licensing\Settings This instance.
     */
    protected function _setManager(Manager $manager) {
        $this->_manager = $manager;
        return $this;
    }

    /**
     * Gets the license manager for this settings controller.
     *
     * @return \Aventura\Wprss\Core\Licensing\Manager The license manager used by this settings controller.
     */
    public function getManager() {
        return $this->_manager;
    }

    /**
     * Initializes the admin notices.
     *
     * @return \Aventura\Wprss\Core\Licensing\Settings
     */
    protected function _initNotices() {
        $factory = wprss_core_container()->get(sprintf('%sfactory', WPRSS_SERVICE_ID_PREFIX));
        $noticesComponent = wprss()->getAdminAjaxNotices();

        foreach ( $this->getManager()->getAddons() as $_addonId => $_addonName ) {
            $_year = date('Y');
            $emptyLicenseNotice = $factory->make(sprintf('%saddon_empty_license', WPRSS_NOTICE_SERVICE_ID_PREFIX), array(
                'addon_id'    => $_addonId,
                'addon_name'  => $_addonName,
                'settings'    => $this
            ));
            $noticesComponent->addNotice($emptyLicenseNotice);

            $inactiveLicenseNotice = $factory->make(sprintf('%saddon_inactive_license', WPRSS_NOTICE_SERVICE_ID_PREFIX), array(
                'addon_id'    => $_addonId,
                'addon_name'  => $_addonName,
                'settings'    => $this
            ));
            $noticesComponent->addNotice($inactiveLicenseNotice);

            $expiringLicenseNotice = $factory->make(sprintf('%saddon_expiring_license', WPRSS_NOTICE_SERVICE_ID_PREFIX), array(
                'addon_id'    => $_addonId,
                'addon_name'  => $_addonName,
                'settings'    => $this,
                'year'        => $_year
            ));
            $noticesComponent->addNotice($expiringLicenseNotice);
        }

        return $this;
    }

    /**
     * Condition callback for the "invalid license notice".
     *
     * @return boolean True if the notice is to be shown, false if not.
     */
    public function emptyLicenseKeyNoticeCondition( $args ) {
        if (!current_user_can('manage_options')) {
            return false;
        }

        if ( ! isset( $args['addon'] ) ) {
            return false;
        }

        if (!is_main_site()) {
            return false;
        }

        $license = $this->getManager()->getLicense( $args['addon'] );

        return is_null($license) || strlen( $license->getKey() ) === 0;
    }


    /**
     * Condition callback for the "inactive saved license" notice.
     *
     * @return boolean True if the notice is to be shown, false if not.
     */
    public function savedInactiveLicenseNoticeCondition( $args ) {
        if (!current_user_can('manage_options')) {
            return false;
        }

        if ( ! isset( $args['addon'] ) ) {
            return false;
        }
        $license = $this->getManager()->getLicense( $args['addon'] );
        return $license !== null && strlen( $license->getKey() ) > 0 && ! $license->isValid();
    }


    /**
     * Condition callback for the "soon to expire license" notice.
     *
     * @return boolean True if the notice is to be shown, false if not.
     */
    public function soonToExpireLicenseNoticeCondition( $args ) {
        if (!current_user_can('manage_options')) {
            return false;
        }

        if ( ! isset( $args['addon'] ) ) return false;
                $manager = $this->getManager();
                if ( !($license = $manager->getLicense( $args['addon'] )) ) {
                    return false;
                }
        return $license->isValid() && $manager->isLicenseExpiring($args['addon']);
    }


    /**
     * Registers the WordPress settings.
     */
    public function registerSettings() {
        // Iterate all addon IDs and register a settings section with 2 fields for each.
        foreach( $this->getManager()->getAddons() as $_addonId => $_addonName ) {
            // Settings Section
            add_settings_section(
                sprintf( 'wprss_settings_%s_licenses_section', $_addonId ),
                sprintf( '%s %s', $_addonName, __( 'License', WPRSS_TEXT_DOMAIN ) ),
                '__return_empty_string',
                'wprss_settings_license_keys'
            );
            // License key field
            add_settings_field(
                sprintf( 'wprss_settings_%s_license', $_addonId ),
                __( 'License key', WPRSS_TEXT_DOMAIN ),
                array( $this, 'renderLicenseKeyField' ),
                'wprss_settings_license_keys',
                sprintf( 'wprss_settings_%s_licenses_section', $_addonId ),
                array( $_addonId )
            );
            // Activate license button
            add_settings_field(
                sprintf( 'wprss_settings_%s_activate_license', $_addonId ),
                __( 'Activate license', WPRSS_TEXT_DOMAIN ),
                array( $this, 'renderActivateLicenseButton' ),
                'wprss_settings_license_keys',
                sprintf( 'wprss_settings_%s_licenses_section', $_addonId ),
                array( $_addonId )
            );
        }

        return $this;
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
        $license = $this->getManager()->getLicense( $addonId );
        // Mask it - if the license exists
        $displayedKey = is_null( $license )? '' : self::obfuscateLicenseKey( $license->getKey() );
        // Render the markup ?>
        <input id="wprss-<?php echo $addonId ?>-license-key" name="wprss_settings_license_keys[<?php echo $addonId ?>_license_key]"
               class="wprss-license-input" type="text" value="<?php echo esc_attr( $displayedKey ) ?>" style="width: 300px;"
        />
        <label class="description" for="wprss-<?php echo $addonId ?>-license-key">
            <?php _e( 'Enter your license key', WPRSS_TEXT_DOMAIN ) ?>
        </label><?php
    }


    /**
     * Masks a license key.
     *
     * @param  string  $licenseKey        The license key to mask
     * @param  string  $maskChar          The masking character(s)
     * @param  integer $maskExcludeAmount The amount of characyers to exclude from the mask. If negative, the exluded characters will begin from the end of the string
     * @return string                     The masked license key
     */
    public static function obfuscateLicenseKey( $licenseKey, $maskChar = self::LICENSE_KEY_MASK_CHAR, $maskExcludeAmount = self::LICENSE_KEY_MASK_EXCLUDE_AMOUNT ) {
        // Pre-calculate license key length
        $licenseKeyLength = strlen( $licenseKey );
        // In case the mask exclude amount is greater than the license key length
        $actualMaskExcludeAmount = abs( $maskExcludeAmount ) > ( $licenseKeyLength - 1 )
                ? ( $licenseKeyLength - 1 ) * ( $maskExcludeAmount < 0 ? -1 : 1 ) // Making sure to preserve position of mask
                : $maskExcludeAmount;
        // How many chars to mask. Always at least one char will be masked.
        $maskLength = $licenseKeyLength - abs( $actualMaskExcludeAmount );
        // Create the mask
        $mask = $maskLength > 0 ? str_repeat( $maskChar, $maskLength ) : '';
        // The starting index: if negative mask exclude amount, start from the back. otherwise start from 0
        $startIndex = $actualMaskExcludeAmount < 0 ? $maskLength : 0;
        // Extract the excluded characters
        $excludedChars = WPRSS_MBString::mb_substr( $licenseKey, $startIndex, abs( $actualMaskExcludeAmount ) );
        // Generate the displayed key and return it
        return sprintf( $actualMaskExcludeAmount > 0 ? '%1$s%2$s' : '%2$s%1$s', $excludedChars, $mask );
    }

    /**
     * Determines whether or not the license key in question is obfuscated.
     *
     * This is achieved by searching for the mask character in the key. Because the
     * mask character cannot be a valid license character, the presence of at least
     * one such character indicates that the key is obfuscated.
     *
     * @param string $key The license key in question.
     * @param string $maskChar The masking character(s).
     * @return bool Whether or not this key is obfuscated.
     */
    public function isLicenseKeyObfuscated( $key, $maskChar = self::LICENSE_KEY_MASK_CHAR ) {
        return WPRSS_MBString::mb_strpos( $key, $maskChar ) !== false;
    }


    /**
     * Invalidates the key if it is obfuscated, causing the saved version to be used.
     * This meanst that the new key will not be saved, as it is considered then to be unchanged.
     *
     * @since 4.6.10
     * @param bool $is_valid Indicates whether the key is currently considered to be valid.
     * @param string $key The license key in question
     * @return Whether or not the key is still to be considered valid.
     */
    public function validateLicenseKeyForSave( $is_valid, $key ) {
        if ( $this->isLicenseKeyObfuscated( $key ) )
            return false;

        return $is_valid;
    }

    /**
     * Renders the activate/deactivate license button for a particular add-on.
     *
     * @since 4.4.5
     */
    public function renderActivateLicenseButton( $args ) {
        $addonId = $args[0];
        $manager = $this->getManager();
        $data = $manager->checkLicense( $addonId, 'ALL' );
        $data = empty($data) ? 'invalid' : $data;
        $status = is_string( $data ) ? $data : $data->license;
        if ( $status === 'site_inactive' ) $status = 'inactive';
        if ( $status === 'item_name_mismatch' ) $status = 'invalid';

        $valid = $status == 'valid';
        $btnText = $valid ? 'Deactivate license' : 'Activate license';
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
                $license = $manager->getLicense( $addonId );
                if ( $license !== null && !$license->isInvalid() && ($licenseKey = $license->getKey()) && !empty( $licenseKey ) ) :
                    if ( is_object( $data ) ) :
                        $currentActivations = $data->site_count;
                        $activationsLeft = $data->activations_left;
                        $activationsLimit = $data->license_limit;
                        $expires = $data->expires;
                        $expiresSpace = strpos($expires, ' ');
                        // if expiry has space, get only first word
                        $expires = ( $expiresSpace !== false ) ? substr( $expires, 0, $expiresSpace ) : $expires;
                        $expires = trim($expires);
                        // change lifetime expiry to never
                        $expires = ($expires === Manager::EXPIRATION_LIFETIME) ? __('never', WPRSS_TEXT_DOMAIN) : $expires;

                        // If the license key is garbage, don't show any of the data.
                        if ( !empty($data->payment_id) && !empty($data->license_limit ) ) :
                        ?>
                        <small>
                            <?php if ( $status !== 'valid' && $activationsLeft === 0 ) : ?>
                                <?php $accountUrl = 'https://www.wprssaggregator.com/account/?action=manage_licenses&payment_id=' . $data->payment_id; ?>
                                <a href="<?php echo $accountUrl; ?>"><?php _e("No activations left. Click here to manage the sites you've activated licenses on.", WPRSS_TEXT_DOMAIN); ?></a>
                                <br/>
                            <?php endif; ?>
                            <?php if ( !empty($expires) && $expires !== 'never' && strtotime($expires) < strtotime("+2 weeks") ) : ?>
                                <?php $renewalUrl = esc_attr(WPRSS_SL_STORE_URL . '/checkout/?edd_license_key=' . $licenseKey); ?>
                                <a href="<?php echo $renewalUrl; ?>"><?php _e('Renew your license to continue receiving updates and support.', WPRSS_TEXT_DOMAIN); ?></a>
                                <br/>
                            <?php endif; ?>
                            <strong><?php _e('Activations', WPRSS_TEXT_DOMAIN); ?>:</strong>
                                <?php echo $currentActivations.'/'.$activationsLimit; ?> (<?php echo $activationsLeft; ?> left)
                            <br/>
                            <?php if ( !empty($expires) ) : ?>
                            <strong><?php _e('Expires', WPRSS_TEXT_DOMAIN); ?>:</strong>
                                <code><?php echo $expires; ?></code>
                            <br/>
                            <?php endif; ?>
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
        $manager = $this->getManager();
        $addons = $manager->getAddons();

        // Get for each registered addon
        foreach( $addons as $id => $name ) {
            // listen for our activate button to be clicked
            if(  isset( $_POST["wprss_{$id}_license_activate"] ) || isset( $_POST["wprss_{$id}_license_deactivate"] ) ) {
                // run a quick security check
                if ( ! check_admin_referer( "wprss_{$id}_license_nonce", "wprss_{$id}_license_nonce" ) )
                    continue; // get out if we didn't click the Activate/Deactivate button
            }

            // retrieve the license
            $license = $manager->getLicense( $id );

            // If the license is not saved in DB, but is included in POST
            if ( $license == '' && ! empty( $_POST['wprss_settings_license_keys'][$id.'_license_key'] ) ) {
                // Use the license given in POST
                $license->setKey( $_POST['wprss_settings_license_keys'][ $id.'_license_key' ] );
            }

            // Prepare the action to take
            if ( isset( $_POST["wprss_{$id}_license_activate"] ) ) {
                $manager->activateLicense( $id );
            }
            elseif ( isset( $_POST["wprss_{$id}_license_deactivate"] ) ) {
                $manager->deactivateLicense( $id );
            }
        }

        return $this;
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

}
