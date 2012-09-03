<?php
    /** 
     * Contains all the cron jobs in use by WP RSS Aggregator
     *         
     * @package WPRSSAggregator
     */
    

    /**
     * Creates the cron to fetch feeds every hour
     *
     * @since 2.0
     */    
    function wprss_schedule_fetch_feeds_cron() {

        // verify event has not been scheduled 
        if ( !wp_next_scheduled( 'wprss_fetch_feeds_hook' ) ) {            
            // Schedule to run hourly
            wp_schedule_event( time(), 'hourly', 'wprss_fetch_feeds_hook' );
        }
        
        add_action( 'wprss_fetch_feeds_hook', 'wprss_fetch_feed_items' );    
    }


    /**
     * Creates the cron to truncate wprss_feed_item posts daily
     *
     * @since 2.0
     */    
    function wprss_schedule_truncate_posts_cron() { 
        // verify event has not been scheduled 
        if ( !wp_next_scheduled( 'wprss_truncate_posts_hook') ) {
            // Schedule to run daily
            wp_schedule_event( time(), 'daily', 'wprss_truncate_posts_hook' );
        }  

        add_action( 'wprss_truncate_posts_hook', 'wprss_truncate_posts' );   
    }
?>