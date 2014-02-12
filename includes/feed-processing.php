<?php
    /**
     * Feed processing related functions
     *
     * @package WPRSSAggregator
     */


    /**
     * Returns whether or not the feed source will forcefully fetch the next fetch,
     * ignoring whether or not it is paused or not.
     * 
     * @param $source_id    The ID of the feed soruce
     * @return boolean
     * @since 3.7
     */
    function wprss_feed_source_force_next_fetch( $source_id ) {
        $force = get_post_meta( $source_id, 'wprss_force_next_fetch', TRUE );
        return ( $force !== '' || $force == '1' );
    }



    /**
     * Change the default feed cache recreation period to 2 hours
     *
     * Probably not needed since we are now disabling caching altogether
     *
     * @since 2.1
     */
    function wprss_feed_cache_lifetime( $seconds )
    {
        return 1; // one second
    }


    /**
     * Disable caching of feeds in transients, we don't need it as we are storing them in the wp_posts table
     *
     * @since 3.0
     */
    function wprss_do_not_cache_feeds( &$feed ) {
        $feed->enable_cache( false );
    }


    /**
     * Parameters for query to get all feed sources
     *
     * @since 3.0
     */
    function wprss_get_all_feed_sources() {
        // Get all feed sources
        $feed_sources = new WP_Query( apply_filters(
            'wprss_get_all_feed_sources',
            array(
                'post_type'      => 'wprss_feed',
                'post_status'    => 'publish',
                'cache_results'  => false,   // Disable caching, used for one-off queries
                'no_found_rows'  => true,    // We don't need pagination, so disable it
                'posts_per_page' => -1
            )
        ) );
        return $feed_sources;
    }



    /**
     * Returns all the feed items of a source.
     *
     * @since 3.8
     */
    function wprss_get_feed_items_for_source( $source_id ) {
        $args = apply_filters(
            'wprss_get_feed_items_for_source_args',
            array(
                'post_type'     => 'wprss_feed_item',
                'cache_results' => false,   // Disable caching, used for one-off queries
                'no_found_rows' => true,    // We don't need pagination, so disable it
                'posts_per_page'=> -1,
                'orderby'       => 'meta_value',
                'meta_key'      => 'wprss_item_date',
                'order'         => 'DESC',
                'meta_query'    => array(
                    array(
                        'key'       => 'wprss_feed_id',
                        'value'     => $source_id,
                        'compare'   => 'LIKE',
                    ),
                )
            )
        );
        return new WP_Query( $args );
    }


    /**
     * Parameters for query to get feed sources
     *
     * @since 3.0
     */
    function wprss_get_feed_source() {
        // Get all feed sources
        $feed_sources = new WP_Query( apply_filters(
            'wprss_get_all_feed_sources',
            array(
                'post_type'      => 'wprss_feed',
                'post_status'    => 'publish',
                'cache_results'  => false,   // Disable caching, used for one-off queries
                'no_found_rows'  => true,    // We don't need pagination, so disable it
                'posts_per_page' => -1
            )
        ) );
        return $feed_sources;
    }


    /**
     * Database query to get existing permalinks
     *
     * @since 3.0
     */
    function get_existing_permalinks( $feed_ID ) {
        global $wpdb;

        $existing_permalinks = $wpdb->get_col(
                                        "SELECT meta_value
                                        FROM $wpdb->postmeta
                                        WHERE meta_key = 'wprss_item_permalink'
                                        AND post_id IN ( SELECT post_id FROM $wpdb->postmeta WHERE meta_value = $feed_ID )"
        );

        return $existing_permalinks;
    }


    add_action( 'publish_wprss_feed', 'wprss_fetch_insert_feed_items', 10 );
    /**
     * Fetches feed items from source provided and inserts into db
     *
     * This function is used when inserting or untrashing a new feed source, it only gets feeds from that particular source
     *
     * @since 3.0
     */
    function wprss_fetch_insert_feed_items( $post_id ) {
        wp_schedule_single_event( time(), 'wprss_fetch_single_feed_hook', array( $post_id ) );
    }



    /**
     * Returns the image of the feed.
     * The reason this function exists is for add-ons to be able to detect if the plugin core
     * supports feed image functionality through a simple function_exists() call.
     * 
     * @param $source_id The ID of the feed source
     * @return string The link to the feed image
     * @since 1.0
     */ 
    function wprss_get_feed_image( $source_id ) {
        return get_post_meta( $source_id, 'wprss_feed_image', true );
    }


    add_action( 'post_updated', 'wprss_updated_feed_source', 10, 3 );
    /**
     * This function is triggered just after a post is updated.
     * It checks if the updated post is a feed source, and carries out any
     * updating necassary.
     *
     * @since 3.3
     */
    function wprss_updated_feed_source( $post_ID, $post_after, $post_before ) {
        // Check if the post is a feed source and is published
        
        if ( ( $post_after->post_type == 'wprss_feed' ) && ( $post_after->post_status == 'publish' ) ) {

            if ( isset( $_POST['wprss_url'] ) && !empty( $_POST['wprss_url'] ) ) {
                $url = $_POST['wprss_url'];
                $feed = wprss_fetch_feed( $url );
                if ( $feed !== NULL && !is_wp_error( $feed ) ) {
                    update_post_meta( $post_ID, 'wprss_site_url', $feed->get_permalink() );
                    update_post_meta( $post_ID, 'wprss_feed_image', $feed->get_image_url() );
                }
            }


        	if ( isset( $_POST['wprss_limit'] ) && !empty( $_POST['wprss_limit'] ) ) {
	            // Checking feed limit change
	            // Get the limit currently saved in db, and limit in POST request
	            //$limit = get_post_meta( $post_ID, 'wprss_limit', true );
	            $limit = $_POST['wprss_limit'];
	            // Get all feed items for this source
	            $feed_sources = new WP_Query(
					array(
						'post_type'      => 'wprss_feed_item',
						'post_status'    => 'publish',
						'cache_results'  => false,   // Disable caching, used for one-off queries
						'no_found_rows'  => true,    // We don't need pagination, so disable it
						'posts_per_page' => -1,
						'orderby' 		 => 'date',
						'order' 		 => 'ASC',
						'meta_query'     => array(
							array(
								'key'     => 'wprss_feed_id',
								'value'   => $post_ID,
								'compare' => 'LIKE'
							)
						)
					)
	            );
	            // If the limit is smaller than the number of found posts, delete the feed items
	            // and re-import, to ensure that most recent feed items are present.
	            $difference = intval( $feed_sources->post_count ) - intval( $limit );
	            if ( $difference > 0 ) {
	            	// Loop and delete the excess feed items
					while ( $feed_sources->have_posts() && $difference > 0 ) {
						$feed_sources->the_post();
						wp_delete_post( get_the_ID(), true );
						$difference--;
					}
	            }
        	}
        }
    }



    add_action( 'updated_post_meta', 'wprss_update_feed_meta', 10, 4 );
    /**
     * This function is run whenever a post is saved or updated.
     *
     * @since 3.4
     */
    function wprss_update_feed_meta( $meta_id, $post_id, $meta_key, $meta_value ) {
        $post = get_post( $post_id );
        if ( $post->post_status === 'publish' && $post->post_type === 'wprss_feed' ) {
            if ( $meta_key === 'wprss_url' )
                wprss_change_fb_url( $post_id, $meta_value );
        }
    }


    function wprss_change_fb_url( $post_id, $url ) {
        # Check if url begins with a known facebook hostname.
        if (    stripos( $url, 'http://facebook.com' ) === 0
            ||  stripos( $url, 'http://www.facebook.com' ) === 0
            ||  stripos( $url, 'https://facebook.com' ) === 0
            ||  stripos( $url, 'https://www.facebook.com' ) === 0
        ) {
            # Generate the new URL to FB Graph
            $com_index = stripos( $url, '.com' );
            $fb_page = substr( $url, $com_index + 4 ); # 4 = length of ".com"
            $fb_graph_url = 'http://graph.facebook.com' . $fb_page;
            # Contact FB Graph and get data
            $response = wp_remote_get( $fb_graph_url );
            # If the repsonse successful and has a body
            if ( !is_wp_error( $response ) && isset( $response['body'] ) ) {
                # Parse the body as a JSON string
                $json = json_decode( $response['body'], true );
                # If an id is present ...
                if ( isset( $json['id'] ) ) {
                    # Generate the final URL for this feed and update the post meta
                    $final_url = "http://www.facebook.com/feeds/page.php?format=atom10&id=" . $json['id'];
                    update_post_meta( $post_id, 'wprss_url', $final_url, $url );   
                }
            }
        }
    }


    add_action( 'trash_wprss_feed', 'wprss_delete_feed_items' );   // maybe use wp_trash_post action? wp_trash_wprss_feed
    /**
     * Delete feed items on trashing of corresponding feed source
     *
     * @since 2.0
     */
    function wprss_delete_feed_items( $postid ) {

        $args = array(
            'post_type'     => 'wprss_feed_item',
            // Next 3 parameters for performance, see http://thomasgriffinmedia.com/blog/2012/10/optimize-wordpress-queries
            'cache_results' => false,   // Disable caching, used for one-off queries
            'no_found_rows' => true,    // We don't need pagination, so disable it
            'fields'        => 'ids',   // Returns post IDs only
            'posts_per_page'=> -1,
            'meta_query'    => array(
                                    array(
                                    'key'     => 'wprss_feed_id',
                                    'value'   => $postid,
                                    'compare' => 'LIKE'
                                    )
            )
        );

        $feed_item_ids = get_posts( $args );
        foreach( $feed_item_ids as $feed_item_id )  {
                $purge = wp_delete_post( $feed_item_id, true ); // delete the feed item, skipping trash
        }
        wp_reset_postdata();
    }


    add_action( 'wprss_delete_all_feed_items_hook', 'wprss_delete_all_feed_items' );
    /**
     * Delete all feed items
     *
     * @since 3.0
     */
    function wprss_delete_all_feed_items() {
        $args = array(
                'post_type'      => 'wprss_feed_item',
                'cache_results'  => false,   // Disable caching, used for one-off queries
                'no_found_rows'  => true,    // We don't need pagination, so disable it
                'fields'         => 'ids',   // Returns post IDs only
                'posts_per_page' => -1,
        );

        //$feed_items = new WP_Query( $args );

        $feed_item_ids = get_posts( $args );
        foreach( $feed_item_ids as $feed_item_id )  {
                $purge = wp_delete_post( $feed_item_id, true ); // delete the feed item, skipping trash
        }
        wp_reset_postdata();
    }


    /**
     * Returns the given parameter as a string. Used in wprss_truncate_posts()
     *
     * @return string The given parameter as a string
     * @since 3.5.1
     */
    function wprss_return_as_string( $item ) {
        return "'$item'";
    }



    /**
     * Returns true if the feed item is older than the given timestamp,
     * false otherwise;
     * 
     * @since 3.8
     */
    function wprss_is_feed_item_older_than( $id, $timestamp ) {
        // GET THE DATE
        $age = get_post_meta( $id, 'wprss_item_date', TRUE );
        if ( $age === '' ) return FALSE;
        // Calculate the age difference
        $difference = $age - $timestamp;
        // Return whether the difference is negative ( the age is smaller than the timestamp )
        return ( $difference <= 0 );
    }


    /**
     * Returns the maximum age setting for a feed source.
     * 
     * @since 3.8
     */
    function wprss_get_max_age_for_feed_source( $source_id ) {
        $general_settings = get_option( 'wprss_settings_general' );
        // Get the meta data for age for this feed source
        $age_limit = get_post_meta( $source_id, 'wprss_age_limit', TRUE );
        $age_unit = get_post_meta( $source_id, 'wprss_age_unit', TRUE );

        // If the meta does not exist, use the global settings
        $age_limit = ( $age_limit === '' )? wprss_get_general_setting( 'limit_feed_items_age' ) : $age_limit;
        $age_unit = ( $age_unit === '' )? wprss_get_general_setting( 'limit_feed_items_age_unit' ) : $age_unit;

        // If the age limit is an empty string, use no limit
        if ( $age_limit === '' ) {
            return FALSE;
        }

        // Return the timestamp of the max age date
        return strtotime( "-$age_limit $age_unit" );
    }


    /**
     * Delete old feed items from the database to avoid bloat.
     * As of 3.8, it uses the new feed age system.
     *
     * @since 3.8
     */
    function wprss_truncate_posts() {
        $general_settings = get_option( 'wprss_settings_general' );

        // Get all feed sources
        $feed_sources = wprss_get_all_feed_sources();


        if( $feed_sources->have_posts() ) {

            // FOR EACH FEED SOURCE
            while ( $feed_sources->have_posts() ) {
                $feed_sources->the_post();

                // Get the max age setting for this feed source
                $max_age = wprss_get_max_age_for_feed_source( get_the_ID() );

                // If the data is empty, do not delete
                if ( $max_age !== FALSE ) {

                    // Get all feed items for this source
                    $feed_items = wprss_get_feed_items_for_source( get_the_ID() );

                    // FOR EACH FEED ITEM
                    if ( $feed_items-> have_posts() ) {
                        while ( $feed_items->have_posts() ) {
                            $feed_items->the_post();
                            // If the post is older than the maximum age
                            if ( wprss_is_feed_item_older_than( get_the_ID(), $max_age ) === TRUE ){
                                // Delete the post
                                wp_delete_post( get_the_ID(), true );
                            }   
                        }
                        // Reset feed items query data
                        wp_reset_postdata();
                    }

                }
            }
            // Reset feed sources query data
            wp_reset_postdata();
        }

        // If the filter to use the fixed limit is enabled, call the old truncation function
        if ( apply_filters( 'wprss_use_fixed_feed_limit', FALSE ) === TRUE && isset( $general_settings['limit_feed_items_db'] ) ) {
            wprss_old_truncate_posts();
        }

    }




    /**
     * The old truncation function.
     * This truncation method uses the deprecated fixed feed limit.
     *
     * @since 2.0
     */
    function wprss_old_truncate_posts() {
        global $wpdb;
        $general_settings = get_option( 'wprss_settings_general' );

        if ( $general_settings['limit_feed_items_db'] == 0 ) {
            return;
        }

        // Set your threshold of max posts and post_type name
        $threshold = $general_settings['limit_feed_items_db'];
        $post_types = apply_filters( 'wprss_truncation_post_types', array( 'wprss_feed_item' ) );
        $post_types_str = array_map( 'wprss_return_as_string', $post_types );
        
        $post_type_list = implode( ',' , $post_types_str );

        // Query post type
        // $wpdb query allows me to select specific columns instead of grabbing the entire post object.
        $query = "
            SELECT ID, post_title FROM $wpdb->posts
            WHERE post_type IN ($post_type_list)
            AND post_status = 'publish'
            ORDER BY post_modified DESC
        ";
        
        $results = $wpdb->get_results( $query );

        // Check if there are any results
        $i = 0;
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
    }


    add_filter( 'wprss_insert_post_item_conditionals', 'wprss_check_feed_item_date_on_import', 2, 3 );
    /**
     * When a feed item is imported, it's date is compared against the max age of it's feed source.
     * 
     * 
     * @since 3.8
     */
    function wprss_check_feed_item_date_on_import( $item, $source, $permalink ){
        if ( $item === NULL ) return NULL;

        // Get the age of the item and the max age setting for its feed source
        $age = $item->get_date( 'U' );
        $max_age = wprss_get_max_age_for_feed_source( $source );

        // If the age is not a valid timestamp, and the max age setting is disabled, return the item
        if ( $age === '' || $age === NULL || $max_age === FALSE || $max_age === NULL ) {
            return $item;
        }

        // Calculate the age difference
        $difference = $age - $max_age;
        
        if ( $difference <= 0 ) {
            return NULL;
        } else {
            return $item;
        }
    }


    /**
     * Custom version of the WP fetch_feed() function, since we want custom sanitization of a feed
     *
     * Not being used at the moment, until we decide whether we can still use fetch_feed and modify its handling of sanitization
     *
     * @since 3.0
     *
     */
    /*function wprss_fetch_feed($url) {
        require_once (ABSPATH . WPINC . '/class-feed.php');

        $feed = new SimplePie();

        // $feed->set_sanitize_class( 'WP_SimplePie_Sanitize_KSES' );
        // We must manually overwrite $feed->sanitize because SimplePie's
        // constructor sets it before we have a chance to set the sanitization class
        // $feed->sanitize = new WP_SimplePie_Sanitize_KSES();

        $feed->set_cache_class( 'WP_Feed_Cache' );
        $feed->set_file_class( 'WP_SimplePie_File' );

        $feed->set_feed_url($url);
        $feed->strip_htmltags(array_merge($feed->strip_htmltags, array( 'h1', 'h2', 'h3', 'h4', 'h5', 'a' )));
        $feed->set_cache_duration( apply_filters( 'wp_feed_cache_transient_lifetime', 12 * HOUR_IN_SECONDS, $url ) );
        do_action_ref_array( 'wp_feed_options', array( &$feed, $url ) );
        $feed->init();
        $feed->handle_content_type();

        if ( $feed->error() )
            return new WP_Error('simplepie-error', $feed->error());

        return $feed;
    }*/


    /**
     * Deletes all imported feeds and re-imports everything
     *
     * @since 3.0
     */
    function wprss_feed_reset() {
        wp_schedule_single_event( time(), 'wprss_delete_all_feed_items_hook' );
        wprss_fetch_insert_all_feed_items( TRUE );
    }

  /*  add_action( 'wp_feed_options', 'wprss_feed_options' );
    function wprss_feed_options( $feed) {
        $feed->strip_htmltags(array_merge($feed->strip_htmltags, array('h1', 'a', 'img','em')));
    }

*/