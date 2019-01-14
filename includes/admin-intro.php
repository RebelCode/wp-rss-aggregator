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
