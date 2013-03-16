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
                'interval' => 300,
                'display' => __( 'Once every five minutes', 'wprss' )
                ),
            'ten_min' => array(
                'interval' => 600,
                'display' => __( 'Once every ten minutes', 'wprss' )
                ),
            'fifteen_min' => array(
                'interval' => 900,
                'display' => __( 'Once every fifteen minutes', 'wprss' )
                ),
            'thirty_min' => array(
                'interval' => 1800,
                'display' => __( 'Once every thirty minutes', 'wprss' )
                ),
            'two_hours' => array(
                'interval' => 7200,
                'display' => __( 'Once every two hours', 'wprss' )
                ),
            );

        return array_merge( $schedules, $frequencies );
    }