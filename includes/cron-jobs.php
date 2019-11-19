<?php

define('WPRA_FETCH_ALL_FEEDS_HOOK', 'wprss_fetch_all_feeds_hook');
define('WPRA_FETCH_FEED_HOOK', 'wprss_fetch_single_feed_hook');
define('WPRA_TRUNCATE_ITEMS_HOOK', 'wprss_truncate_posts_hook');
define('WPRA_ACTIVATE_FEED_HOOK', 'wprss_activate_feed_schedule_hook');
define('WPRA_PAUSE_FEED_HOOK', 'wprss_pause_feed_schedule_hook');

define('WPRA_TRUNCATE_ITEMS_INTERVAL', 'daily');

/**
 * Alias for add_action, primarily used for readability to distinguish between cron-events and normal hooks.
 *
 * @since [*next-version*]
 *
 * @param string   $cron     The cron hook event.
 * @param callable $callback The callback to invoke for the cron.
 */
function wpra_on_cron_do($cron, $callback)
{
    add_action($cron, $callback);
}

// Cron events
wpra_on_cron_do(WPRA_FETCH_ALL_FEEDS_HOOK, 'wprss_fetch_insert_all_feed_items_from_cron');
wpra_on_cron_do(WPRA_TRUNCATE_ITEMS_HOOK, 'wprss_truncate_posts');
wpra_on_cron_do(WPRA_TRUNCATE_ITEMS_HOOK, 'wprss_activate_feed_source', 10, 1);
wpra_on_cron_do(WPRA_PAUSE_FEED_HOOK, 'wprss_pause_feed_source', 10, 1);

// Initialize crons that must always be scheduled
add_action('init', 'wpra_init_crons');

// When a feed source is activated, schedule its fetch cron
add_action('wprss_on_feed_source_activated', 'wprss_feed_source_update_start_schedule');

// When a feed source is paused, cancel its fetch cron
add_action('wprss_on_feed_source_paused', 'wprss_feed_source_update_stop_schedule');

// Filter the possible cron intervals to add more options
add_filter('cron_schedules', 'wprss_filter_cron_schedules');

/**
 * Initializes the cron jobs.
 *
 * @since [*next-version*]
 */
function wpra_init_crons()
{
    wprss_schedule_fetch_all_feeds_cron();
    wprss_schedule_truncate_posts_cron();
}

/**
 * Creates the cron to fetch feeds.
 *
 * @since 2.0
 */
function wprss_schedule_fetch_all_feeds_cron()
{
    // Check if the global fetch is scheduled
    if (wp_next_scheduled(WPRA_FETCH_ALL_FEEDS_HOOK)) {
        return;
    }

    // If the event is not scheduled, schedule it
    $interval = wprss_get_general_setting('cron_interval');
    wp_schedule_event(time(), $interval, WPRA_FETCH_ALL_FEEDS_HOOK);
}

/**
 * Creates the cron to truncate wprss_feed_item posts daily
 *
 * @since 2.0
 */
function wprss_schedule_truncate_posts_cron()
{
    // Check if the truncatation cron is scheduled
    if (wp_next_scheduled(WPRA_TRUNCATE_ITEMS_HOOK)) {
        return;
    }

    // If not, schedule it
    wp_schedule_event(time(), WPRA_TRUNCATE_ITEMS_INTERVAL, WPRA_TRUNCATE_ITEMS_HOOK);
}

/**
 * Updates the feed processing cron job schedules.
 * Removes the current schedules and adds the ones in the feed source's meta.
 *
 * @since 3.8
 *
 * @param int $feed_id The id of the wprss_feed
 */
function wprss_update_feed_processing_schedules($feed_id)
{
    // Get the feed's activate and pause times
    $activate = get_post_meta($feed_id, 'wprss_activate_feed', true);
    $pause = get_post_meta($feed_id, 'wprss_pause_feed', true);

    // Parse as time strings
    $activate = wprss_strtotime($activate);
    $pause = wprss_strtotime($pause);

    if (!empty($activate)) {
        wpra_reschedule($activate, WPRA_ACTIVATE_FEED_HOOK, null, [$feed_id]);
    }

    if ($pause !== '') {
        wpra_reschedule($pause, WPRA_PAUSE_FEED_HOOK, null, [$feed_id]);
    }
}

/**
 * Starts the looping schedule for a feed source. Runs on a schedule
 *
 * @since 3.9
 *
 * @param int $feed_id The ID of the feed source
 */
