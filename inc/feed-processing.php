
<?php  
    /**
     * Feed processing related functions 
     * 
     * @package WPRSSAggregator
     */ 


    /**
     * Change the default feed cache recreation period to 2 hours
     * 
     * @since 2.1
     */ 
    function wprss_return_7200( $seconds )
    {      
      return 7200;
    } // end wprss_return_7200
      

    /**
     * Fetches feed items from sources provided
     * 
     * @since 2.0
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
                        $feed = fetch_feed( $feed_url );
                        remove_filter( 'wp_feed_cache_transient_lifetime' , 'wprss_return_7200' ); 
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
    } // end wprss_fetch_all_feed_items


    add_action( 'wprss_items_post_meta', 'wprss_items_create_post_meta'); 
    /**
     * Creates meta entries for feed items while they are being imported
     * 
     * @since 2.3
     */
    function wprss_items_create_post_meta( $inserted_ID, $item, $feed_ID) {
        update_post_meta( $inserted_ID, 'wprss_item_permalink', $item->get_permalink() );
        update_post_meta( $inserted_ID, 'wprss_item_description', $item->get_description() );                        
        update_post_meta( $inserted_ID, 'wprss_item_date', $item->get_date( 'U' ) ); // Save as Unix timestamp format
        update_post_meta( $inserted_ID, 'wprss_feed_id', $feed_ID); 
        do_action( 'wprss_items_create_post_meta', $inserted_ID, $item );
    } // end wprss_items_create_post_meta


    add_action( 'wp_insert_post', 'wprss_fetch_feed_items' ); 
    /**
     * Fetches feed items from sources provided
     * 
     * @since 2.0
     */
    function wprss_fetch_feed_items( $post_id ) {            
        
        // Get current post that triggered the hook, $post_id passed via the hook
        $post = get_post( $post_id );
                
        if( ( $post->post_type == 'wprss_feed' ) && ( $post->post_status == 'publish' ) ) { 
        
            // Get all feed sources
            $feed_sources = new WP_Query( array(
                'post_type'      => 'wprss_feed',
                'post_status'    => 'publish',
                'posts_per_page' => -1,
            ) );
           
            if( $feed_sources->have_posts() ) {
                // Start by getting one feed source, we will cycle through them one by one, 
                // fetching feed items and adding them to the database in each pass
                while ( $feed_sources->have_posts() ) {                
                    $feed_sources->the_post();
                    
                    $feed_ID = get_the_ID();
                    $feed_url = get_post_meta( get_the_ID(), 'wprss_url', true );
                    
                    // Use the URL custom field to fetch the feed items for this source
                    if( !empty( $feed_url ) ) {             
                        add_filter( 'wp_feed_cache_transient_lifetime' , 'wprss_return_7200' );
                        $feed = fetch_feed( $feed_url );
                        remove_filter( 'wp_feed_cache_transient_lifetime' , 'wprss_return_7200' ); 
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
                            // Check if newly fetched item already present in existing feed items, 
                            // if not insert it into wp_posts and insert post meta.
                            if ( ! ( in_array( $item->get_permalink(), $existing_permalinks ) ) ) { 
                                $feed_item = apply_filters(
                                    'wprss_populate_post_data',
                                    array(
                                        'post_title'   => $item->get_title(),
                                        'post_content' => '',
                                        'post_status'  => 'publish',
                                        'post_type'    => 'wprss_feed_item',
                                    ),
                                    $item
                                );
                                // Create and insert post object into the DB                              
                                $inserted_ID = wp_insert_post( $feed_item );
                                // Create and insert post meta into the DB
                                wprss_items_create_post_meta( $inserted_ID, $item, $feed_ID );               
                           } //end if
                        } //end foreach
                    } // end if
                } // end $feed_sources while loop
                wp_reset_postdata(); // Restore the $post global to the current post in the main query        
            } // end if
        } // end if
    } // end wprss_fetch_feed_items      


    add_action( 'trash_wprss_feed', 'wprss_delete_feed_items' );
    /**
     * Delete feed items on trashing of corresponding feed source
     * 
     * @since 2.0
     */    
    function wprss_delete_feed_items() {
        global $post;
  
        $args = array(
                'post_type'   => 'wprss_feed_item',
                'meta_query'  => array(                                                   
                                    'key'     => 'wprss_feed_id',                  
                                    'value'   => $post->ID, 
                                    'compare' => 'LIKE'                                        
                )        
        );
        
        $feed_items = new WP_Query( $args );  

        if ( $feed_items->have_posts() ) :
            while ( $feed_items->have_posts() ) : $feed_items->the_post();
                $postid = get_the_ID();

                $purge = wp_delete_post( $postid, true );                
            endwhile;
        endif;
 
        wp_reset_postdata();
    } // end wprss_delete_feed_items   

 
    /**
     * Delete old feed items from the database to avoid bloat
     * 
     * @since 2.0
     */
    function wprss_truncate_posts() {
        global $wpdb;

        // Set your threshold of max posts and post_type name
        $threshold = 50;
        $post_type = 'wprss_feed_item';

        // Query post type
        // $wpdb query allows me to select specific columns instead of grabbing the entire post object.
        $query = "
            SELECT ID, post_title FROM $wpdb->posts 
            WHERE post_type = '$post_type' 
            AND post_status = 'publish' 
            ORDER BY post_modified DESC
        ";
        $results = $wpdb->get_results( $query );

        // Check if there are any results
        if ( count( $results ) ){
            foreach ( $results as $post ) {
                $i++;

                // Skip any posts within our threshold
                if ( $i <= $threshold )
                    continue;

                // Let the WordPress API do the heavy lifting for cleaning up entire post trails
                $purge = wp_delete_post( $post->ID, true );
            }
        }
    } // end wprss_truncate_posts        
