<?php

    /**
     * Functions relating to feed importing
     *
     * @package WPRSSAggregator
     */

    use Psr\Log\LogLevel;

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
        $logger = wpra_get_logger($feed_ID);

		$logger->info('Starting import');

		global $wprss_importing_feed;
		$wprss_importing_feed = $feed_ID;
		register_shutdown_function( 'wprss_detect_exec_timeout' );

		// Check if the feed source is active.
		if ( wprss_is_feed_source_active( $feed_ID ) === FALSE && wprss_feed_source_force_next_fetch( $feed_ID ) === FALSE ) {
            $logger->info('Feed is not active. Finished');
			return;
		}

		// If the feed source is forced for next fetch, remove the force next fetch data
		if ( wprss_feed_source_force_next_fetch( $feed_ID ) ) {
			delete_post_meta( $feed_ID, 'wprss_force_next_fetch' );
		}

		// Get the feed source URL from post meta, and filter it
		$feed_url = get_post_meta( $feed_ID, 'wprss_url', true );
		$feed_url = apply_filters( 'wprss_feed_source_url', $feed_url, $feed_ID );
		$logger->debug('Feed source URL: {0}', [$feed_url]);

		// Get the feed limit from post meta
		$feed_limit = get_post_meta( $feed_ID, 'wprss_limit', true );

		// If the feed has no individual limit
		if ( $feed_limit === '' || intval( $feed_limit ) <= 0 ) {
			// Get the global limit
			$global_limit = wprss_get_general_setting('limit_feed_items_imported');
			// If no global limit is set, mark as NULL
			if ( $global_limit === '' || intval($global_limit) <= 0 ) {
				$feed_limit = NULL;
			}
			else $feed_limit = $global_limit;
		}

		$logger->debug('Feed item import limit: {0}', [$feed_limit]);

		// Filter the URL for validaty
		if ( ! wprss_validate_url( $feed_url ) ) {
		    $logger->error('Feed URL is not valid!');
        } else {
			// Get the feed items from the source
			$items = wprss_get_feed_items( $feed_url, $feed_ID );

			// If got NULL, convert to an empty array
			if ( $items === NULL ) {
                $items_to_insert = array();
			} else {
                // See `wprss_item_comparators` filter
                wprss_sort_items($items);

                // If using a limit ...
                if ( $feed_limit === NULL ) {
                    $items_to_insert = $items;
                } else {
                    $items_to_insert = array_slice( $items, 0, $feed_limit );
                    $logger->info('Fetched {0} items. Got {1} items after applying limit', [
                        count($items),
                        count($items_to_insert)
                    ]);
                }
            }

			// Gather the permalinks of existing feed item's related to this feed source
			$existing_permalinks = wprss_get_existing_permalinks( $feed_ID );

			// Check if we should only import uniquely-titled feed items.
			$existing_titles = array();
			$unique_titles = FALSE;
			if ( wprss_get_general_setting( 'unique_titles' ) ) {
				$unique_titles = TRUE;
				$existing_titles = wprss_get_existing_titles( );
			} else if ( get_post_meta( $feed_ID, 'wprss_unique_titles', true ) === 'true' ) {
				$unique_titles = TRUE;
				$existing_titles = wprss_get_existing_titles( $feed_ID );
			}

			// Generate a list of items fetched, that are not already in the DB
			$new_items = array();
			foreach ( $items_to_insert as $item ) {
			    $item_title = $item->get_title();

				$permalink = wprss_normalize_permalink( $item->get_permalink(), $item, $feed_ID );
				$logger->debug('Checking item "{0}"', [$item_title]);

				// Check if not blacklisted and not already imported
				$is_blacklisted = wprss_is_blacklisted( $permalink );
				$permalink_exists = array_key_exists( $permalink, $existing_permalinks );
				$title_exists = array_key_exists( $item->get_title(), $existing_titles );

				if ( $is_blacklisted === FALSE && $permalink_exists === FALSE && $title_exists === FALSE) {
					$new_items[] = $item;

					if ( $unique_titles ) {
						$existing_titles[$item->get_title()] = 1;
					}
				} else {
					if ( $is_blacklisted ) {
					    $logger->debug('Item "{0}" is blacklisted', [$item_title]);
					}
					if ( $permalink_exists ) {
                        $logger->debug('Item "{0}" already exists in the database', [$item_title]);
					}
					if ( $title_exists ) {
                        $logger->debug('An item with the title "{0}" already exists', [$item_title]);
					}
				}
			}

			$original_count = count( $items_to_insert );
			$new_count = count( $new_items );

			if ( $new_count !== $original_count ) {
			    $logger->debug('{0} will be skipped', [$original_count - $new_count]);
			}

			$items_to_insert = $new_items;
            $per_import = wprss_get_general_setting('limit_feed_items_per_import');
            if (!empty($per_import)) {
                $logger->debug('Applying per-import item limit of {0} items', [$per_import]);
                $items_to_insert = array_slice( $items_to_insert, 0, $per_import );
            }

			// If using a limit - delete any excess items to make room for the new items
			if ( $feed_limit !== NULL ) {
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

				// Iterate the feed items and delete them
                $num_items_deleted = 0;
				foreach ( $feed_items_to_delete as $key => $post ) {
					wp_delete_post( $post->ID, TRUE );
					$num_items_deleted++;
				}

				if ($num_items_deleted > 0) {
                    $logger->info('Deleted the oldest {0} items from the database', [$num_items_deleted]);
                }
			}

			update_post_meta( $feed_ID, 'wprss_last_update', $last_update_time = time() );
			update_post_meta( $feed_ID, 'wprss_last_update_items', 0 );

			// Insert the items into the db
			if ( !empty( $items_to_insert ) ) {
				wprss_items_insert_post( $items_to_insert, $feed_ID );
			}
		}

		$next_scheduled = get_post_meta( $feed_ID, 'wprss_reschedule_event', TRUE );

		if ( $next_scheduled !== '' ) {
			wprss_feed_source_update_start_schedule( $feed_ID );
			delete_post_meta( $feed_ID, 'wprss_reschedule_event' );
			$logger->info('Scheduled next update');
		}

		wprss_flag_feed_as_idle( $feed_ID );

		$logger->info('Imported completed!');

        $wprss_importing_feed = null;
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

		wpra_get_logger($source)->error('Failed to fetch the feed from {0}. Error: {1}', [
            $feed_url,
            $feed->get_error_message()
        ]);

        return NULL;
	}

