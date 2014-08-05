<?php

/**
 * This file contains all functions relating to the blacklisting of
 * imported feeds items.
 * 
 * Blacklisting a feed item is in essence nothing more than a saved list
 * of feed items. When a feed item is imported, its normalized permalink
 * is tested against this list, and if found, the feed item is not
 * imported. Admins can add items to the blacklist, to prevent them
 * from being imported again.
 * 
 * @package WP RSS Aggregator
 * @since 4.4
 */


// Add the row actions to the targetted post type
add_filter( 'post_row_actions', 'wprss_blacklist_row_actions', 10, 1 );


/**
 * Returns the post type being used or blacklisting.
 * 
 * @since 4.4
 * @return string The post type being used for blacklisting.
 */
function wprss_blacklist_post_type() {
	// Return the post type - allow filter
	return apply_filters( 'wprss_blacklist_post_type', 'wprss_feed_item' );
}


/**
 * Retrieves the blacklisted items.
 * 
 * @since 4.4
 * @return array An associative array of blacklisted item, each entry
 * 		having the key as the permalink, and the value as the title. 
 */
function wprss_get_blacklisted_items() {
	// Get the option
	$blacklist_option = get_option('wprss_blacklist');
	// If the option does not exist
	if ( $blacklist_option === FALSE ) {
		// create it
		update_option( 'wprss_blacklist', array() );
		$blacklist_option = array();
	}
	return $blacklist_option;
}


/**
 * Creates a blacklist entry for the given feed item.
 * 
 * @since 4.4
 * @param int|string The ID of the feed item to add to the blacklist
 */
function wprss_blacklist_item( $item ) {
	// Return if feed item is null
	if ( is_null($item) ) return;
	
	// Get the feed item data
	$item_title = get_the_title( $item );
	$item_permalink = get_post_meta( $item, 'wprss_item_permalink', TRUE );
	// Prepare the data for blacklisting
	$title = apply_filters( 'wprss_blacklist_title', trim($item_title) );
	$permalink = apply_filters( 'wprss_blacklist_permalink', trim($item_permalink) );
	
	// Get the blacklisted items
	$blacklist = wprss_get_blacklisted_items();
	// Add to the blacklist
	$blacklist[ $permalink ] = $title;
	
	// Update the option
	update_option( 'wprss_blacklist', $blacklist );
}


/**
 * Determines whether the given item is blacklist.
 * 
 * @since 4.4
 * @param string $permalink The permalink to look for in the saved option
 * @return bool TRUE if the permalink is found, FALSE otherwise.
 */
function wprss_is_blacklisted( $permalink ) {
	// Get the blacklisted items
	$blacklist = wprss_get_blacklisted_items();
	// Add to the blacklist
	return isset( $blacklist[ $permalink ] );
}


/**
 * Adds the row actions to the targetted post type.
 * Default post type = wprss_feed_item
 * 
 * @since 4.4
 * @param array $actions The row actions to be filtered
 * @return array The new filtered row actions
 */
function wprss_blacklist_row_actions( $actions ) {
	// Check the current page, and generate the URL query string for the page
	$paged = isset( $_GET['paged'] )? '&paged=' . $_GET['paged'] : '';
	
	// Get the post type - allow filters
	$post_type = apply_filters( 'wprss_blacklist_post_type', 'wprss_feed_item' );
	
	// Check the post type
	if ( get_post_type() == $post_type ) {
		// Unset the trash action
		$trash_action = $actions['trash'];
		unset( $actions['trash'] );
		
		// Get the Post ID
		$ID = get_the_ID();
		
		// Prepare the blacklist URL
		$url = apply_filters(
			'wprss_blacklist_row_action_url',
			admin_url( "edit.php?post_type=$post_type&wprss_blacklist=$ID" ),
			$ID
		) . $paged;
		// Prepare the text
		$text = apply_filters( 'wprss_blacklist_row_action_text', 'Blacklist' );
		$text = __( $text, 'wprss' );
		
		// Add the blacklist action
		$actions['blacklist-item'] = "<a href='$url'>$text</a>";
		// Add the trash action
		$actions['trash'] = $trash_action;
	}
	
	// Return the actions
	return $actions;
}
