<?php /**
     * Plugin activation procedure
     */      
    
    function wprss_activate() {
        // Activates the plugin and checks for compatible version of WordPress 
        if ( version_compare( get_bloginfo( 'version' ), '3.2', '<' ) ) {
            deactivate_plugins ( basename( __FILE__ ));     // Deactivate plugin
            wp_die( "This plugin requires WordPress version 3.2 or higher." );
        }
        
        // verify event has not been scheduled 
        if ( !wp_next_scheduled( 'wprss_cron_hook' ) ) {            
            // Schedule to run hourly
            wp_schedule_event( time(), 'hourly', 'wprss_cron_hook' );
        }
        
        add_action( 'wprss_cron_hook', 'wprss_fetch_feed_items' );              
    }
?>