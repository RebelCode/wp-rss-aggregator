<?php
	/**
	 * Functions relating to feed importing
	 *
	 * @package WPRSSAggregator
	 */



	/**
	 * A clone of the function 'fetch_feed' in wp-includes/feed.php [line #529]
	 *
	 * @since 3.5
	 */
	function wprss_fetch_feed( $url ) {
		require_once ( ABSPATH . WPINC . '/class-feed.php' );

		$feed = new SimplePie();

		// Commented out Sanitization, due to a conflict with google RSS image URLS.
		// With sanitization on, the urls get truncated from the front.

		// $feed->set_sanitize_class( 'WP_SimplePie_Sanitize_KSES' );
		// We must manually overwrite $feed->sanitize because SimplePie's
		// constructor sets it before we have a chance to set the sanitization class
		// $feed->sanitize = new WP_SimplePie_Sanitize_KSES();

		$feed->set_cache_class( 'WP_Feed_Cache' );
		$feed->set_file_class( 'WP_SimplePie_File' );

		$feed->set_feed_url( $url );

		$feed->set_cache_duration( apply_filters( 'wp_feed_cache_transient_lifetime', 12 * HOUR_IN_SECONDS, $url ) );
		do_action_ref_array( 'wp_feed_options', array( &$feed, $url ) );
		$feed->init();
		$feed->handle_content_type();

		if ( $feed->error() ) {
			return new WP_Error( 'simplepie-error', $feed->error() );
		}

		return $feed;
	}


	/**
	 * Fetch the feeds from a feed item url
	 *
	 * @since 3.0
	 */
	function wprss_get_feed_items( $feed_url ) {
		$general_settings = get_option( 'wprss_settings_general' );
		$feed_item_limit = $general_settings['limit_feed_items_imported'];
		
		// Don't fetch the feed if feed item limit is 0, there's no need, huge speed improvement
		// if ( $feed_item_limit === '' ) return;

		add_filter( 'wp_feed_cache_transient_lifetime' , 'wprss_feed_cache_lifetime' );

		/* Disable caching of feeds */
		add_action( 'wp_feed_options', 'wprss_do_not_cache_feeds' );
		/* Fetch the feed from the soure URL specified */
		$feed = wprss_fetch_feed( $feed_url );
		//$feed = new SimplePie();
		//$feed->set_feed_url( $feed_url );
		//$feed->init();
		/* Remove action here because we only don't want it active feed imports outside of our plugin */
		remove_action( 'wp_feed_options', 'wprss_do_not_cache_feeds' );

		//$feed = wprss_fetch_feed( $feed_url );
		remove_filter( 'wp_feed_cache_transient_lifetime' , 'wprss_feed_cache_lifetime' );
		
		if ( !is_wp_error( $feed ) ) {

			// Figure out how many total items there are, but limit it to the number of items set in options.
			$maxitems = $feed->get_item_quantity( $feed_item_limit );

			if ( $maxitems == 0 ) { return; }

			// Build an array of all the items, starting with element 0 (first element).
			$items = $feed->get_items( 0, $maxitems );
			return $items;
		}

		else {
			wprss_log( 'Failed to fetch feed "' . $url . '"' );
			return;
		}
	}


	/**
	 * Insert a WPRSS feed item post
	 *
	 * @since 3.0
	 */
	function wprss_items_insert_post( $items, $feed_ID ) {

		// Gather the permalinks of existing feed item's related to this feed source
		$existing_permalinks = get_existing_permalinks( $feed_ID );

		foreach ( $items as $item ) {

			// normalize permalink to pass through feed proxy URL
			$permalink = $item->get_permalink();

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


			/*
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
							'post_title'     => $item->get_title(),
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
						wprss_items_insert_post_meta( $inserted_ID, $item, $feed_ID, $permalink );

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
	 * Creates meta entries for feed items while they are being imported
	 *
	 * @since 2.3
	 */
	function wprss_items_insert_post_meta( $inserted_ID, $item, $feed_ID, $feed_url) {
		update_post_meta( $inserted_ID, 'wprss_item_permalink', $feed_url );
		update_post_meta( $inserted_ID, 'wprss_item_description', $item->get_description() );
		update_post_meta( $inserted_ID, 'wprss_item_date', $item->get_date( 'U' ) ); // Save as Unix timestamp format
		update_post_meta( $inserted_ID, 'wprss_feed_id', $feed_ID);
		do_action( 'wprss_items_create_post_meta', $inserted_ID, $item, $feed_ID );
	}