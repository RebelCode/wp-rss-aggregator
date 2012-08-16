<?php    
    /**
     * Plugin deactivation procedure
     * @since 1.0
     */           
    function wprss_deactivate() {
        // on deactivation remove the cron job 
        if ( wp_next_scheduled( 'wprss_generate_hook' ) ) 
        wp_clear_scheduled_hook( 'wprss_generate_hook' );
    }
?>