<?php
    /**
     * Functions relating to feed source states
     *
     * @package WPRSSAggregator
     */


    add_action( 'admin_init', 'wprss_bulk_change_state', 2 );
    /**
     * Changes the state of feed sources selected from the table bulk actions.
     * 
     * @since 4.1
     */
    function wprss_bulk_change_state() {
        // If the id and state are in POST data
        if ( isset( $_GET['post_type'] ) && (isset( $_GET['action'] ) || isset( $_GET['action2'] )) && isset( $_GET['post'] ) ) {
            // Get the action and post ids from GET request
            $action = isset($_GET['action']) && $_GET['action'] !== '-1' ? $_GET['action'] : $_GET['action2'];
            $post_ids = $_GET['post'];

            // check the action
            switch ( $action ) {
                // Activate all feed sources in $post_ids
                case 'activate':
                    foreach( $post_ids as $post_id ) {
                        wprss_activate_feed_source( $post_id );
                    }
                    // Set a transient to show the admin notice, after redirection
                    set_transient( 'wprss_notify_bulk_change_state', 'activated', 0 );
                    break;

                // Pause all feed sources in $post_ids
                case 'pause':
                    foreach( $post_ids as $post_id ) {
                        wprss_pause_feed_source( $post_id );
                    }
                    // Set a transient to show the admin notice, after redirection
                    set_transient( 'wprss_notify_bulk_change_state', 'paused', 0 );
                    break;
            }

            /* Note:
             * Transients are used since bulk actions will, after processing, case a redirect to the same page.
             * Thus, using add_action( 'all_admin_notices', ... ) will result in the notice appearing on the first request,
             * and not be shown after redirection.
             * The transient is set to show the notification AFTER redirection.
             */
        }
    }



    add_action( 'admin_init', 'check_for_state_notice_after_redirect', 1 );
    /**
     * Checks if the 'wprss_notify_bulk_change_state' transient is set.
     * If it is, it will show the appropriate admin notice
     *
     * @since 4.1
     */
    function check_for_state_notice_after_redirect() {
        $transient = get_transient( 'wprss_notify_bulk_change_state' );
        if ( $transient !== FALSE ) {
            switch ( $transient ) {
                case 'activated':
                    wprss()->getAdminAjaxNotices()->addNotice('bulk_feed_activated');
                    break;
                case 'paused':
                    wprss()->getAdminAjaxNotices()->addNotice('bulk_feed_paused');
                    break;
            }
            delete_transient( 'wprss_notify_bulk_change_state' );
        }
    }


    
    /**
     * Activates the feed source. Runs on a schedule.
     * 
     * @param $feed_id  The of of the wprss_feed
     * @since 3.7
     */
    function wprss_activate_feed_source( $feed_id ) {
        update_post_meta( $feed_id, 'wprss_state', 'active' );
        update_post_meta( $feed_id, 'wprss_activate_feed', '' );

        // Add an action hook, so functions can be run when a feed source is activated
        do_action( 'wprss_on_feed_source_activated', $feed_id );
    }


    /**
     * Pauses the feed source. Runs on a schedule.
     * 
     * @param $feed_id  The of of the wprss_feed
     * @since 3.7
     */
    function wprss_pause_feed_source( $feed_id ) {
        update_post_meta( $feed_id, 'wprss_state', 'paused' );
        update_post_meta( $feed_id, 'wprss_pause_feed', '' );

        // Add an action hook, so functions can be run when a feed source is paused
        do_action( 'wprss_on_feed_source_paused', $feed_id );
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
