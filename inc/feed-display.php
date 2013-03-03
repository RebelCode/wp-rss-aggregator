<?php  
    /**
     * Feed display related functions 
     * 
     * @package WPRSSAggregator
     */ 


    /**
     * Retrieve settings and prepare them for use in the display function
     * 
     * @since 3.0
     */
    function wprss_get_display_settings( $settings ) {
        
        switch ( $settings['open_dd'] ) {             
            
            case 'Lightbox' :
                $display_settings['open'] = 'class="colorbox"'; 
                break;

            case 'New window' :
                $display_settings['open'] = 'target="_blank"';
                break;   
        }

        switch ( $settings['follow_dd'] ) { 

            case 'No follow' :
                $display_settings['follow'] = 'rel="nofollow"';
                break;
        }

        do_action( 'wprss_get_settings' );

        return $display_settings;
    }


    /**
     * Merges the default arguments with the user set arguments
     * 
     * @since 3.0
     */
	function wprss_get_args( $args ) {
		// Default shortcode/function arguments for displaying feed items
        $default_args = apply_filters( 
	        				'default_args',
	        				array(
		                          'links_before' => '<ul class="rss-aggregator">',
		                          'links_after'  => '</ul>',
		                          'link_before'  => '<li class="feed-item">',
		                          'link_after'   => '</li>' 
		                    )                         
	    );

        // Parse incoming $args into an array and merge it with $default_args         
        $args = wp_parse_args( $args, $default_args );

        return $args;
	}


    /**
     * Prepares and builds the query for fetching the feed items
     * 
     * @since 3.0
     */
	function wprss_get_feed_items_query( $settings ) {
	 	// Arguments for the next query to fetch all feed items
        $feed_items_args = apply_filters( 
					        	'wprss_display_feed_items_query',
					        	array(
						            'post_type'      => 'wprss_feed_item',
						            'posts_per_page' => $settings['feed_limit'], 
						            'orderby'        => 'meta_value', 
						            'meta_key'       => 'wprss_item_date', 
						            'order'          => 'DESC'
					        	)
        );

        // Query to get all feed items for display
        $feed_items = new WP_Query( $feed_items_args );

        return $feed_items;
	}


	add_action( 'wprss_display_template', 'wprss_default_display_template', '', 4 );
    /**
     * Default template for feed items display - DISABLED JUST FOR TESTING PURPOSES
     * 
     * @since 3.0
     */
	/*function wprss_default_display_template( $display_settings, $args, $feed_items ) {

        $general_settings = get_option('wprss_settings');
        $excerpts_settings = get_option('wprss_settings_excerpts');
        $thumbnails_settings = get_option('wprss_settings_thumbnails');
        // Declare each item in $args as its own variable
        extract( $args, EXTR_SKIP );   

        $output = '';

        if( $feed_items->have_posts() ) {            
           
            $output .= "$links_before\n";
           
            while ( $feed_items->have_posts() ) {                
                $feed_items->the_post();		
                $permalink 		 = get_post_meta( get_the_ID(), 'wprss_item_permalink', true );
                $description 	 = get_the_excerpt();
                $feed_source_id  = get_post_meta( get_the_ID(), 'wprss_feed_id', true );
                $source_name 	 = get_the_title( $feed_source_id );  
                do_action( 'wprss_get_post_data' );              
                $thumbnail 		 = get_post_meta( get_the_ID(), 'wprss_item_thumbnail', true );

                // convert from Unix timestamp        
                $date = date( 'Y-m-d', intval( get_post_meta( get_the_ID(), 'wprss_item_date', true ) ) );

                // need to have filter to add thumbnail              
                $output .= "\t\t" . "$link_before" . '<a ' . $display_settings['open'] . ' ' . $display_settings['follow'] . ' href="'. $permalink . '">'. get_the_title(). '</a>' . "\n";                 
            	if ( function_exists( 'wpthumb' ) ) {
	                $output .= '<div class="block" style="overflow:hidden;"><img style="max-width:100%; float:left; margin-top: 0.5em; margin-right:10px;" src="' 
	                			. wpthumb( $thumbnail, 'width=' . $thumbnails_settings['thumbnail_width'] 
	                			. '&height=' . $thumbnails_settings['thumbnail_height'] . '&crop=1' ) . '" />';
	            }                 
                $output .= "\t\t" . limit_words( $description, 40 ) . "\n\n"; 
                $output .= "\t\t" .'</div><div><span class="feed-source">' . __( 'Source: ' ) . $source_name . ' | ' . $date . '</span></div>' . "$link_after" . "\n\n"; 
            }
            $output .= "\t\t $links_after";
            $output = apply_filters( 'feed_output', $output );
            
            echo $output;
              
                       		// echo paginate_links();

            wp_reset_postdata();
            
        } else {
            $output = apply_filters( 'no_feed_items_found', __( 'No feed items found.', 'wprss' ) );
            echo $output;
        }            
    }*/







    add_action( 'wprss_display_template', 'wprss_default_display_template', '', 3 );
    /**
     * Default template for feed items display 
     * 
     * @since 3.0
     */
    function wprss_default_display_template( $display_settings, $args, $feed_items ) {

        $general_settings    = get_option( 'wprss_settings' );
        $excerpts_settings   = get_option( 'wprss_settings_excerpts' );
        $thumbnails_settings = get_option( 'wprss_settings_thumbnails' );

        // Declare each item in $args as its own variable
        extract( $args, EXTR_SKIP );   

        $output = '';

        if( $feed_items->have_posts() ) {            
           
            $output .= "$links_before\n";
           
            while ( $feed_items->have_posts() ) {                
                $feed_items->the_post();        
                $permalink       = get_post_meta( get_the_ID(), 'wprss_item_permalink', true );
                $description     = get_the_excerpt();
                $feed_source_id  = get_post_meta( get_the_ID(), 'wprss_feed_id', true );
                $source_name     = get_the_title( $feed_source_id );  
                do_action( 'wprss_get_post_data' );              
                $thumbnail_src   = get_post_meta( get_the_ID(), 'wprss_item_thumbnail', true );

                if ( ! empty( $thumbnail_src) ) {
                    $thumbnail = wpthumb( $thumbnail_src, 'width=' . $thumbnails_settings['thumbnail_width'] 
                                    . '&height=' . $thumbnails_settings['thumbnail_height'] . '&crop=1' );
                }

                else {
                 
                    $thumbnail = wpthumb( $thumbnails_settings['default_thumbnail'], 'width=' . $thumbnails_settings['thumbnail_width'] 
                                    . '&height=' . $thumbnails_settings['thumbnail_height'] . '&crop=1' );
                }

                // convert from Unix timestamp        
                $date = date( 'Y-m-d', intval( get_post_meta( get_the_ID(), 'wprss_item_date', true ) ) );

                // need to have filter to add thumbnail              
                $output .= "\t\t" . "$link_before" . '<a ' . $display_settings['open'] . ' ' . $display_settings['follow'] . ' href="'. $permalink . '">'. get_the_title(). '</a>' . "\n";                 
                if ( function_exists( 'wpthumb' ) ) {
                    $output .= '<div class="thumbnail-excerpt"><img src="' . $thumbnail . '" />';
                }                 
                $output .= "\t\t" . limit_words( $description, 40 ) . "\n\n"; 
                $output .= "\t\t" .'</div><div><span class="feed-source">' . __( 'Source: ' ) . $source_name . ' | ' . $date . '</span></div>' . "$link_after" . "\n\n"; 
            }
            $output .= "\t\t $links_after";
            $output = apply_filters( 'feed_output', $output );
            
            echo $output;
              
            // echo paginate_links();

            wp_reset_postdata();
            
        } else {
            $output = apply_filters( 'no_feed_items_found', __( 'No feed items found.', 'wprss' ) );
            echo $output;
        }            
    }













    /**
     * Display feed items on the front end (via shortcode or function)
     * 
     * @since 2.0
     */
    function wprss_display_feed_items( $args = array() ) {
    	$settings = get_option( 'wprss_settings' );
    	$display_settings = wprss_get_display_settings( $settings );
    	$args = wprss_get_args( $args );    
 		$feed_items = wprss_get_feed_items_query( $settings );
 		do_action( 'wprss_display_template', $display_settings, $args, $feed_items );
    } // end wprss_display_feed_items


    /**
     * Redirects to wprss_display_feed_items
     * It is used for backwards compatibility to versions < 2.0
     * 
     * @since 2.1
     */
    function wp_rss_aggregator( $args = array() ) { 
        wprss_display_feed_items( $args ); 
    } // end wp_rss_aggregator
    
