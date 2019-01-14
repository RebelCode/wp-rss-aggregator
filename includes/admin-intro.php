<?php

if (!defined('ABSPATH')) {
    die;
}

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
