<?php

if (!defined('ABSPATH')) {
    die;
}

define('WPRSS_INTRO_STEP_OPTION', 'wprss_intro_step');
define('WPRSS_INTRO_NONCE_NAME', 'wprss_intro_nonce');
define('WPRSS_INTRO_STEP_POST_PARAM', 'wprss_intro_step');

/**
 * AJAX handler for setting the introduction step the user has reached.
 *
 * @since [*next-version*]
 */
add_action('wp_ajax_wprss_set_intro_step', function () {
    check_ajax_referer(WPRSS_INTRO_NONCE_NAME, 'nonce');
    if (!current_user_can('manage_options')) {
        wp_die('', '', [
            'response' => 403,
        ]);
    }

    $step = filter_input(INPUT_POST, WPRSS_INTRO_STEP_POST_PARAM, FILTER_VALIDATE_INT);

    if ($step === null) {
        wprss_ajax_error_response(
            sprintf(__('Missing intro step param "%s"', WPRSS_TEXT_DOMAIN), WPRSS_INTRO_STEP_POST_PARAM)
        );
    }

    wprss_set_intro_step($step);
    wprss_ajax_success_response([
        'wprss_intro_step' => $step,
    ]);
});

/**
 * Retrieves the step the user has reached in the introduction.
 *
 * @since [*next-version*]
 *
 * @return int
 */
function wprss_get_intro_step()
{
    return get_option(WPRSS_INTRO_STEP_OPTION, 0);
}

/**
 * Sets the step the user has reached in the introduction.
 *
 * @since [*next-version*]
 *
 * @param int $step A positive integer.
 */
function wprss_set_intro_step($step)
{
    update_option(WPRSS_INTRO_STEP_OPTION, max($step, 0));
}

/**
 * Sends an AJAX success response.
 *
 * @since [*next-version*]
 *
 * @param array $data   Optional data to send.
 * @param int   $status Optional HTTP status code of the response.
 */
function wprss_ajax_success_response($data = [], $status = 200)
{
    echo json_encode([
        'success' => true,
        'error' => '',
        'data' => $data,
        'status' => $status,
    ]);
    wp_die();
}

/**
 * Sends an AJAX success response.
 *
 * @since [*next-version*]
 *
 * @param string $message Optional error message.
 * @param int    $status  Optional HTTP status code of the response.
 */
function wprss_ajax_error_response($message, $status = 400)
{
    echo json_encode([
        'success' => false,
        'error' => $message,
        'data' => [],
        'status' => $status,
    ]);
    wp_die();
}
