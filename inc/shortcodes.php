<?php

    /**
     * Set up shortcodes and call the main function for output
     *
     * @since 1.0
     */         
    function wprss_shortcode( $atts ) {    
        if ( !empty ($atts) ) {
            foreach ( $atts as $key => &$val ) {
                $val = html_entity_decode($val);
            }
        }
        wprss_display_feed_items( $atts );       
    }
    
    // Register shortcodes
    add_shortcode( 'wp_rss_aggregator', 'wprss_shortcode');
    add_shortcode( 'wp-rss-aggregator', 'wprss_shortcode');    
?>