//add_action ('cron_request', 'wpse_cron_add_xdebug_cookie', 10, 2) ;

/**
 * Allow debugging of wp_cron jobs
 *
 * @param array $cron_request_array
 * @param string $doing_wp_cron
 *
 * @return array $cron_request_array with the current XDEBUG_SESSION cookie added if set
 */
function wpse_cron_add_xdebug_cookie ($cron_request_array, $doing_wp_cron)
{
    if (empty ($_COOKIE['XDEBUG_SESSION'])) {
        return ($cron_request_array) ;
    }

    if (empty ($cron_request_array['args']['cookies'])) {
        $cron_request_array['args']['cookies'] = array () ;
    }
    $cron_request_array['args']['cookies']['XDEBUG_SESSION'] = $_COOKIE['XDEBUG_SESSION'] ;

    return ($cron_request_array) ;
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
            $force_feed = get_post_meta($source, 'wprss_force_feed', true);
            // If turned on, force the feed
            if ($force_feed == 'true' || $param_force_feed) {
                $feed->force_feed(true);
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
						$permalink = 'https://www.youtube.com/embed/' . $yt_matches[2];
						break;
					case 'vimeo':
						preg_match( '/(\d*)$/i', $permalink, $vim_matches );
						$permalink = 'https://player.vimeo.com/video/' . $vim_matches[0];
						break;
					case 'dailymotion':
						preg_match( '/(\.com\/)(video\/)(.*)/i', $permalink, $dm_matches );
						$permalink = 'https://www.dailymotion.com/embed/video/' . $dm_matches[3];
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
		$logger = wpra_get_logger($feed_ID);

		// Gather the permalinks of existing feed item's related to this feed source
		$existing_permalinks = wprss_get_existing_permalinks( $feed_ID );

		// Count of items inserted
		$items_inserted = 0;

		foreach ( $items as $item ) {

			// Normalize the URL
            $permalink = $item->get_permalink(); // Link or enclosure URL
            $permalink = htmlspecialchars_decode( $permalink ); // SimplePie encodes HTML special chars

            $logger->debug('Beginning import for "{0}"', [$item->get_title()]);

			$permalink = wprss_normalize_permalink( $permalink, $item, $feed_ID );

			// Save the enclosure URL
			$enclosure_url = '';
			if ( $enclosure = $item->get_enclosure(0) ) {

				if ( $enclosure->get_link() ) {
					$enclosure_url = $enclosure->get_link();

                    $logger->debug('Item "{0}" has an enclosure link: {1}', [
                        $item->get_title(),
                        $enclosure_url
                    ]);
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
				// Extend the importing time and refresh the feed's updating flag to reflect that it is active
				$time_limit = wprss_get_item_import_time_limit();
				set_time_limit( $time_limit );

				// Apply filters that determine if the feed item should be inserted into the DB or not.
				$item = apply_filters( 'wprss_insert_post_item_conditionals', $item, $feed_ID, $permalink );

				// Check if the imported count should still be updated, even if the item is NULL
                $still_update_count = apply_filters( 'wprss_still_update_import_count', FALSE );

				// If the item is not NULL, continue to inserting the feed item post into the DB
				if ( $item !== NULL && !is_bool($item) ) {
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

					if ( defined('ICL_SITEPRESS_VERSION') )
						@include_once( WP_PLUGIN_DIR . '/sitepress-multilingual-cms/inc/wpml-api.php' );
					if ( defined('ICL_LANGUAGE_CODE') ) {
						$_POST['icl_post_language'] = $language_code = ICL_LANGUAGE_CODE;

						$logger->debug('Detected WPML with language code {0]', [$language_code]);
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
					}
					else {
						update_post_meta( $feed_ID, 'wprss_error_last_import', 'An error occurred while inserting a feed item into the database.' );

						$logger->error('Failed to save item "{0}" into the database', [$item->get_title()]);
					}
				}
				// If the item is TRUE, then a hook function in the filter inserted the item.
				// increment the inserted counter
				elseif ( ( is_bool($item) && $item === TRUE ) || ( $still_update_count === TRUE && $item !== FALSE ) ) {
					$items_inserted++;
				}
			}

			if (is_object($item) && !is_wp_error($item)) {
                $logger->info('Imported "{0}"', [$item->get_title()]);
            }
		}

		update_post_meta( $feed_ID, 'wprss_last_update_items', $items_inserted );
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
	    wpra_get_logger()->info('Beginning import for all feed sources');
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
        global $wprss_importing_feed;
        $feed_ID = (isset($wprss_importing_feed) && !empty($wprss_importing_feed))
            ? $wprss_importing_feed
            : null;

        if ($feed_ID === null) {
            return;
        }

        // Remove the "importing" flag from the feed source
        wprss_flag_feed_as_idle($feed_ID);

        // If no error, stop
        $error = error_get_last();
        if (empty($error)) {
            return;
        }

        $msg = sprintf(
            __('The importing process failed after %d seconds with the message: "%s"', 'wprss'),
            wprss_get_item_import_time_limit(),
            $error['message']
        );
        // Save the error in the feed source's meta and the plugin log
        update_post_meta($feed_ID, 'wprss_error_last_import', $msg);
        wpra_get_logger($feed_ID)->error($msg);
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
        } catch (\InvalidArgumentException $e) {
            wpra_get_logger($feedSource)->warning('Encountered an error while sorting the database items: {0}', [
                $e->getMessage()
            ]);
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
