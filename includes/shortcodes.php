<?php
    /**
     * Set up shortcodes and call the main function for output
     *
     * @since 1.0
     */         
    function wprss_shortcode( $atts ) {    

        //Enqueue scripts / styles
        wp_enqueue_script( 'jquery.colorbox-min', WPRSS_JS . 'jquery.colorbox-min.js', array( 'jquery' ) );         
        wp_enqueue_script( 'custom', WPRSS_JS . 'custom.js', array( 'jquery', 'jquery.colorbox-min' ) );  

        $general_settings = get_option( 'wprss_settings_general' );

        if( ! $general_settings['styles_disable'] == 1 ) {         
            wp_enqueue_style( 'colorbox', WPRSS_CSS . 'colorbox.css', array(), '1.4.33' );     
            wp_enqueue_style( 'styles', WPRSS_CSS . 'styles.css', array(), '' );         
        }
                
        if ( !empty ($atts) ) {
            foreach ( $atts as $key => &$val ) {
                $val = html_entity_decode($val);
            }
        }      
        ob_start(); // start an output buffer to output of the following function
        wprss_display_feed_items( $atts ); 
        $feed_items = ob_get_clean(); // save the current buffer and clear it


        
        return apply_filters( 'wprss_shortcode_output', $feed_items );
    }
    
    // Register shortcodes
    add_shortcode( 'wp_rss_aggregator', 'wprss_shortcode');
    add_shortcode( 'wp-rss-aggregator', 'wprss_shortcode');    
