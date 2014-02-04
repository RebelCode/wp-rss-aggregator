<?php
    /**
     * Functions relating to feed source states
     *
     * @package WPRSSAggregator
     */


    add_action( 'init', 'wprss_change_feed_state' );
    /**
     * Changes the state of a feed source, using POST data
     * 
     * @since 3.7
     */
    function wprss_change_feed_state() {
        // If the id and state are in POST data
        if ( isset( $_GET['wprss-feed-id'] ) ) {
            // Get the id and state
            $feed_ID = $_GET['wprss-feed-id'];
            // Change the state
            if ( wprss_is_feed_source_active( $feed_ID ) ) {
                wprss_pause_feed_source( $feed_ID );
            } else {
                wprss_activate_feed_source( $feed_ID );
            }
            // Check for a redirect
            if ( isset( $_GET['wprss-redirect'] ) && $_GET['wprss-redirect'] == '1' ) {
                wp_redirect( admin_url( 'edit.php?post_type=wprss_feed', 301 ) );
                exit();
            }
        }
    }


    /**
     * Returns whether or not a feed source is active.
     * 
     * @param $source_id    The ID of the feed soruce
     * @return boolean
     * @since 3.7
     */
    function wprss_is_feed_source_active( $source_id ) {
        $state = get_post_meta( $source_id, 'wprss_state', TRUE );
        return ( $state === '' || $state === 'active' );
    }