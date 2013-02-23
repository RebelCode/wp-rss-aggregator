<?php 
	/**
     * Plugin activation procedure
     *
     * @since 1.0
     */          
    function wprss_activate() {
        // Activates the plugin and checks for compatible version of WordPress 
        if ( version_compare( get_bloginfo( 'version' ), '3.2', '<' ) ) {
            deactivate_plugins ( basename( __FILE__ ));     // Deactivate plugin
            wp_die( __( 'This plugin requires WordPress version 3.2 or higher.' ), 'WP RSS Aggregator', array( 'back_link' => true ) );
        }  
        wprss_addfeed_add_feed();
        flush_rewrite_rules();
        wprss_schedule_fetch_feeds_cron();   
    }

