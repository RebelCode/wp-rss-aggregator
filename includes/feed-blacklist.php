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


// Check if the 'blacklist' GET param is set
add_action( 'init', 'wprss_check_if_blacklist_item' );
// Register custom post type
add_action( 'init', 'wprss_blacklist_cpt' );
// Add the row actions to the targetted post type
add_filter( 'post_row_actions', 'wprss_blacklist_row_actions', 10, 1 );
// Force delete when a post is trashed
add_action( 'before_delete_post', 'wprss_blacklist_force_delete' );
// Changes the wprss_blacklist table columns
add_filter( 'manage_wprss_blacklist_posts_columns', 'wprss_blacklist_columns');
// Changes the wprss_blacklist bulk actions
add_filter('bulk_actions-edit-wprss_blacklist','wprss_blacklist_bulk_actions', 5, 1 );


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
function wprss_get_blacklist() {
	// Get the option
	$blacklist_option = get_option('wprss_blacklist');
	// If the option does not exist
	if ( $blacklist_option === FALSE || !is_array( $blacklist_option ) ) {
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
function wprss_blacklist_item( $ID ) {
	// Return if feed item is null
	if ( is_null($ID) ) return;
	
	// Get the feed item data
	$item_title = get_the_title( $ID );
	$item_permalink = get_post_meta( $ID, 'wprss_item_permalink', TRUE );
	// Prepare the data for blacklisting
	$title = apply_filters( 'wprss_blacklist_title', trim($item_title) );
	$permalink = apply_filters( 'wprss_blacklist_permalink', trim($item_permalink) );
	
	// Get the blacklisted items
	$blacklist = wprss_get_blacklist();
	// Add the item to the blacklist
	$blacklist[ $permalink ] = $title;
	
	// Delete the item
	wp_delete_post( $ID, TRUE );
	
	// Add the blacklisted item
	$id = wp_insert_post(array(
		'post_title'	=>	$title,
		'post_content'	=>	$permalink,
		'post_type'		=>	'wprss_blacklist',
		'post_status'	=>	'publish'
	));
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
	$blacklist = wprss_get_blacklist();
	// Add to the blacklist
	return isset( $blacklist[ $permalink ] );
}


/**
 * Check if the 'blacklist' GET param is set, and prepare to blacklist
 * the item.
 */
function wprss_check_if_blacklist_item() {
	// If the GET param is not set, do nothing. Return.
	if ( empty( $_GET['wprss_blacklist'] ) ) return;
	
	// Get the ID from the GET param
	$ID = $_GET['wprss_blacklist'];
	// If the post does not exist, stop. Show a message
	if ( get_post($ID) === NULL ) {
		wp_die('The item you are trying to blacklist does not exist');
	}
	
	// If the post type is not correct, 
	if ( get_post_type($ID) !== wprss_blacklist_post_type() ) {
		wp_die('The item you are trying to blacklist is not valid!');
	}
	
	wprss_blacklist_item( $ID );
	
	// Check the current page, and generate the URL query string for the page
	$paged = isset( $_GET['paged'] )? '&paged=' . $_GET['paged'] : '';
	// Get the blacklisting post type
	$post_type = wprss_blacklist_post_type();
	// Refresh the page without the GET parameter
	header( 'Location: ' . admin_url( "edit.php?post_type=$post_type" . $paged ) );
	exit();
}




/**
 * Registers the Blacklist Custom Post Type.
 * 
 * @since 4.4
 */
function wprss_blacklist_cpt() {
	register_post_type( 'wprss_blacklist', array(
		'label'				=>	'Blacklist',
		'public'			=>	false,
		'show_ui'			=>	true,
		'show_in_menu'		=>	'edit.php?post_type=wprss_feed',
		'supports'			=>	array('title'),
		'capability_type'	=>	'wprss_blacklist',
		'labels'			=>	array(
			'name'					=> __( 'Blacklist', 'wprss' ),
			'singular_name'			=> __( 'Blacklist', 'wprss' ),
			'all_items'				=> __( 'Blacklist', 'wprss' ),
			'search_items'			=> __( 'Search Blacklist', 'wprss' ),
			'not_found'				=> __( 'You do not have any items blacklisted yet!', 'wprss' ),
		)
	));
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
	
	$post_type = wprss_blacklist_post_type();
	
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
	
	// For the blacklisted item
	elseif ( get_post_type() === 'wprss_blacklist' ) {
		$remove_url = wp_nonce_url( 'post.php?post='.get_the_ID().'&action=trash' );
		$actions = array(
			'trash'	=>	str_replace( '>Trash<', '>Remove from blacklist<', $actions['trash'] )
		);
	}
	
	// Return the actions
	return $actions;
}


/**
 * 
 */
function wprss_blacklist_force_delete( $post_id ) {
	global $post_type;
	if ( $post_type === 'wprss_blacklist' ) {
		wp_delete_post( $post_id, TRUE );
	}
}


/**
 * Returns the custom columns for the blacklist post type
 * 
 * @since 4.4
 * @params array $cols The columns to filter
 * @return array The new columns
 */
function wprss_blacklist_columns( $cols ) {
	return array(
		'cb'		=>	$cols['cb'],
		'title'		=>	__( 'Title' ),
		'permalink'	=>	__( 'Permalink' )
	);
}


/**
 * Removes the bulk actions for the Blacklist post type
 * 
 * @since 4.4
 * @param array $actions The array of actions to be filtered
 * @return array An empty array
 */
function wprss_blacklist_bulk_actions( $actions ) {
	return array();
}