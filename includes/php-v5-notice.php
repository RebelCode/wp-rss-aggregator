<?php

define('WPRSS_PHP_NOTICE_DISMISSED_OPTION', 'wprss_php_notice_dismissed', false);
define('WPRSS_PHP_NOTICE_VERSION', '7.4.0', false);

add_action('plugins_loaded', function () {
    if (version_compare(PHP_VERSION, WPRSS_PHP_NOTICE_VERSION, '<')) {
        $option = get_option(WPRSS_PHP_NOTICE_DISMISSED_OPTION, '0');
        if ($option === '0') {
            add_action('admin_notices', 'wprss_php_upgrade_notice');
        }
    }
});

function wprss_php_upgrade_notice()
{
    $icon = WPRSS_IMG . 'wpra-icon-transparent.png';
    $nonce = wp_create_nonce('wpra-dismiss-php-notice');

    ?>
        <div class="notice wpra-php-notice"> 
            <input type="hidden" class="wpra-php-notice-nonce" value="<?= esc_attr($nonce) ?>" />

            <div class="wpra-php-notice-left">
                <img src="<?= esc_attr($icon) ?>" style="width: 32px !important" alt="WP RSS Aggregator" />
            </div>
            <div class="wpra-php-notice-right">
                <h3>Important Notice: WP RSS Aggregator Update Ahead!</h3>
                <p>
                    <?php
                    printf(
                        _x(
                            'WP RSS Aggregator is getting a full revamp, including a fresh new look! We\'re excited to share it with you, but before we do so, this major update will require your website to be running PHP 7.4 or higher. Please make sure you upgrade your PHP version by the end of October 2023. You may contact your hosting provider to assist you with the upgrade. If you have any questions, our %s is always available.',
                            '%s = "support team"',
                            'wprss'
                        ),
                        sprintf('<a href="mailto:support@wprssaggregator.com" rel="noopener noreferrer">%s</a>', __('support team', 'wprss'))
                    );
                    ?>
                </p>
            </div>
            <button class="wpra-php-notice-close">
                <span class="dashicons dashicons-no-alt" />
            </button>
        </div>
    <?php
}

add_action('wp_ajax_wprss_dismiss_php_notice', function () {
    update_option(WPRSS_PHP_NOTICE_DISMISSED_OPTION, '1');
    echo "OK";
    die();
});
