<?php

add_action( 'wp_ajax_wprss_feed_source_table_ajax', 'wprss_feed_source_updates');
/**
 *
 */
function wprss_feed_source_updates() {
	$response = array();
	
	if ( ! current_user_can( 'edit_feed_sources' ) ) return $response;

	if ( empty($_POST['wprss_heartbeat']) ) return $response;

	// Get the wprss heartbeat data and extract the data
	$wprss_heartbeat = $_POST['wprss_heartbeat'];
	$action = $wprss_heartbeat['action'];
	$params = $wprss_heartbeat['params'];

	// Perform the action specified by the heartbeat data
	switch( $action ) {
		/* FEED SOURCE UPDATING STATUS
		 * Used to determine whether or not to show the updating icon in the feed source table.
		 */
		case 'feed_sources':
			// Prepare array of IDs for feed sources currently updating
			$feed_sources_data = array();
			// Iterate all feed sources
			foreach ( $params as $feed_id ) {
				$feed_sources_data[$feed_id] = array();
				$feed_source_data = &$feed_sources_data[$feed_id];

				// Check if the feed source is updating
				$next_fetch = wprss_get_next_feed_source_update($feed_id);
                $fetches_soon = $next_fetch !== false && $next_fetch < 2 && $next_fetch > 0;
				$is_fetching = wprss_is_feed_source_updating($feed_id);
                $is_deleting = wprss_is_feed_source_deleting($feed_id);
                $feed_source_data['fetching'] = $fetches_soon || $is_fetching;
                $feed_source_data['deleting'] = $is_deleting;

				// Add the number of imported items
				$items = wprss_get_feed_items_for_source( $feed_id );
				$feed_source_data['items'] = $items->post_count;
				// Update the meta field 
				update_post_meta( $feed_id, 'wprss_items_imported', $items->post_count );

				// Add the next update time
				$next_update = wprss_get_next_feed_source_update( $feed_id );
				$update_interval = get_post_meta( $feed_id, 'wprss_update_interval', TRUE );
				// If using the global interval, get the timestamp of the next global update
				if ( $update_interval === wprss_get_default_feed_source_update_interval() || $update_interval === '' ) {
					$next_update = wp_next_scheduled( 'wprss_fetch_all_feeds_hook', array() );
				}
				// Set the text appropriately
				if ( ! wprss_is_feed_source_active( $feed_id ) ) {
					$feed_source_data['next-update'] = __( '...', 'wprss' );
				}
				elseif( $next_update === FALSE ) {
					$feed_source_data['next-update'] = __( 'None', 'wprss' );
					wprss_activate_feed_source( $feed_id );
				}
				else {
				    $time_diff = absint($next_update - time());

					$feed_source_data['next-update'] = ($time_diff > 1)
                        ? human_time_diff( $next_update, time() )
                        : _x('now', 'Next update: now', 'wprss');
				}
				// Update the meta field
				update_post_meta( $feed_id, 'wprss_next_update', $feed_source_data['next-update'] );

				// Add the last update information
				$last_update = get_post_meta( $feed_id, 'wprss_last_update', TRUE );
				$last_update_items = get_post_meta( $feed_id, 'wprss_last_update_items', TRUE );

            	$feed_source_data['last-update'] = ( $last_update === '' )? '' : human_time_diff( $last_update, time() );
            	$feed_source_data['last-update-imported'] = $last_update_items;

            	// Add any error info
            	$errors = get_post_meta( $feed_id, 'wprss_error_last_import', true );
            	$feed_source_data['errors'] = $errors;
			}
			// Send back all the IDs
			$response['wprss_feed_sources_data'] = $feed_sources_data;
			break;
	}
	// Return the response
	die( json_encode($response) );
}
