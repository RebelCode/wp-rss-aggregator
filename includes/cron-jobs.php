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

        add_action( 'wprss_fetch_all_feeds_hook', 'wprss_fetch_insert_all_feed_items' );
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
     */
    function wprss_update_feed_processing_schedules( $feed_id ) {
        // Get the new feed processing schedules
        $activate = get_post_meta( $feed_id, 'wprss_activate_feed', TRUE );
        $pause = get_post_meta( $feed_id, 'wprss_pause_feed', TRUE );
        // Convert the meta data values to time stamps
        $new_activate_time = wprss_strtotime( $activate, true ); //. ' 20:51:00' );
        $new_pause_time = wprss_strtotime( $pause ); //. ' 20:52:00' );
        file_put_contents( 'C:\log.txt', "$activate => $new_activate_time\n$pause => $new_pause_time" );

        $schedule_args = array( $feed_id );

        // Get the current schedules
        $activate_feed_timestamp = wp_next_scheduled( 'wprss_activate_feed_schedule_hook', $schedule_args );
        $pause_feed_timestamp = wp_next_scheduled( 'wprss_pause_feed_schedule_hook', $schedule_args );
        
        // If a previous schedules exist, unschedule them
        if ( $activate_feed_timestamp !== FALSE ) {
            wp_unschedule_event( $activate_feed_timestamp, 'wprss_activate_feed_schedule_hook', $schedule_args );
        }
        if ( $pause_feed_timestamp !== FALSE ) {
            wp_unschedule_event( $pause_feed_timestamp, 'wprss_pause_feed_schedule_hook', $schedule_args );
        }

        wp_schedule_single_event( $new_activate_time, 'wprss_activate_feed_schedule_hook', $schedule_args );
        wp_schedule_single_event( $new_pause_time, 'wprss_pause_feed_schedule_hook', $schedule_args );
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
    }



    function wprss_strtotime( $str, $b = false ){
        $parts = explode( '/', $str );
        $m = ( $b )? '03' : '04';
        return mktime( '21', $m, '00', $parts[1], $parts[0], $parts[2] );
    }