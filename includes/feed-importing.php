<?php
       /**
        * Functions relating to feed importing
        *
        * @package WPRSSAggregator
        */


	// Warning: Order may be important
	add_filter('wprss_normalize_permalink', 'wprss_google_news_url_fix', 8);
	add_filter('wprss_normalize_permalink', 'wprss_bing_news_url_fix', 9);
	add_filter('wprss_normalize_permalink', 'wprss_google_alerts_url_fix', 10);
	add_filter('wprss_normalize_permalink', 'wprss_convert_video_permalink', 100);

    // Adds comparators for item sorting
    add_filter('wprss_item_comparators', 'wprss_sort_comparators_default');

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
		wprss_log_obj( 'Starting import of feed', $feed_ID, null, WPRSS_LOG_LEVEL_INFO );

		global $wprss_importing_feed;
		$wprss_importing_feed = $feed_ID;
		register_shutdown_function( 'wprss_detect_exec_timeout' );

		// Check if the feed source is active.
		if ( wprss_is_feed_source_active( $feed_ID ) === FALSE && wprss_feed_source_force_next_fetch( $feed_ID ) === FALSE ) {
			// If it is not active ( paused ), return without fetching the feed items.
			wprss_log( 'Feed is not active and not forced. Import cancelled.', null, WPRSS_LOG_LEVEL_INFO );
			return;
		}
		// If the feed source is forced for next fetch, remove the force next fetch data
		if ( wprss_feed_source_force_next_fetch( $feed_ID ) ) {
			delete_post_meta( $feed_ID, 'wprss_force_next_fetch' );
			wprss_log( 'Force feed flag removed', null, WPRSS_LOG_LEVEL_SYSTEM );
		}

		$start_of_update = wprss_flag_feed_as_updating( $feed_ID );
		wprss_log_obj( 'Start of import time updated', date( 'Y-m-d H:i:s', $start_of_update), null, WPRSS_LOG_LEVEL_SYSTEM );

		// Get the feed source URL from post meta, and filter it
		$feed_url = get_post_meta( $feed_ID, 'wprss_url', true );
		wprss_log_obj( 'Original feed source URL', $feed_url, null, WPRSS_LOG_LEVEL_SYSTEM );
		$feed_url = apply_filters( 'wprss_feed_source_url', $feed_url, $feed_ID );
		wprss_log_obj( 'Actual feed source URL', $feed_url, null, WPRSS_LOG_LEVEL_INFO );

		// Get the feed limit from post meta
		$feed_limit = get_post_meta( $feed_ID, 'wprss_limit', true );
		wprss_log_obj( 'Feed limit value is', $feed_limit, null, WPRSS_LOG_LEVEL_SYSTEM );

		// If the feed has no individual limit
		if ( $feed_limit === '' || intval( $feed_limit ) <= 0 ) {
			wprss_log_obj( 'Using global limit', $feed_limit, null, WPRSS_LOG_LEVEL_NOTICE );
			// Get the global limit
			$global_limit = wprss_get_general_setting('limit_feed_items_imported');
			// If no global limit is set, mark as NULL
			if ( $global_limit === '' || intval($global_limit) <= 0 ) {
				$feed_limit = NULL;
			}
			else $feed_limit = $global_limit;
		}
		wprss_log_obj( 'Feed import limit', $feed_limit, null, WPRSS_LOG_LEVEL_INFO );

		// Filter the URL for validaty
		if ( wprss_validate_url( $feed_url ) ) {
			wprss_log_obj( 'Feed URL is valid', $feed_url, null, WPRSS_LOG_LEVEL_INFO );
			// Get the feed items from the source
			$items = wprss_get_feed_items( $feed_url, $feed_ID );

			// If got NULL, convert to an empty array
			if ( $items === NULL ) {
				$items = array();
				wprss_log( 'Items were NULL. Using empty array', null, WPRSS_LOG_LEVEL_WARNING );
			}

            // See `wprss_item_comparators` filter
            wprss_sort_items($items);

			// If using a limit ...
			if ( $feed_limit === NULL ) {
				$items_to_insert = $items;
			} else {
				$items_to_insert = array_slice( $items, 0, $feed_limit );
				wprss_log_obj( 'Sliced a segment of items', count($items_to_insert), null, WPRSS_LOG_LEVEL_SYSTEM );
			}

			// Gather the permalinks of existing feed item's related to this feed source
			$existing_permalinks = wprss_get_existing_permalinks( $feed_ID );
			wprss_log_obj( 'Retrieved existing permalinks', count( $existing_permalinks ), null, WPRSS_LOG_LEVEL_SYSTEM );

			// Check if we should only import uniquely-titled feed items.
			$existing_titles = array();
			$unique_titles = FALSE;
			if ( wprss_get_general_setting( 'unique_titles' ) ) {
				$unique_titles = TRUE;
				$existing_titles = wprss_get_existing_titles( );
				wprss_log_obj( 'Retrieved existing titles from global', count( $existing_titles ), null, WPRSS_LOG_LEVEL_SYSTEM );
			} else if ( get_post_meta( $feed_ID, 'wprss_unique_titles', true ) === 'true' ) {
				$unique_titles = TRUE;
				$existing_titles = wprss_get_existing_titles( $feed_ID );
				wprss_log_obj( 'Retrieved existing titles from feed source', count( $existing_titles ), null, WPRSS_LOG_LEVEL_SYSTEM );
			}

			// Generate a list of items fetched, that are not already in the DB
			$new_items = array();
			foreach ( $items_to_insert as $item ) {

				$permalink = wprss_normalize_permalink( $item->get_permalink(), $item, $feed_ID );
				wprss_log_obj( 'Normalized permalink', sprintf('%1$s -> %2$s', $item->get_permalink(), $permalink), null, WPRSS_LOG_LEVEL_SYSTEM );

				// Check if not blacklisted and not already imported
				$is_blacklisted = wprss_is_blacklisted( $permalink );
				$permalink_exists = array_key_exists( $permalink, $existing_permalinks );
				$title_exists = array_key_exists( $item->get_title(), $existing_titles );

				if ( $is_blacklisted === FALSE && $permalink_exists === FALSE && $title_exists === FALSE) {
					$new_items[] = $item;
					wprss_log_obj( 'Permalink OK', $permalink, null, WPRSS_LOG_LEVEL_SYSTEM );

					if ( $unique_titles ) {
						$existing_titles[$item->get_title()] = 1;
					}
				} else {
					if ( $is_blacklisted ) {
						wprss_log( 'Permalink blacklisted', null, WPRSS_LOG_LEVEL_SYSTEM );
					}
					if ( $permalink_exists ) {
						wprss_log( 'Permalink already exists', null, WPRSS_LOG_LEVEL_SYSTEM );
					}
					if ( $title_exists ) {
						wprss_log( 'Title already exists', null, WPRSS_LOG_LEVEL_SYSTEM );
					}
				}
			}

			$original_count = count( $items_to_insert );
			$new_count = count( $new_items );
			if ( $new_count !== $original_count ) {
				wprss_log_obj( 'Items filtered out', $original_count - $new_count, null, WPRSS_LOG_LEVEL_SYSTEM );
			} else {
				wprss_log( 'Items to import remained untouched. Not items already exist or are blacklisted.', null, WPRSS_LOG_LEVEL_SYSTEM );
			}

			$items_to_insert = $new_items;
            $per_import = wprss_get_general_setting('limit_feed_items_per_import');
            if (!empty($per_import)) {
                wprss_log_obj( 'Per-import limit', $per_import, null, WPRSS_LOG_LEVEL_SYSTEM );
                $items_to_insert = array_slice( $items_to_insert, 0, $per_import );
            }

			// If using a limit - delete any excess items to make room for the new items
			if ( $feed_limit !== NULL ) {
				wprss_log_obj( 'Some items may be deleted due to limit', $feed_limit, null, WPRSS_LOG_LEVEL_SYSTEM );

				// Get the number of feed items in DB, and their count
				$db_feed_items = wprss_get_feed_items_for_source( $feed_ID );
				$num_db_feed_items = $db_feed_items->post_count;

				// Get the number of feed items we can store until we reach the limit
				$num_can_insert = $feed_limit - $num_db_feed_items;
				// Calculate how many feed items we must delete before importing, to keep to the limit
				$num_new_items = count( $new_items );
				$num_feed_items_to_delete = $num_can_insert > $num_new_items
						? 0
						: $num_new_items - $num_can_insert;

				// Get an array with the DB feed items in reverse order (oldest first)
				$db_feed_items_reversed = array_reverse( $db_feed_items->posts );
				// Cut the array to get only the first few that are to be deleted ( equal to $num_feed_items_to_delete )
				$feed_items_to_delete = array_slice( $db_feed_items_reversed, 0, $num_feed_items_to_delete );
				wprss_log( sprintf( 'There already are %1$d items in the database. %2$d items can be inserted. %3$d items will be deleted', $num_db_feed_items, $num_can_insert, $num_feed_items_to_delete ), null, WPRSS_LOG_LEVEL_SYSTEM );

				// Iterate the feed items and delete them
				foreach ( $feed_items_to_delete as $key => $post ) {
					wp_delete_post( $post->ID, TRUE );
				}

				if ( $deleted_items_count = count( $feed_items_to_delete ) )
					wprss_log_obj( 'Items deleted due to limit', $deleted_items_count, null, WPRSS_LOG_LEVEL_NOTICE );
			}

			update_post_meta( $feed_ID, 'wprss_last_update', $last_update_time = time() );
			update_post_meta( $feed_ID, 'wprss_last_update_items', 0 );
			wprss_log_obj( 'Last import time updated', $last_update_time, null, WPRSS_LOG_LEVEL_SYSTEM );

			// Insert the items into the db
			if ( !empty( $items_to_insert ) ) {
				wprss_log_obj( 'There are items to insert', count($items_to_insert), null, WPRSS_LOG_LEVEL_INFO );
				wprss_items_insert_post( $items_to_insert, $feed_ID );
			}
		} else {
			wprss_log_obj('The feed URL is not valid! Please recheck', $feed_url);
		}

		$next_scheduled = get_post_meta( $feed_ID, 'wprss_reschedule_event', TRUE );

		if ( $next_scheduled !== '' ) {
			wprss_feed_source_update_start_schedule( $feed_ID );
			delete_post_meta( $feed_ID, 'wprss_reschedule_event' );
			wprss_log( 'Next update rescheduled', null, WPRSS_LOG_LEVEL_SYSTEM );
		}

		wprss_flag_feed_as_idle( $feed_ID );
		wprss_log_obj( 'Import complete', $feed_ID, __FUNCTION__, WPRSS_LOG_LEVEL_INFO );
	}


	/**
	 * Fetches the feed items from a feed at the given URL.
	 *
	 * Called from 'wprss_fetch_insert_single_feed_items'
	 *
	 * @since 3.0
	 */
	function wprss_get_feed_items( $feed_url, $source, $force_feed = FALSE ) {
		// Add filters and actions prior to fetching the feed items
		add_filter( 'wp_feed_cache_transient_lifetime' , 'wprss_feed_cache_lifetime' );
		add_action( 'wp_feed_options', 'wprss_do_not_cache_feeds' );

		/* Fetch the feed from the soure URL specified */
		$feed = wprss_fetch_feed( $feed_url, $source, $force_feed );

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
	function wprss_fetch_feed($url, $source = null, $param_force_feed = false)
    {
        // Trim the URL
        $url = trim($url);

        // Initialize the Feed
        $feed = new SimplePie();
        $feed->set_feed_url($url);
        $feed->set_autodiscovery_level(SIMPLEPIE_LOCATOR_ALL);

        // If a feed source was passed
        if ($source !== null || $param_force_feed) {
            // Get the force feed option for the feed source
            $force_feed = get_post_meta($source, 'wprss_force_feed', null);
            // If turned on, force the feed
            if ($force_feed == 'true' || $param_force_feed) {
                $feed->force_feed(null);
            }
        }

        // Set timeout limit
        $fetch_time_limit = wprss_get_feed_fetch_time_limit();
        $feed->set_timeout($fetch_time_limit);

        $feed->enable_cache(false);

        // Reference array action hook, for the feed object and the URL
        do_action_ref_array('wp_feed_options', array(&$feed, $url));

        // Prepare the tags to strip from the feed
        $tags_to_strip = apply_filters('wprss_feed_tags_to_strip', $feed->strip_htmltags, $source);
        // Strip them
        $feed->strip_htmltags($tags_to_strip);

        do_action('wprss_fetch_feed_before', $feed, $source);

        // Fetch the feed
        $feed->init();
        $feed->handle_content_type();

        do_action('wprss_fetch_feed_after', $feed);

        // Convert the feed error into a WP_Error, if applicable
        if ($feed->error()) {
            if ($source !== null) {
                $msg = sprintf(__('Failed to fetch the RSS feed. Error: %s', WPRSS_TEXT_DOMAIN), $feed->error());
                update_post_meta($source, 'wprss_error_last_import', $msg);
            }
            return new WP_Error('simplepie-error', $feed->error(), array('feed' => $feed));
        }
        // If no error, return the feed and remove any error meta
        delete_post_meta($source, 'wprss_error_last_import');
        return $feed;
	}


	/**
	 * Normalizes the given permalink.
	 *
	 * @param $permalink The permalink to normalize
	 * @return string The normalized permalink
	 * @since 4.2.3
	 */
	function wprss_normalize_permalink( $permalink, $item, $feed_ID) {
		// Apply normalization functions on the permalink
		$permalink = trim( $permalink );
		$permalink = apply_filters( 'wprss_normalize_permalink', $permalink, $item, $feed_ID);
		// Return the normalized permalink
		return $permalink;
	}


	/**
	 * Extracts the actual URL from a Google News permalink
	 *
	 * @param string $permalink The permalink to normalize.
	 * @since 4.2.3
	 */
	function wprss_google_news_url_fix($permalink) {
	    return wprss_tracking_url_fix($permalink, '!^(https?:\/\/)?' . preg_quote('news.google.com', '!') . '.*!');
	}


        /**
	 * Extracts the actual URL from a Google Alerts permalink
	 *
	 * @param string $permalink The permalink to normalize.
         * @since 4.7.3
	 */
	function wprss_google_alerts_url_fix($permalink) {
	    return wprss_tracking_url_fix($permalink, '!^(https?:\/\/)?(www\.)?' . preg_quote('google.com/url', '!') . '.*!');
	}


	/**
	 * Extracts the actual URL from a Bing permalink
	 *
	 * @param string $permalink The permalink to normalize.
	 * @since 4.2.3
	 */
	function wprss_bing_news_url_fix($permalink) {
	    return wprss_tracking_url_fix($permalink, '!^(https?:\/\/)?(www\.)?' . preg_quote('bing.com/news', '!') . '.*!');
	}


	/**
	 * Checks if the permalink is a tracking permalink based on host, and if
	 * it is, returns the normalized URL of the proper feed item article,
	 * determined by the named query argument.
	 *
	 * Fixes the issue with equivalent Google News etc. items having
	 * different URLs, that contain randomly generated GET parameters.
	 * Example:
	 *
	 * http://news.google.com/news/url?sa=t&fd=R&ct2=us&ei=V3e9U6izMMnm1QaB1YHoDA&url=http://abcd...
	 * http://news.google.com/news/url?sa=t&fd=R&ct2=us&ei=One9U-HQLsTp1Aal-oDQBQ&url=http://abcd...
	 *
	 * @param string $permalink The permalink URL to check and/or normalize.
	 * @param string|array $patterns One or an array of host names, for which the URL should be fixed.
	 * @param string Name of the query argument that specifies the actual URL.
	 * @return string The normalized URL of the original article, as indicated by the `url`
	 *					parameter in the URL query string.
	 * @since 4.2.3
	 */
	function wprss_tracking_url_fix( $permalink, $patterns, $argName = 'url' ) {
		// Parse the url
		$parsed = parse_url( urldecode( html_entity_decode( $permalink ) ) );
		$patterns = is_array($patterns) ? $patterns :array($patterns);

		// If parsing failed, return the permalink
		if ( $parsed === FALSE || $parsed === NULL ) return $permalink;

		// Determine if it's a tracking item
		$isMatch = false;
		foreach( $patterns as $_idx => $_pattern ) {
		    if( preg_match($_pattern, $permalink) ) {
			$isMatch = true;
			break;
		    }
		}

		if( !$isMatch ) return $permalink;

		// Check if the url GET query string is present
		if ( !isset( $parsed['query'] ) ) return $permalink;

		// Parse the query string
		$query = array();
		parse_str( $parsed['query'], $query );

		// Check if the url GET parameter is present in the query string
		if ( !is_array($query) || !isset( $query[$argName] ) ) return $permalink;

		return urldecode( $query[$argName] );
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
		update_post_meta( $feed_ID, 'wprss_feed_is_updating', $update_started_at = time() );
		wprss_log_obj( 'Starting import of items for feed ' . $feed_ID, $update_started_at, null, WPRSS_LOG_LEVEL_INFO );

		// Gather the permalinks of existing feed item's related to this feed source
		$existing_permalinks = wprss_get_existing_permalinks( $feed_ID );

		// Count of items inserted
		$items_inserted = 0;

		foreach ( $items as $item ) {

			// Normalize the URL
                    $permalink = $item->get_permalink(); // Link or enclosure URL
                    $permalink = htmlspecialchars_decode( $permalink ); // SimplePie encodes HTML special chars

			$permalink = wprss_normalize_permalink( $permalink, $item, $feed_ID );
			wprss_log_obj( 'Importing item', $permalink, null, WPRSS_LOG_LEVEL_INFO );
			wprss_log_obj( 'Original permalink', $item->get_permalink(), null, WPRSS_LOG_LEVEL_SYSTEM );

			// Save the enclosure URL
			$enclosure_url = '';
			if ( $enclosure = $item->get_enclosure(0) ) {
				wprss_log( 'Item has an enclosure', null, WPRSS_LOG_LEVEL_SYSTEM );
				if ( $enclosure->get_link() ) {
					$enclosure_url = $enclosure->get_link();
					wprss_log_obj( 'Enclosure has link', $enclosure_url, null, WPRSS_LOG_LEVEL_SYSTEM );
				}
			}

			/* OLD NORMALIZATION CODE - TO NORMALIZE URLS FROM PROXY URLS
			$response = wp_remote_head( $permalink );
			if ( !is_wp_error(  $response ) && isset( $response['headers']['location'] ) ) {
				$permalink = current( explode( '?', $response['headers']['location'] ) );
			}*/

			// Check if newly fetched item already present in existing feed items,
			// if not insert it into wp_posts and insert post meta.
			if ( ! ( array_key_exists( $permalink, $existing_permalinks ) ) ) {
				wprss_log( "Importing (unique) feed item (Source: $feed_ID)", null, WPRSS_LOG_LEVEL_INFO );

				// Extend the importing time and refresh the feed's updating flag to reflect that it is active
				$extend_time = wprss_flag_feed_as_updating( $feed_ID );
				$extend_time_f = date( 'Y-m-d H:i:s', $extend_time );
				$time_limit = wprss_get_item_import_time_limit();
				wprss_log( "Extended execution time limit by {$time_limit}. (Current Time: {$extend_time_f})", null, WPRSS_LOG_LEVEL_INFO );
				set_time_limit( $time_limit );

				// Apply filters that determine if the feed item should be inserted into the DB or not.
				$item = apply_filters( 'wprss_insert_post_item_conditionals', $item, $feed_ID, $permalink );

				// Check if the imported count should still be updated, even if the item is NULL
                $still_update_count = apply_filters( 'wprss_still_update_import_count', FALSE );

				// If the item is not NULL, continue to inserting the feed item post into the DB
				if ( $item !== NULL && !is_bool($item) ) {
					wprss_log( 'Using core logic', null, WPRSS_LOG_LEVEL_SYSTEM );

					// Get the date and GTM date and normalize if not valid dor not present
					$format    = 'Y-m-d H:i:s';
					$has_date  = $item->get_date( 'U' ) ? TRUE : FALSE;
					$timestamp = $has_date ? $item->get_date( 'U' ) : date( 'U' );
					$date      = date( $format, $timestamp );
					$date_gmt  = gmdate( $format, $timestamp );
					// Prepare the item data
					$feed_item = apply_filters(
						'wprss_populate_post_data',
						array(
							'post_title'     => html_entity_decode( $item->get_title() ),
							'post_content'   => '',
							'post_status'    => 'publish',
							'post_type'      => 'wprss_feed_item',
							'post_date'      => $date,
							'post_date_gmt'  => $date_gmt
						),
						$item
					);
					wprss_log( 'Post data filters applied', null, WPRSS_LOG_LEVEL_SYSTEM );

					if ( defined('ICL_SITEPRESS_VERSION') )
						@include_once( WP_PLUGIN_DIR . '/sitepress-multilingual-cms/inc/wpml-api.php' );
					if ( defined('ICL_LANGUAGE_CODE') ) {
						$_POST['icl_post_language'] = $language_code = ICL_LANGUAGE_CODE;
						wprss_log_obj( 'WPML detected. Language code determined', $language_code, null, WPRSS_LOG_LEVEL_SYSTEM );
					}

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

						// Increment the inserted items counter
						$items_inserted++;

						// Create and insert post meta into the DB
						wprss_items_insert_post_meta( $inserted_ID, $item, $feed_ID, $permalink, $enclosure_url );

						// Remember newly added permalink
						$existing_permalinks[$permalink] = 1;
						wprss_log_obj( 'Item imported', $inserted_ID, null, WPRSS_LOG_LEVEL_INFO );
					}
					else {
						update_post_meta( $source, 'wprss_error_last_import', 'An error occurred while inserting a feed item into the database.' );
						wprss_log_obj( 'Failed to insert post', $feed_item, 'wprss_items_insert_post > wp_insert_post' );
					}
				}
				// If the item is TRUE, then a hook function in the filter inserted the item.
				// increment the inserted counter
				elseif ( ( is_bool($item) && $item === TRUE ) || ( $still_update_count === TRUE && $item !== FALSE ) ) {
					$items_inserted++;
				}
			}
			else {
				wprss_log( 'Item already exists and will be skipped', null, WPRSS_LOG_LEVEL_NOTICE );
			}

			wprss_log_obj( 'Finished importing item', $permalink, null, WPRSS_LOG_LEVEL_INFO );
		}

		update_post_meta( $feed_ID, 'wprss_last_update_items', $items_inserted );
		wprss_log_obj( sprintf( 'Finished importing %1$d items for feed source', $items_inserted ), $feed_ID, null, WPRSS_LOG_LEVEL_INFO );
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

		$author = $item->get_author();
		if ( $author ) {
			update_post_meta( $inserted_ID, 'wprss_item_author', $author->get_name() );
		}

		update_post_meta( $inserted_ID, 'wprss_feed_id', $feed_ID);
		do_action( 'wprss_items_create_post_meta', $inserted_ID, $item, $feed_ID );
	}


	/**
	 * Returns the time limit for the importing of a single feed item.
	 * The value if filtered through 'wprss_item_import_time_limit'. The default value is WPRSS_ITEM_IMPORT_TIME_LIMIT.
	 *
	 * @since 4.6.6
	 * @return int The maximum amount of seconds allowed for a single feed item to import.
	 */
	function wprss_get_item_import_time_limit() {
		return apply_filters( 'wprss_item_import_time_limit', WPRSS_ITEM_IMPORT_TIME_LIMIT );
	}

	/**
	 * Returns the time limit for a feed fetch operation.
	 * The value if filtered through 'wprss_feed_fetch_time_limit'. The default value is WPRSS_FEED_FETCH_TIME_LIMIT.
	 *
	 * @since 4.6.6
	 * @return int The maximum amount of seconds allowed for an RSS feed XML document to be fetched.
	 */
	function wprss_get_feed_fetch_time_limit() {
		return apply_filters( 'wprss_feed_fetch_time_limit', WPRSS_FEED_FETCH_TIME_LIMIT );
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
		wprss_log( 'Importing from all sources...', __FUNCTION__, WPRSS_LOG_LEVEL_SYSTEM );
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


	/**
	 * Shutdown function for detecting if the PHP script reaches the maximum execution time limit
	 * while importing a feed.
	 *
	 * @since 4.6.6
	 */
	function wprss_detect_exec_timeout() {
		// Get last error
		if ( $error = error_get_last() ){
			// Check if it is an E_ERROR and if it is a max exec time limit error
			if ( $error['type'] === E_ERROR && stripos( $error['message'], 'maximum execution' ) === 0 ) {
				// If the importing process was running
				if ( array_key_exists( 'wprss_importing_feed', $GLOBALS ) && $GLOBALS['wprss_importing_feed'] !== NULL ) {
					// Get the ID of the feed that was importing
					$feed_ID = $GLOBALS['wprss_importing_feed'];
					// Perform clean up
					wprss_flag_feed_as_idle( $feed_ID );
					$msg = sprintf( __( 'The PHP script timed out while importing an item from this feed, after %d seconds.', WPRSS_TEXT_DOMAIN ), wprss_get_item_import_time_limit() );
					update_post_meta( $feed_ID, 'wprss_error_last_import', $msg );
					// Log the error
					wprss_log( 'The PHP script timed out while importing feed #' . $feed_ID, NULL, WPRSS_LOG_LEVEL_ERROR );
				}
			}
		}
	}

    /**
     * Validates a feed item.
     *
     * @since 4.11.2
     *
     * @param \SimplePie_Item|mixed $item The item to validate.
     *
     * @return \SimplePie_Item|null The item, if it passes; otherwise, null.
     */
    function wprss_item_filter_valid($item)
    {
        return $item instanceof \SimplePie_Item
                ? $item
                : null;
    }

    /**
     * Sorts items according to settings.
     *
     * Use the `wprss_item_comparators` filter to change the list of comparators
     * used to determine the new order of items. See {@see wprss_items_sort_compare_items()}.
     *
     * @since 4.11.2
     *
     * @param \SimplePie_Item[] $items The items list.
     * @param \WP_Post $feedSource The feed source, for which to sort, if any.
     */
    function wprss_sort_items(&$items, $feedSource = null)
    {
        // Callbacks used to compare items
        $comparators = apply_filters('wprss_item_comparators', array());
        if (empty($comparators)) {
            return;
        }

        try {
            usort($items, function ($itemA, $itemB) use ($comparators, $feedSource) {
                return wprss_items_sort_compare_items($itemA, $itemB, $comparators, $feedSource);
            });

            wprss_log_obj( 'Sorted', NULL, WPRSS_LOG_LEVEL_INFO );
        } catch (\InvalidArgumentException $e) {
            wprss_log( 'Error was encountered while sorting items; list remains unsorted', NULL, WPRSS_LOG_LEVEL_WARNING );
        }
    }

    /**
     * Recursively compares two items using a list of comparators.
     *
     * If a comparator determines that two items are equal, then the items are
     * evaluated using the next comparator in list, recursively until one of
     * the comparators establishes a difference between items, or the list of
     * comparators is exhausted.
     *
     * @since 4.11.2
     *
     * @param \SimplePie_Item|mixed $itemA The item being compared;
     * @param \SimplePie_Item|mixed $itemB The item being compared to;
     * @param callable[] $comparators A list of functions for item comparison.
     *
     * @return int A result usable as a return value for {@see usort()}.
     *
     * @throws \InvalidArgumentException If the comparator is not callable.
     */
    function wprss_items_sort_compare_items($itemA, $itemB, $comparators, $feedSource = null)
    {
        if (empty($comparators)) {
            return 0;
        }

        $comparator = array_shift($comparators);
        if (!is_callable($comparator)) {
            throw new \InvalidArgumentException('Comparator must be callable');
        }

        $result = call_user_func_array($comparator, array($itemA, $itemB, $feedSource));
        if (!$result) {
            return wprss_items_sort_compare_items($itemA, $itemB, $comparators);
        }

        return $result;
    }

    /**
     * Retrieves a custom field of a feed source, or a general setting if the field doesn't exist.
     *
     * @since 4.11.2
     *
     * @param string $key The key of the field or setting.
     * @param \WP_Post|null $feedSource The feed source, if any.
     * @return type
     */
    function wprss_get_source_meta_or_setting($key, $feedSource = null)
    {
        $value = null;
        if ($feedSource instanceof \WP_Post) {
            $value = $feedSource->{$key};
        }

        return $value !== null && $value !== false
                ? $value
                : wprss_get_general_setting($key);
    }

    /**
     * Determines date order of two feed items.
     *
     * Which should come first is determined by `feed_items_import_order` setting.
     *
     * @since 4.11.2
     *
     * @param \SimplePie_Item|mixed $itemA The first item.
     * @param \SimplePie_Item|mixed $itemB The second item.
     * @param \WP_Post|null $feedSource The feed source for which the items are being compared, if any.
     * @return int A comparison result for {@see usort()}.
     */
    function wprss_item_comparator_date($itemA, $itemB, $feedSource = null)
    {
        $sortOrder = wprss_get_source_meta_or_setting('feed_items_import_order', $feedSource);
        if (empty($sortOrder)) {
            return 0;
        }

        if (!wprss_item_filter_valid($itemA) || !wprss_item_filter_valid($itemB)) {
            return 0;
        }

        $aDate = intval($itemA->get_gmdate('U'));
        $bDate = intval($itemB->get_gmdate('U'));

        switch ($sortOrder) {
            case 'latest':
                if ($aDate === $bDate) {
                    return null;
                }
                return $aDate > $bDate ? -1 : 1;
                break;

            case 'oldest':
                return $aDate < $bDate ? -1 : 1;
                break;

            case '':
            default:
                return 0;
                break;
        }
    }

    /**
     * Retrieves default comparators for sorting.
     *
     * @since 4.11.2
     *
     * @param \WP_Post|null $feedSource The feed source, for which to get comparators, if any.
     *
     * @return callable[] The list of comparators.
     */
    function wprss_sort_comparators_default($feedSource = null)
    {
        $helper = wprss_wp_container()->get('wprss.admin_helper');
        $defaultArgs = array(2 => $feedSource);
        return array(
            $helper->createCommand('wprss_item_comparator_date', $defaultArgs),
        );
    }
