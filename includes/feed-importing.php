<?php
	/**
	 * Functions relating to feed importing
	 *
	 * @package WPRSSAggregator
	 */



	add_action( 'wprss_fetch_single_feed_hook', 'wprss_fetch_insert_single_feed_items' );
	/**
	 * The main feed fetching function.
	 * Fetches the feed items from the source provided and inserts them into the DB.
	 *
	 * Called on hook 'wprss_fetch_single_feed_hook'.
	 *
	 * @since 3.2
	 */
	function wprss_fetch_insert_single_feed_items( $feed_ID ) {
		// Check if the feed source is active.
		if ( wprss_is_feed_source_active( $feed_ID ) === FALSE && wprss_feed_source_force_next_fetch( $feed_ID ) === FALSE ) {
			// If it is not active ( paused ), return without fetching the feed items.
			return;
		}
		// If the feed source is forced for next fetch, remove the force next fetch data
		if ( wprss_feed_source_force_next_fetch( $feed_ID ) ) {
			delete_post_meta( $feed_ID, 'wprss_force_next_fetch' );
		}

		// Get the feed source URL from post meta, and filter it
		$feed_url = get_post_meta( $feed_ID, 'wprss_url', true );
		$feed_url = apply_filters( 'wprss_feed_source_url', $feed_url, $feed_ID );

		// Get the feed limit from post meta
		$feed_limit = get_post_meta( $feed_ID, 'wprss_limit', true );
		// Sanitize the limit. If smaller or equal to zero, or an empty string, set to NULL.
		$feed_limit = ( $feed_limit <= 0 || empty( $feed_limit ) )? NULL : $feed_limit;

		// Filter the URL for validaty
		if ( filter_var( $feed_url, FILTER_VALIDATE_URL ) ) {
			// Get the feed items from the source
			$items = wprss_get_feed_items( $feed_url, $feed_ID );
			// If got NULL, convert to an empty array
			if ( $items === NULL ) $items = array();

			// If the feed has its own meta limit,
			if ( $feed_limit !== NULL ) {
				// slice the items array using the feed meta limit
				// @todo -	Check current number of feed items for source, and delete oldest to make room for new, to keep to the limit.
				//			Use wprss_get_feed_items_for_source
				
				$items_to_insert = array_slice( $items, 0, $feed_limit );

				// Gather the permalinks of existing feed item's related to this feed source
				$existing_permalinks = get_existing_permalinks( $feed_ID );

				// Generate a list of items fetched, that are not already in the DB
				$new_items = array();
				foreach( $items as $item ) {
					$permalink = $item->get_permalink();
					if ( !in_array( $permalink, $existing_permalinks ) ) {
						if ( ! is_array( $new_items ) ) {
							$new_items = array();
						}
						$new_items = array_push( $new_items, $item );
					}
				}

				// Get the number of feed items in DB, and their count
				$db_feed_items = wprss_get_feed_items_for_source( $feed_ID );
				$num_db_feed_items = $db_feed_items->post_count;
				// Get the number of feed items we can store until we reach the limit
				$num_can_insert = $feed_limit - $num_db_feed_items;
				
				// Calculate how many feed items we must delete before importing, to keep to the limit
				$num_feed_items_to_delete = count( $new_items ) - $num_can_insert;

				// Get an array with the DB feed items in reverse order (oldest first)
				$db_feed_items_reversed = array_reverse( $db_feed_items->posts );
				// Cut the array to get only the first few that are to be deleted ( equal to $num_feed_items_to_delete )
				$feed_items_to_delete = array_slice( $db_feed_items_reversed, 0, $num_feed_items_to_delete );
				// Iterate the feed items and delete them
				foreach ( $feed_items_to_delete as $key => $post ) {
					wp_delete_post( $post->ID, TRUE );
				}

				//$items_to_insert = $new_items;

			}
			else { 
				$items_to_insert = $items;
			}
			
			// Insert the items into the db
			if ( !empty( $items_to_insert ) ) {
				wprss_items_insert_post( $items_to_insert, $feed_ID );
			}
		}
	}






	/**
	 * Fetches the feed items from a feed at the given URL.
	 *
	 * Called from 'wprss_fetch_insert_single_feed_items'
	 *
	 * @since 3.0
	 */
	function wprss_get_feed_items( $feed_url, $source ) {
		// Add filters and actions prior to fetching the feed items
		add_filter( 'wp_feed_cache_transient_lifetime' , 'wprss_feed_cache_lifetime' );
		add_action( 'wp_feed_options', 'wprss_do_not_cache_feeds' );

		/* Fetch the feed from the soure URL specified */
		$feed = wprss_fetch_feed( $feed_url, $source );

		// Remove previously added filters and actions
		remove_action( 'wp_feed_options', 'wprss_do_not_cache_feeds' );
		remove_filter( 'wp_feed_cache_transient_lifetime' , 'wprss_feed_cache_lifetime' );
		
		if ( !is_wp_error( $feed ) ) {
			// Return the items in the feed.
			return $feed->get_items();
		}
		else {
			wprss_log( 'Failed to fetch feed "' . $feed_url . '". ' . $feed->get_error_message() );
			return NULL;
		}
	}







	/**
	 * A clone of the function 'fetch_feed' in wp-includes/feed.php [line #529]
	 *
	 * Called from 'wprss_get_feed_items'
	 *
	 * @since 3.5
	 */
	function wprss_fetch_feed( $url, $source = NULL ) {
		// Import SimplePie
		require_once ( ABSPATH . WPINC . '/class-feed.php' );

		// Trim the URL
		$url = trim( $url );

		// Initialize the Feed
		$feed = new SimplePie();
		// Obselete method calls ?
		//$feed->set_cache_class( 'WP_Feed_Cache' );
		//$feed->set_file_class( 'WP_SimplePie_File' );
		$feed->set_feed_url( $url );
		$feed->set_autodiscovery_level( SIMPLEPIE_LOCATOR_ALL );

		// If a feed source was passed
		if ( $source !== NULL ) {
			// Get the force feed option for the feed source
			$force_feed = get_post_meta( $source, 'wprss_force_feed', TRUE );
			// If turned on, force the feed
			if ( $force_feed == 'true' ) {
				$feed->force_feed( TRUE );
			}
		}
		
		// Set timeout to 30s. Default: 15s
		$feed->set_timeout( 30 );

		//$feed->set_cache_duration( apply_filters( 'wp_feed_cache_transient_lifetime', 12 * HOUR_IN_SECONDS, $url ) );
		$feed->enable_cache( FALSE );

		// Reference array action hook, for the feed object and the URL
		do_action_ref_array( 'wp_feed_options', array( &$feed, $url ) );

		// Prepare the tags to strip from the feed
		$tags_to_strip = apply_filters( 'wprss_feed_tags_to_strip', $feed->strip_htmltags, $source );
		// Strip them
		$feed->strip_htmltags( $tags_to_strip );

		// Fetch the feed
		$feed->init();
		$feed->handle_content_type();

		// Convert the feed error into a WP_Error, if applicable
		if ( $feed->error() ) {
			return new WP_Error( 'simplepie-error', $feed->error() );
		}

		// If no error, return the feed
		return $feed;
	}




	/**
	 * Converts YouTube, Vimeo and DailyMotion video urls
	 * into embedded video player urls.
	 * If the permalink is not a video url, the permalink is returned as is.
	 *
	 * @param	$permalink The string permalink url to convert.
	 * @return	A string, with the convert permalink, or the same permalink passed as parameter if
	 *			not a video url.
	 * @since 4.0
	 */
	function wprss_convert_video_permalink( $permalink ) {
		// CHECK PERMALINK FOR VIDEO HOSTS : YOUTUBE, VIMEO AND DAILYMOTION
		$found_video_host = preg_match( '/http[s]?:\/\/(www\.)?(youtube|dailymotion|vimeo)\.com\/(.*)/i', $permalink, $matches );

		// If video host was found
		if ( $found_video_host !== 0 && $found_video_host !== FALSE ) {
		
			// Get general options
			$options = get_option( 'wprss_settings_general' );
			// Get the video link option entry, or false if it does not exist
			$video_link = ( isset($options['video_link']) )? $options['video_link'] : 'false';
		
			// If the video link option is true, change the video URL to its repective host's embedded
			// video player URL. Otherwise, leave the permalink as is.
			if ( strtolower( $video_link ) === 'true' ) {
				$host = $matches[2];
				switch( $host ) {
					case 'youtube':
						preg_match( '/(&|\?)v=([^&]+)/', $permalink, $yt_matches );
						$permalink = 'http://www.youtube.com/embed/' . $yt_matches[2];
						break;
					case 'vimeo':
						preg_match( '/(\d*)$/i', $permalink, $vim_matches );
						$permalink = 'http://player.vimeo.com/video/' . $vim_matches[0];
						break;
					case 'dailymotion':
						preg_match( '/(\.com\/)(video\/)(.*)/i', $permalink, $dm_matches );
						$permalink = 'http://www.dailymotion.com/embed/video/' . $dm_matches[3];
						break;
				}
			}
		}

		return $permalink;
	}





	/**
	 * Insert wprss_feed_item posts into the DB
	 *
	 * @since 3.0
	 */
	function wprss_items_insert_post( $items, $feed_ID ) {

		// Gather the permalinks of existing feed item's related to this feed source
		$existing_permalinks = get_existing_permalinks( $feed_ID );

		foreach ( $items as $item ) {

			// Convert the url if it is a video url and the conversion is enabled in the settings.
			$permalink = wprss_convert_video_permalink( $item->get_permalink() );

			// Save the enclosure URL
			$enclosure_url = '';
			if ( $enclosure = $item->get_enclosure(0) ) {
				if ( $enclosure->get_link() ) {
					$enclosure_url = $enclosure->get_link();
				}
			}

			/* OLD NORMALIZATION CODE - TO NORMALIZE URLS FROM PROXY URLS
			$response = wp_remote_head( $permalink );
			if ( !is_wp_error(  $response ) && isset( $response['headers']['location'] ) ) {
				$permalink = current( explode( '?', $response['headers']['location'] ) );
			}*/

			// Check if newly fetched item already present in existing feed items,
			// if not insert it into wp_posts and insert post meta.
			if ( ! ( in_array( $permalink, $existing_permalinks ) ) ) {

				// Apply filters that determine if the feed item should be inserted into the DB or not.
				$item = apply_filters( 'wprss_insert_post_item_conditionals', $item, $feed_ID, $permalink );

				// If the item is not NULL, continue to inserting the feed item post into the DB
				if ( $item !== NULL ) {
			
					$feed_item = apply_filters(
						'wprss_populate_post_data',
						array(
							'post_title'     => html_entity_decode( $item->get_title() ),
							'post_content'   => '',
							'post_status'    => 'publish',
							'post_type'      => 'wprss_feed_item',
							'post_date'      => get_date_from_gmt( $item->get_date( 'Y-m-d H:i:s' ) ), 
							'post_date_gmt'  => $item->get_date( 'Y-m-d H:i:s' ),
						),
						$item
					);
				
					if ( defined('ICL_SITEPRESS_VERSION') )
						@include_once( WP_PLUGIN_DIR . '/sitepress-multilingual-cms/inc/wpml-api.php' );
					if ( defined('ICL_LANGUAGE_CODE') )
						$_POST['icl_post_language'] = $language_code = ICL_LANGUAGE_CODE;
					
					// Create and insert post object into the DB
					$inserted_ID = wp_insert_post( $feed_item );

					if ( !is_wp_error( $inserted_ID ) ) {

						if ( is_object( $inserted_ID ) ) {
							if ( isset( $inserted_ID['ID'] ) ) {
								$inserted_ID = $inserted_ID['ID'];
							}
							elseif ( isset( $inserted_ID->ID ) ) {
								$inserted_ID = $inserted_ID->ID;
							}
						}

						// Create and insert post meta into the DB
						wprss_items_insert_post_meta( $inserted_ID, $item, $feed_ID, $permalink, $enclosure_url );

						// Remember newly added permalink
						$existing_permalinks[] = $permalink;
					}
					else {
						wprss_log_obj( 'Failed to insert post', $feed_item, 'wprss_items_insert_post > wp_insert_post' );
					}
				}
			}
		}
	}





	/**
	 * Inserts the appropriate post meta for feed items.
	 *
	 * Called from 'wprss_items_insert_post'
	 *
	 * @since 2.3
	 */
	function wprss_items_insert_post_meta( $inserted_ID, $item, $feed_ID, $permalink, $enclosure_url ) {
		update_post_meta( $inserted_ID, 'wprss_item_permalink', $permalink );
		update_post_meta( $inserted_ID, 'wprss_item_enclosure', $enclosure_url );
		update_post_meta( $inserted_ID, 'wprss_item_description', $item->get_description() );
		update_post_meta( $inserted_ID, 'wprss_item_date', $item->get_date( 'U' ) ); // Save as Unix timestamp format
		update_post_meta( $inserted_ID, 'wprss_feed_id', $feed_ID);
		do_action( 'wprss_items_create_post_meta', $inserted_ID, $item, $feed_ID );
	}







	/**
	 * Fetches all feed items from all feed sources.
	 * Iteratively calls 'wprss_fetch_insert_single_feed_items' for all feed sources.
	 *
	 * This function is used by the cron job or the debugging functions to get all feeds from all feed sources
	 *
	 * @param $all  If set to TRUE, the function will pull from all feed sources, regardless of their individual
	 *              update interval. If set to FALSE, only feed sources using the global update system will be updated.
	 *              (Optional) Default: TRUE.
	 * @since 3.0
	 */
	function wprss_fetch_insert_all_feed_items( $all = TRUE ) {
		// Get all feed sources
		$feed_sources = wprss_get_all_feed_sources();

		if( $feed_sources->have_posts() ) {
			// Start by getting one feed source, we will cycle through them one by one,
			// fetching feed items and adding them to the database in each pass
			while ( $feed_sources->have_posts() ) {
				$feed_sources->the_post();

				$interval = get_post_meta( get_the_ID(), 'wprss_update_interval', TRUE );
				$using_global_interval = ( $interval === wprss_get_default_feed_source_update_interval() || $interval === '' );

				// Check if fetching from all, or if feed source uses the global interval
				if ( $all === TRUE || $using_global_interval ) {
					wp_schedule_single_event( time(), 'wprss_fetch_single_feed_hook', array( get_the_ID() ) );
				}
			}
			wp_reset_postdata(); // Restore the $post global to the current post in the main query
		}
	}

	/**
	 * Runs the above function with parameter FALSE
	 * 
	 * @since 3.9
	 */
	function wprss_fetch_insert_all_feed_items_from_cron() {
		wprss_fetch_insert_all_feed_items( FALSE );
	}
