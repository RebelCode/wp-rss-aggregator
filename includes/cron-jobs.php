<?php
    /**
     * Contains all the cron jobs in use by WP RSS Aggregator
     *
     * @package WPRSSAggregator
     */


    add_action( 'init', 'wprss_schedule_fetch_all_feeds_cron' );
    /**
     * Creates the cron to fetch feeds every hour
     *
     * @since 2.0
     */
    function wprss_schedule_fetch_all_feeds_cron() {

        $options = get_option( 'wprss_settings_general' );

        $cron_interval = $options['cron_interval'];

        // verify event has not been scheduled
        if ( ! wp_next_scheduled( 'wprss_fetch_all_feeds_hook' ) ) {
            // Schedule to run hourly
            wp_schedule_event( time(), $cron_interval, 'wprss_fetch_all_feeds_hook' );
        }

        add_action( 'wprss_fetch_all_feeds_hook', 'wprss_fetch_insert_all_feed_items_from_cron' );
    }


    add_action( 'init', 'wprss_schedule_truncate_posts_cron' );
    /**
     * Creates the cron to truncate wprss_feed_item posts daily
     *
     * @since 2.0
     */
    function wprss_schedule_truncate_posts_cron() {

        // verify event has not been scheduled
        if ( ! wp_next_scheduled( 'wprss_truncate_posts_hook') ) {
            // Schedule to run daily
            wp_schedule_event( time(), 'daily', 'wprss_truncate_posts_hook' );
        }

        add_action( 'wprss_truncate_posts_hook', 'wprss_truncate_posts' );
    }


    // filter to add new possible frequencies to the cron
    add_filter( 'cron_schedules', 'wprss_filter_cron_schedules' );
    /**
     * Adding a few more handy cron schedules to the default ones
     * @since 3.0
     */
    function wprss_filter_cron_schedules( $schedules) {
        $frequencies = array(
            'five_min' => array(
                'interval' => 5 * MINUTE_IN_SECONDS,
                'display' => __( 'Once every five minutes', 'wprss' )
                ),
            'ten_min' => array(
                'interval' => 10 * MINUTE_IN_SECONDS,
                'display' => __( 'Once every ten minutes', 'wprss' )
                ),
            'fifteen_min' => array(
                'interval' => 15 * MINUTE_IN_SECONDS,
                'display' => __( 'Once every fifteen minutes', 'wprss' )
                ),
            'thirty_min' => array(
                'interval' => 30 * MINUTE_IN_SECONDS,
                'display' => __( 'Once every thirty minutes', 'wprss' )
                ),
            'two_hours' => array(
                'interval' => 2 * HOUR_IN_SECONDS,
                'display' => __( 'Once every two hours', 'wprss' )
                ),
            );

        return array_merge( $schedules, $frequencies );
    }




    /**
     * Deletes a custom cron schedule.
     * 
     * Credits: WPCrontrol
     *
     * @param string $name The internal_name of the schedule to delete.
     * @since 3.7
     */
    function wprss_delete_schedule($name) {
        $scheds = get_option('crontrol_schedules',array());
        unset($scheds[$name]);
        update_option('crontrol_schedules', $scheds);
    }




    /**
     * Updates the feed processing cron job schedules.
     * Removes the current schedules and adds the ones in the feed source's meta.
     * 
     * @param $feed_id  The id of the wprss_feed
     * @since 3.8
     */
    function wprss_update_feed_processing_schedules( $feed_id ) {
        // Get the new feed processing schedules
        $activate = get_post_meta( $feed_id, 'wprss_activate_feed', TRUE );
        $pause = get_post_meta( $feed_id, 'wprss_pause_feed', TRUE );

        $schedule_args = array( $feed_id );

        if ( $activate !== '' ) {
            // Convert the meta data values to time stamps
            $new_activate_time = wprss_strtotime( $activate );
            // Get the current schedules
            $activate_feed_timestamp = wp_next_scheduled( 'wprss_activate_feed_schedule_hook', $schedule_args );
            // If a previous schedule exists, unschedule it
            if ( $activate_feed_timestamp !== FALSE ) {
                wp_unschedule_event( $activate_feed_timestamp, 'wprss_activate_feed_schedule_hook', $schedule_args );
            }
            wp_schedule_single_event( $new_activate_time, 'wprss_activate_feed_schedule_hook', $schedule_args );
        }

        if ( $pause !== '' ){
            // Convert the meta data values to time stamps
            $new_pause_time = wprss_strtotime( $pause );
            // Get the current schedules
            $pause_feed_timestamp = wp_next_scheduled( 'wprss_pause_feed_schedule_hook', $schedule_args );
            // If a previous schedule exists, unschedule it
            if ( $pause_feed_timestamp !== FALSE ) {
                wp_unschedule_event( $pause_feed_timestamp, 'wprss_pause_feed_schedule_hook', $schedule_args );
            }
            wp_schedule_single_event( $new_pause_time, 'wprss_pause_feed_schedule_hook', $schedule_args );
        }
        
    }


    add_action( 'wprss_activate_feed_schedule_hook', 'wprss_activate_feed_source', 10, 1 );
    /**
     * Activates the feed source. Runs on a schedule.
     * 
     * @param $feed_id  The of of the wprss_feed
     * @since 3.7
     */
    function wprss_activate_feed_source( $feed_id ) {
        update_post_meta( $feed_id, 'wprss_state', 'active' );
        update_post_meta( $feed_id, 'wprss_activate_feed', '' );

        // Add an action hook, so functions can be run when a feed source is activated
        do_action( 'wprss_on_feed_source_activated', $feed_id );
    }


    add_action( 'wprss_pause_feed_schedule_hook', 'wprss_pause_feed_source', 10 , 1 );
    /**
     * Pauses the feed source. Runs on a schedule.
     * 
     * @param $feed_id  The of of the wprss_feed
     * @since 3.7
     */
    function wprss_pause_feed_source( $feed_id ) {
        update_post_meta( $feed_id, 'wprss_state', 'paused' );
        update_post_meta( $feed_id, 'wprss_pause_feed', '' );

        // Add an action hook, so functions can be run when a feed source is paused
        do_action( 'wprss_on_feed_source_paused', $feed_id );
    }


    add_action( 'wprss_on_feed_source_activated', 'wprss_feed_source_update_start_schedule' );
    /**
     * Starts the looping schedule for a feed source. Runs on a schedule
     * 
     * @param $feed_id The ID of the feed source
     * @since 3.9
     */
    function wprss_feed_source_update_start_schedule( $feed_id ) {
        // Stop any currently scheduled update operations
        wprss_feed_source_update_stop_schedule( $feed_id );
        // Prepare the schedule
        $schedule_args = array( strval( $feed_id ) );

        // Get the interval
        $interval = get_post_meta( $feed_id, 'wprss_update_interval', TRUE );
        // Do nothing if the feed source has no update interval (not sure if possible) or if the interval
        // is set to global
        if ( $interval === '' || $interval === wprss_get_default_feed_source_update_interval() ) return;

        wp_schedule_event( time(), $interval , 'wprss_fetch_single_feed_hook', $schedule_args );
    }


    add_action( 'wprss_on_feed_source_paused', 'wprss_feed_source_update_stop_schedule' );
    /**
     * Stops any scheduled update operations for a feed source. Runs on a schedule.
     * 
     * @param $feed_id The ID of the feed source ( wprss_feed )
     * @since 3.9
     */
    function wprss_feed_source_update_stop_schedule( $feed_id ) {
        $schedule_timestamp = wprss_get_next_feed_source_update( $feed_id );
        // If a schedule exists, unschedule it
        if ( $schedule_timestamp !== FALSE ) {
            $schedule_args = array( strval( $feed_id ) );
            wp_unschedule_event( $schedule_timestamp, 'wprss_fetch_single_feed_hook', $schedule_args );
        }
    }


    /**
     * Returns the timestamp for the next feed source update
     * 
     * @param $feed_id The ID of the feed source ( wprss_feed )
     * @return  The timestamp of the next update operation, or false is no
     *          update is scheduled.
     * @since 3.9
     */
    function wprss_get_next_feed_source_update( $feed_id ) {
        $schedule_args = array( strval( $feed_id ) );
        $timestamp = wp_next_scheduled( 'wprss_fetch_single_feed_hook', $schedule_args );
        return $timestamp;
    }


    /**
     * Parses the date time string into a UTC timestamp.
     * The string must be in the format: m/d/y h:m:s
     * 
     * @since 3.9
     */
    function wprss_strtotime( $str ){
        $parts = explode(' ', $str);
        $date = explode( '/', $parts[0] );
        $time = explode( ':', $parts[1] );
        return mktime( $time[0], $time[1], $time[2], $date[1], $date[0], $date[2] );
    }


    /**
     * Returns the default value for the per feed source update interval
     * 
     * @since 3.9
     */
    function wprss_get_default_feed_source_update_interval() {
        return 'global';
    }