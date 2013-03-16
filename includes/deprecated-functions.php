<?php

    /**
     * Fetches feed items from sources provided
     * DEPRECATED - JUST FOR REFERENCE
     * 
     * @since 2.0
     * @deprecated 3.0
     */
     function wprss_fetch_all_feed_items( ) {            
        
            // Get all feed sources
            $feed_sources = new WP_Query( array(
                'post_type'      => 'wprss_feed',
                'post_status'    => 'publish',
                'posts_per_page' => -1,
            ) );
            
            if( $feed_sources->have_posts() ) {
                /* Start by getting one feed source, we will cycle through them one by one, 
                   fetching feed items and adding them to the database in each pass */
                while ( $feed_sources->have_posts() ) {                
                    $feed_sources->the_post();
                    
                    $feed_ID = get_the_ID();
                    $feed_url = get_post_meta( get_the_ID(), 'wprss_url', true );
                    
                    // Use the URL custom field to fetch the feed items for this source
                    if( !empty( $feed_url ) ) {             
                        
                        add_filter( 'wp_feed_cache_transient_lifetime' , 'wprss_return_7200' );
                        //$feed = fetch_feed( $feed_url );                        
                        $feed = wprss_fetch_feed( $feed_url );                        
                        remove_filter( 'wp_feed_cache_transient_lifetime' , 'wprss_return_7200' ); 

                    //    $feed->strip_htmltags( array_merge( $feed->strip_htmltags, array('h1', 'a', 'img') ) ); 
                        
                        if ( !is_wp_error( $feed ) ) {
                            // Figure out how many total items there are, but limit it to 10. 
                            $maxitems = $feed->get_item_quantity(10); 

                            // Build an array of all the items, starting with element 0 (first element).
                            $items = $feed->get_items( 0, $maxitems );   
                        }
                        else { return; }
                    }

                    if ( ! empty( $items ) ) {
                        // Gather the permalinks of existing feed item's related to this feed source
                        global $wpdb;
                        $existing_permalinks = $wpdb->get_col(
                            "SELECT meta_value
                            FROM $wpdb->postmeta
                            WHERE meta_key = 'wprss_item_permalink'
                            AND post_id IN ( SELECT post_id FROM $wpdb->postmeta WHERE meta_value = $feed_ID)
                            ");

                        foreach ( $items as $item ) {
                            // Check if newly fetched item already present in existing feed item item, 
                            // if not insert it into wp_posts and insert post meta.
                            if (  ! ( in_array( $item->get_permalink(), $existing_permalinks ) )  ) { 
                                // Create post object
                                $feed_item = array(
                                    'post_title'   => $item->get_title(),
                                    'post_content' => '',
                                    'post_status'  => 'publish',
                                    'post_type'    => 'wprss_feed_item'
                                );                
                                $inserted_ID = wp_insert_post( $feed_item );
                                wprss_items_create_post_meta( $inserted_ID, $item, $feed_ID );                  
                           } //end if
                        } //end foreach
                    } // end if
                } // end $feed_sources while loop
                wp_reset_postdata(); // Restore the $post global to the current post in the main query        
           // } // end if
        } // end if
    } 

// For testing query speed
// $time_start = microtime( true );    
// wp_die(number_format( microtime( true ) - $time_start, 10 ));

