<?php
    /**
     * Set up shortcodes and call the main function for output
     */     
    
    // Register shortcodes
    add_shortcode( 'wp_rss_aggregator', 'wprss_shortcode');
    add_shortcode( 'wp-rss-aggregator', 'wprss_shortcode');
    
    function wprss_shortcode( $atts ) {    
        if ( !empty ($atts) ) {
            foreach ( $atts as $key => &$val ) {
                $val = html_entity_decode($val);
            }
        }
        wp_rss_aggregator( $atts );       
    }
?>