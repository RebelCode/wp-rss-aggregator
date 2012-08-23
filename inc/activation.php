<?php /**
     * Plugin activation procedure
     */      
    
    function wprss_activate() {
        // Activates the plugin and checks for compatible version of WordPress 
        if ( version_compare( get_bloginfo( 'version' ), '3.2', '<' ) ) {
            deactivate_plugins ( basename( __FILE__ ));     // Deactivate plugin
            wp_die( "This plugin requires WordPress version 3.2 or higher." );
        }
          
        if ( !wp_next_scheduled( 'wprss_generate_hook' ) ) {            
            // Schedule to run hourly
            wp_schedule_event( time(), 'hourly', 'wprss_generate_hook' );
        }
        
        add_action( 'wprss_generate_hook', 'wp_rss_aggregator' );                
    }
?>