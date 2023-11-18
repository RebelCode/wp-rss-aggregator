<?php

if (!defined('ABSPATH')) {
    die;
}

const WPRSS_DID_GUIDE_SIGN_UP_OPTION = 'wprss_did_guide_sign_up';
const WPRSS_DL_GUIDE_NONCE = 'wprss_dl_guide';

/* Show the notice for the free guide to new users. */
add_action('admin_notices', function () {
    $page = isset($_GET['page']) ? $_GET['page'] : '';

    if (!wprss_is_wprss_page() || $page === 'wpra-intro') {
        return;
    }

    $didSignUp = filter_var(get_option(WPRSS_DID_GUIDE_SIGN_UP_OPTION, '0'), FILTER_VALIDATE_BOOLEAN);
    if ($didSignUp || count(wprss_get_addons()) > 0) {
        return;
    }

    $user = wp_get_current_user();
    $userName = $user instanceof WP_User ? $user->display_name : '';
    $userEmail = $user instanceof WP_User ? $user->user_email : '';

    ?>
    <div id="wpra-dl-guide-notice" class="wpra-dl-guide-notice notice">
        <div class="wpra-dl-guide-left">
            <img src="<?= esc_attr(WPRSS_IMG . 'wpra-icon-transparent-new.png') ?>" alt="WP RSS Aggregator logo" />
        </div>
        <div class="wpra-dl-guide-content">
            <div id="wpra-dl-guide-success">
                <h3>Thank you for subscribing!</h3>
                <p>
                    Your FREE expert guide to content aggregation and curation is on its way to your inbox.
                </p>
                <p>
                    Get ready to boost your website's content game and make a lasting impact!
                </p>
                <a href="javascript:void(0)" id="wpra-dl-guide-dismiss-link">Dismiss notice</a>
            </div>

            <div class="wpra-dl-guide-content-col wpra-dl-guide-content-col-left">
                <h3>Boost Your Content Game: Download Your FREE Starter Guide Now</h3>
                <p>
                    Join our newsletter and get your FREE guide for mastering content aggregation and curation on
                    your website.
                </p>
            </div>

            <div class="wpra-dl-guide-content-col wpra-dl-guide-content-col-right">
                <input id="wpra_dl_guide_nonce" type="hidden" value="<?= wp_create_nonce(WPRSS_DL_GUIDE_NONCE) ?>" />
                <label for="wpra-dl-guide-name-field">
                    By unlocking this guide, you’re opting in to receiving occasional emails. Opt out any time.
                </label>
                <div class="wpra-dl-guide-form">
                    <div class="wpra-dl-guide-fields">
                        <input
                            id="wpra-dl-guide-name-field"
                            type="text"
                            autocomplete="name"
                            placeholder="First name"
                            name="wpra_guide_name"
                            value="<?= esc_attr($userName) ?>"
                        />
                        <input
                            id="wpra-dl-guide-email-field"
                            type="email"
                            autocomplete="email"
                            placeholder="Email address"
                            name="wpra_guide_email"
                            value="<?= esc_attr($userEmail) ?>"
                        />
                    </div>
                    <button id="wpra-dl-guide-btn">Download the free guide</button>
                </div>
                <div id="wpra-dl-guide-error"></div>
            </div>
        </div>
        <div class="wpra-dl-guide-right">
            <button id="wpra-dl-guide-close-btn">
                <i class="dashicons dashicons-no-alt"></i>
            </button>
        </div>
    </div>
    <?php
});

// Add admin ajax handler for when the user signs up with their email address
add_action('wp_ajax_wpra_dl_guide', function () {
    $nonce = isset($_POST['nonce']) ? $_POST['nonce'] : '';
    if (!wp_verify_nonce($nonce, WPRSS_DL_GUIDE_NONCE)) {
        wp_send_json_error('Invalid nonce. Try refreshing the page.', 401);
        die;
    }

    $name = trim(isset($_POST['name']) ? $_POST['name'] : '');
    $email = trim(isset($_POST['email']) ? $_POST['email'] : '');
    $email = filter_var($email, FILTER_VALIDATE_EMAIL);

    if (!$name) {
        wp_send_json_error('Please enter your first name.', 400);
        die;
    }

    if (!$email) {
        wp_send_json_error('Invalid email address.', 400);
        die;
    }

    wprss_sub_to_newsletter($name, $email);
    update_option(WPRSS_DID_GUIDE_SIGN_UP_OPTION, '1');

    wp_send_json_success([]);
    die;
});

// Add admin ajax handler for when the user dismisses the notice
add_action('wp_ajax_wpra_dismiss_guide', function () {
    $nonce = isset($_POST['nonce']) ? $_POST['nonce'] : '';
    if (!wp_verify_nonce($nonce, WPRSS_DL_GUIDE_NONCE)) {
        wp_send_json_error('Invalid nonce. Try refreshing the page.', 401);
        die;
    }

    update_option(WPRSS_DID_GUIDE_SIGN_UP_OPTION, '1');

    wp_send_json_success([]);
    die;
});