function wprss_feed_source_update_start_schedule($feed_id)
{
    // Stop any currently scheduled update operations
    wprss_feed_source_update_stop_schedule($feed_id);

    // Get the interval
    $interval = get_post_meta($feed_id, 'wprss_update_interval', true);
    // Do nothing if the feed source has no update interval (not sure if possible) or if the interval
    // is set to global
    if ($interval === '' || $interval === wprss_get_default_feed_source_update_interval()) {
        return;
    }

    wp_schedule_event(time(), $interval, WPRA_FETCH_FEED_HOOK, [strval($feed_id)]);
}

/**
 * Stops any scheduled update operations for a feed source. Runs on a schedule.
 *
 * @since 3.9
 *
 * @param int $feed_id The ID of the feed source ( wprss_feed )
 */
function wprss_feed_source_update_stop_schedule($feed_id)
{
    $timestamp = wprss_get_next_feed_source_update($feed_id);

    // If a schedule exists, unschedule it
    if ($timestamp !== false) {
        wp_unschedule_event($timestamp, WPRA_FETCH_FEED_HOOK, [strval($feed_id)]);
    }
}

/**
 * Returns the timestamp for the next feed source update
 *
 * @since 3.9
 *
 * @param int $feed_id The ID of the feed source ( wprss_feed )
 *
 * @return int The timestamp of the next update operation, or false is no
 *          update is scheduled.
 */
function wprss_get_next_feed_source_update($feed_id)
{
    return wp_next_scheduled(WPRA_FETCH_FEED_HOOK, [strval($feed_id)]);
}

/**
 * Reschedules a cron event, unscheduling any existing matching crons.
 *
 * @since [*next-version*]
 *
 * @param int         $timestamp  The timestamp.
 * @param string      $event      The hook event.
 * @param string|null $recurrence The recurrence.
 * @param array       $args       Additional args.
 */
function wpra_reschedule($timestamp, $event, $recurrence = null, $args = [])
{
    $existing = wp_next_scheduled($event, $args);

    if ($existing !== false) {
        wp_unschedule_event($existing, $event, $args);
    }

    if ($recurrence === null) {
        wp_schedule_single_event($timestamp, $event, $args);
    } else {
        wp_schedule_event($timestamp, $recurrence, $event, $args);
    }
}

/**
 * Adding a few more handy cron schedules to the default ones
 *
 * @since 3.0
 */
function wprss_filter_cron_schedules($schedules)
{
    $frequencies = array(
        'five_min' => array(
            'interval' => 5 * MINUTE_IN_SECONDS,
            'display' => __('Once every five minutes', WPRSS_TEXT_DOMAIN),
        ),
        'ten_min' => array(
            'interval' => 10 * MINUTE_IN_SECONDS,
            'display' => __('Once every ten minutes', WPRSS_TEXT_DOMAIN),
        ),
        'fifteen_min' => array(
            'interval' => 15 * MINUTE_IN_SECONDS,
            'display' => __('Once every fifteen minutes', WPRSS_TEXT_DOMAIN),
        ),
        'thirty_min' => array(
            'interval' => 30 * MINUTE_IN_SECONDS,
            'display' => __('Once every thirty minutes', WPRSS_TEXT_DOMAIN),
        ),
        'two_hours' => array(
            'interval' => 2 * HOUR_IN_SECONDS,
            'display' => __('Once every two hours', WPRSS_TEXT_DOMAIN),
        ),
    );

    return array_merge($schedules, $frequencies);
}

/**
 * Deletes a custom cron schedule.
 *
 * Credits: WPCrontrol
 *
 * @since 3.7
 *
 * @param string $name The internal_name of the schedule to delete.
 */
function wprss_delete_schedule($name)
{
    $scheds = get_option('crontrol_schedules', array());
    unset($scheds[$name]);
    update_option('crontrol_schedules', $scheds);
}

/**
 * Parses the date time string into a UTC timestamp.
 * The string must be in the format: m/d/y h:m:s
 *
 * @since 3.9
 */
function wprss_strtotime($str)
{
    if (empty($str)) {
        return 0;
    }

    $parts = explode(' ', $str);
    $date = explode('/', $parts[0]);
    $time = explode(':', $parts[1]);

    return mktime($time[0], $time[1], $time[2], $date[1], $date[0], $date[2]);
}

/**
 * Returns the default value for the per feed source update interval
 *
 * @since 3.9
 */
function wprss_get_default_feed_source_update_interval()
{
    return 'global';
}
