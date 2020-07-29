<?php
    /**
     * Functions for the admin section, columns and row actions
     *
     * @package WP RSS Aggregator
     */

    // Adds the "active" class to the feed source list table rows, for active feed sources
    add_filter( 'post_class', function( $classes, $class, $postId ) {
        $post = get_post($postId);

        if ($post->post_type !== 'wprss_feed') {
            return $classes;
        }

        if (wprss_is_feed_source_active($postId)) {
            $classes[] = 'active';
        }

        return $classes;
    }, 10, 3 );

    add_filter( 'manage_wprss_feed_posts_columns', 'wprss_set_feed_custom_columns', 20, 1 );
    /**
     * Set up the custom columns for the wprss_feed list
     *
     * @since 2.0
     */
    function wprss_set_feed_custom_columns( $columns ) {
        $isTrashPage = filter_input(INPUT_GET, 'post_status') === 'trash';

        $columns = array(
            'cb'          =>  '<input type="checkbox" />',
        );


        if (!$isTrashPage) {
            $columns['state'] =  __( 'State', WPRSS_TEXT_DOMAIN );
        }

        $columns['title'] = __( 'Name', WPRSS_TEXT_DOMAIN );

        $columns = apply_filters( 'wprss_set_feed_custom_columns', $columns );

        if (!$isTrashPage) {
            $columns['updates'] = __( 'Updates', WPRSS_TEXT_DOMAIN );
            $columns['feed-count'] = __( apply_filters( 'wprss_feed_items_count_column', 'Imported items' ), WPRSS_TEXT_DOMAIN );
        }

        return apply_filters( 'wprss_feed_columns', $columns );
    }


    add_action( "manage_wprss_feed_posts_custom_column", "wprss_show_custom_columns", 10, 2 );
    /**
     * Show up the custom columns for the wprss_feed list
     *
     * @since 2.0
     */
    function wprss_show_custom_columns( $column, $post_id ) {

      switch ( $column ) {
        case 'state':
            $switch_title = __('Activate or pause auto importing for this feed', 'wprss');
            ?>
            <div class="wprss-feed-state-container" title="<?php echo esc_attr($switch_title); ?>">
                <label class="wprss-switch">
                    <input type="checkbox"
                           class="wprss-toggle-feed-state"
                           autocomplete="off"
                           value="<?php echo esc_attr($post_id); ?>"
                           <?php checked(true, wprss_is_feed_source_active($post_id)) ?>
                    />
                    <span class="wprss-switch-slider"></span>
                </label>
            </div>

            <?php
            $feed_type = 'rss';
            $feed_icon = 'rss';
            $icon_title = __('Normal RSS Feed', 'wprss');

            if (wprss_is_feed_youtube($post_id)) {
                $feed_type = 'yt';
                $feed_icon = 'video-alt3';
                $icon_title = __('YouTube Feed', 'wprss');
            }
            ?>

            <div class="wprss-feed-source-type wprss-feed-source-type-<?php echo $feed_type ?>"
                 title="<?php echo esc_attr($icon_title) ?>"
            >
                <span class="dashicons dashicons-<?php echo $feed_icon ?>"></span>
            </div>
            <?php

          break;

        case 'updates':
            // Get the update interval
            $update_interval = get_post_meta( $post_id, 'wprss_update_interval', TRUE );
            // Get the last updated and next update data
            $last_update = get_post_meta( $post_id, 'wprss_last_update', TRUE );
            $last_update_items = get_post_meta( $post_id, 'wprss_last_update_items', TRUE );
            $next_update = wprss_get_next_feed_source_update( $post_id );

            // If using the global interval, get the timestamp of the next global update
            if ( $update_interval === wprss_get_default_feed_source_update_interval() || $update_interval === '' ) {
              $next_update = wp_next_scheduled( 'wprss_fetch_all_feeds_hook', array() );
            }

		  	// Update the meta field
		  	if ( wprss_is_feed_source_active( $post_id ) ) {
				$next_update_text = $next_update === FALSE ? __( 'None', WPRSS_TEXT_DOMAIN ) : human_time_diff( $next_update, time() );
			} else {
				$next_update_text = __( '...', 'wprss' );
			}
		  	update_post_meta( $post_id, 'wprss_next_update', $next_update_text );

            $timeago = empty($last_update) ? '' : human_time_diff( $last_update, time() );
            ?>

            <p class="next-update-container">
                <?php _e( 'Next update in', WPRSS_TEXT_DOMAIN ) ?>
                <code class="next-update">
                   	<?php echo $next_update_text; ?>
                </code>
            </p>

            <p class="last-update-container"
               style="display: <?php echo empty($timeago) ? 'none' : 'inline-block'; ?>">
                <span class="last-update-num-items-container">
                    <?php echo _x( 'Updated', 'Example: "Updated 2 days ago"', 'wprss' ); ?>
                    <span class="last-update-time-container">
                        <code class="last-update-time"><?php printf(__('%1$s ago', 'wprss'), $timeago) ?></code>
                    </span>
                    (<span class="last-update-num-items"><?php echo
                        $last_update_items ?></span>
                    <?php echo _x('items', 'Example: "15 new"', 'wprss'); ?>)
                </span>
            </p>

            <?php
            break;

        case 'feed-count':
            $items = wprss_get_feed_items_for_source( $post_id );
            $has_items_class = ($items->post_count > 0) ? 'has-imported-items' : '';

            $errors = get_post_meta( $post_id, 'wprss_error_last_import', true );
            $errorShowClass = ( $errors !== '' )? 'wprss-show' : '';
            $default_msg = __( "This feed source experienced an error during the last feed fetch or validation check. Re-check the feed source URL or check the Error Log in the Debugging page for more details.", WPRSS_TEXT_DOMAIN );
            $msg = strlen( $errors ) > 0 ? $errors : $default_msg;
            $errorIcon = sprintf(
                '<i title="%1$s" class="fa fa-warning fa-fw wprss-feed-error-symbol %2$s"></i>',
                esc_attr($msg),
                $errorShowClass
            );

            $view_items_url = admin_url( 'edit.php?post_type=wprss_feed_item&wprss_feed=' . $post_id );
            $view_items_url = apply_filters( 'wprss_view_feed_items_row_action_link', $view_items_url, $post_id );
            ?>
                <a href="<?php echo esc_attr($view_items_url); ?>"
                   class="items-imported-link <?php echo $has_items_class; ?>"
                   title="<?php echo esc_attr(__('View the imported items for this feed source', 'wprss')); ?>"
                >
                    <span class="items-imported"><?php echo $items->post_count ?></span>
                    <?php _e('items', 'wprss') ?>
                </a>
            <div class="spinner"></div>

                <?php echo $errorIcon; ?>

                <div class="row-actions">
                    <span class="fetch">
                        <a href="javascript:;"
                           class="wprss_fetch_items_ajax_action"
                           pid="<?php echo esc_attr ($post_id); ?>"
                           purl="<?php echo admin_url('admin-ajax.php'); ?>">
                            <?php _e('Fetch', 'wprss'); ?>
                        </a>
                    </span>
                    <span class="purge-posts trash <?php echo $has_items_class; ?>">
                        |
                        <a href="javascript:;"
                           class="wprss_delete_items_ajax_action"
                           pid="<?php echo esc_attr ($post_id); ?>"
                           purl="<?php echo admin_url('admin-ajax.php'); ?>">
                            <?php _e('Delete items', 'wprss'); ?>
                        </a>
                    </span>
                </div>
			<?php

		  	// Set meta field for items imported
		  	update_post_meta( $post_id, 'wprss_items_imported', $items->post_count );

            break;
      }
    }


	add_filter( "manage_edit-wprss_feed_sortable_columns", "wprss_feed_sortable_columns" );
    /**
     * Make the custom columns sortable for wprss_feed post type
     *
     * @since 2.0
     */
    function wprss_feed_sortable_columns() {
        $sortable_columns = array(
            // meta column id => sortby value used in query
            'state'			=> 'state',
            'title'			=> 'title',
			'updates'		=>	'updates',
			'feed-count'	=>	'feed-count'
        );
        return apply_filters( 'wprss_feed_sortable_columns', $sortable_columns );
    }


    add_action( 'pre_get_posts', 'wprss_feed_source_order' );
    /**
     * Change order of feed sources to alphabetical ascending according to feed name
     *
     * @since 2.2
     */
    function wprss_feed_source_order( $query ) {
		// Check if the query is being processed in WP Admin, is the main query, and is targetted
		// for the wprss_feed CPT. If not, stop
        if ( !is_admin() || !$query->is_main_query() || $query->get('post_type') !== 'wprss_feed' ) {
            return;
        }

		// Get the sorting query args
		$order = strtoupper($query->get('order'));
		$orderby = $query->get('orderby');

		// If order is not specified, default to ascending
		if ($order !== 'ASC' && $order !== 'DESC') {
			$order = 'ASC';
		}

		$query->set('order', $order);

		// If not explicitly sorting or sorting by title, sort by title
		if (!$orderby || $orderby === 'title') {
			$query->set('orderby', 'title');
		}

		// Check what we are sorting by
		switch ( $orderby ) {
			case 'state':
				$query->set('meta_key', 'wprss_state');
				$query->set('orderby', 'meta_value');
				break;

			case 'updates':
				$query->set('meta_key', 'wprss_next_update');
				$query->set('orderby', 'meta_value');

				break;
			case 'feed-count':
				$query->set('meta_key', 'wprss_items_imported');
				$query->set('orderby', 'meta_value_num');

				break;
		}
	}


    add_filter( 'manage_wprss_feed_item_posts_columns', 'wprss_set_feed_item_custom_columns', 20, 1 );
    /**
     * Set up the custom columns for the wprss_feed source list
     *
     * @since 2.0
     */
    function wprss_set_feed_item_custom_columns( $columns ) {

        $columns = array (
            'cb'          => '<input type="checkbox" />',
            'title'       => __( 'Name', WPRSS_TEXT_DOMAIN ),
            'permalink'   => __( 'Permalink', WPRSS_TEXT_DOMAIN ),
            'publishdate' => __( 'Date published', WPRSS_TEXT_DOMAIN ),
            'source'      => __( 'Source', WPRSS_TEXT_DOMAIN )
        );
        return apply_filters( 'wprss_set_feed_item_custom_columns', $columns );
    }


    add_action( "manage_wprss_feed_item_posts_custom_column", "wprss_show_feed_item_custom_columns", 10, 2 );
    /**
     * Show up the custom columns for the wprss_feed list
     *
     * @since 2.0
     */
    function wprss_show_feed_item_custom_columns( $column, $post_id ) {

        switch ( $column ) {
            case "permalink":
                $url = get_post_meta( $post_id, 'wprss_item_permalink', true);
                echo '<a href="' . $url . '">' . $url. '</a>';
                break;

            case "publishdate":
                $item_date = get_the_time( 'U', get_the_ID() );
                $item_date = ( $item_date === '' )? date('U') : $item_date;
                $publishdate = date( 'Y-m-d H:i:s', $item_date ) ;
                echo $publishdate;
                break;

            case "source":
                $query = new WP_Query();
                $source = '<a href="' . get_edit_post_link( get_post_meta( $post_id, 'wprss_feed_id', true ) ) . '">' . get_the_title( get_post_meta( $post_id, 'wprss_feed_id', true ) ) . '</a>';
                echo $source;
                break;
        }
    }


    add_filter( "manage_edit-wprss_feed_item_sortable_columns", "wprss_feed_item_sortable_columns" );
    /**
     * Make the custom columns sortable
     *
     * @since 2.0
     */
    function wprss_feed_item_sortable_columns() {
        $sortable_columns = array(
            // meta column id => sortby value used in query
            'publishdate' => 'publishdate',
            'source'      => 'source'
        );
        return apply_filters( 'wprss_feed_item_sortable_columns', $sortable_columns );
    }


    add_action( 'pre_get_posts', 'wprss_feed_item_orderby' );
    /**
     * Change ordering of posts on wprss_feed_item screen
     *
     * @since 2.0
     */
    function wprss_feed_item_orderby( $query ) {
        if( ! is_admin() )
            return;

        $post_type = $query->get('post_type');

        // If we're on the feed listing admin page
        if ( $post_type == 'wprss_feed_item') {
            // Set general orderby to date the feed item was published
            $query->set('orderby','publishdate');
            // If user clicks on the reorder link, implement reordering
            $orderby = $query->get( 'orderby');
            if( 'publishdate' == $orderby ) {
                $query->set( 'order', 'DESC' );
                $query->set( 'orderby', 'date' );
            }
        }
    }


    add_filter( 'post_updated_messages', 'wprss_feed_updated_messages' );
    /**
     * Change default notification message when new feed is added or updated
     *
     * @since 2.0
     */
    function wprss_feed_updated_messages( $messages ) {
        global $post, $post_ID;

        $messages[ 'wprss_feed' ] = array(
            0  => '', // Unused. Messages start at index 1.
            1  => __( 'Feed source updated. ', WPRSS_TEXT_DOMAIN ),
            2  => __( 'Custom field updated.', WPRSS_TEXT_DOMAIN ),
            3  => __( 'Custom field deleted.', WPRSS_TEXT_DOMAIN ),
            4  => __( 'Feed source updated.', WPRSS_TEXT_DOMAIN ),
            5  => '',
            6  => __( 'Feed source saved.', WPRSS_TEXT_DOMAIN ),
            7  => __( 'Feed source saved.', WPRSS_TEXT_DOMAIN ),
            8  => __( 'Feed source submitted.', WPRSS_TEXT_DOMAIN ),
            9  => '',
            10 => __( 'Feed source updated.', WPRSS_TEXT_DOMAIN )
        );

        return apply_filters( 'wprss_feed_updated_messages', $messages );
    }


    add_filter( 'post_row_actions', 'wprss_remove_row_actions', 10, 2 );
    /**
     * Remove actions row for imported feed items, we don't want them to be editable or viewable
     *
     * @since 2.0
     */
    function wprss_remove_row_actions( $actions, $post )
    {

        $page = isset( $_GET['paged'] )? '&paged=' . $_GET['paged'] : '';
        if ( get_post_type($post) === 'wprss_feed_item' )  {
            if (!wpra_is_dev_mode()) {
                unset($actions['edit']);
            }
            unset( $actions[ 'view' ] );
            //unset( $actions[ 'trash' ] );
            unset( $actions[ 'inline hide-if-no-js' ] );
        }
        elseif ( get_post_type($post) === 'wprss_feed' ) {
    		$actions = array_reverse( $actions );
    		$actions['id'] = '<span class="wprss-row-id">' . sprintf( __( 'ID: %1$s', WPRSS_TEXT_DOMAIN ), $post->ID ) . '</span>';
    		$actions = array_reverse( $actions );

            unset( $actions[ 'view'] );
            unset( $actions[ 'inline hide-if-no-js'] );
        }
        return apply_filters( 'wprss_remove_row_actions', $actions );
    }



    add_action( 'wprss_delete_feed_items_from_source_hook', 'wprss_delete_feed_items_of_feed_source', 10 , 1 );
    /**
     * Deletes the feed items of the feed source identified by the given ID.
     *
     * @since 3.5
     * @param int $source_id The ID of the feed source
     */
    function wprss_delete_feed_items_of_feed_source($source_id) {
        wprss_delete_feed_items($source_id);

        update_post_meta($source_id, 'wprss_feed_is_deleting_items', '');
    }


    /**
     * Shows a notification that tells the user that feed items for a particular source are being deleted
     *
     * @since 3.5
     */
    function wprss_notify_about_deleting_source_feed_items() {
        $message = __( apply_filters( 'wprss_notify_about_deleting_source_feed_items_message', 'The feed items for this feed source are being deleted in the background.' ), WPRSS_TEXT_DOMAIN );
        echo '<div class="updated"><p>' . $message . '</p></div>';
    }


    add_action( 'wp_ajax_wprss_fetch_items_row_action', 'wprss_fetch_feeds_action_hook' );
    /**
     * The AJAX function for the 'Fetch Feed Items' row action on the
     * 'All Feed Sources' page.
     *
     * @since 3.3
     */
    function wprss_fetch_feeds_action_hook() {
        $response = wprss()->createAjaxResponse();
        $wprss = wprss();
        $kFeedSourceId = 'feed_source_id';
        try {
            $kId = 'id';
            if (!isset( $_POST[$kId] ) || empty( $_POST[$kId] )) {
                throw new Exception($wprss->__('Could not schedule fetch: source ID must be specified'));
            }
            $id = $_POST['id'];
            $response->setAjaxData($kFeedSourceId, $id);

            if (!current_user_can('edit_feed_sources')) {
                throw new Exception($wprss->__(array('Could not schedule fetch for source #%1$s: user must have sufficient privileges', $id)));
            }

            // Verify admin referer
            if (!wprss_verify_nonce( 'wprss_feed_source_action', 'wprss_admin_ajax_nonce' )) {
                throw new Exception($wprss->__(array('Could not schedule fetch for source #%1$s: nonce is expired', $id)));
            }

            update_post_meta( $id, 'wprss_force_next_fetch', '1' );

            // Prepare the schedule args
            $schedule_args = array( strval( $id ) );

            // Get the current schedule - do nothing if not scheduled
            $next_scheduled = wp_next_scheduled( 'wprss_fetch_single_feed_hook', $schedule_args );
            if ( $next_scheduled !== FALSE ) {
                // If scheduled, unschedule it
                wp_unschedule_event( $next_scheduled, 'wprss_fetch_single_feed_hook', $schedule_args );

                // Get the interval option for the feed source
                $interval = get_post_meta( $id, 'wprss_update_interval', TRUE );
                // if the feed source uses its own interval
                if ( $interval !== '' && $interval !== wprss_get_default_feed_source_update_interval() ) {
                    // Add meta in feed source. This is used to notify the source that it needs to reschedule it
                    update_post_meta( $id, 'wprss_reschedule_event', $next_scheduled );
                }
            }

            // Schedule the event for 5 seconds from now
            $offset = floor(count(wpra_get_ready_cron_jobs()) / 2);
            $success = wp_schedule_single_event( time() + $offset, 'wprss_fetch_single_feed_hook', $schedule_args );
            if (!$success) {
                throw new Exception(__('Failed to schedule cron', 'wprss'));
            }
            wprss_flag_feed_as_updating( $id );
        } catch (Exception $e) {
            $response = wprss()->createAjaxErrorResponse($e);
            if (isset($id)) {
                $response->setAjaxData($kFeedSourceId, $id);
            }
            echo $response->getBody();
            exit();
        }

        $response->setAjaxData('message', $wprss->__(array('Fetch for feed source #%1$s successfully scheduled', $id)));
        $response->setAjaxData('success', $success);
        echo $response->getBody();
        exit();
    }
    
    add_action( 'wp_ajax_wprss_delete_items_row_action', 'wprss_delete_items_ajax_action_hook' );
    /**
     * The AJAX function for the 'Delete Items' row action on the 'All Feed Sources' page.
     *
     * @since 4.14
     */
    function wprss_delete_items_ajax_action_hook() {
        $kFeedSourceId = 'feed_source_id';
        $response = wprss()->createAjaxResponse();
        $wprss = wprss();
        try {
            $id = filter_input(INPUT_POST, 'id', FILTER_DEFAULT);
            if (empty($id)) {
                throw new Exception($wprss->__('Source ID was not specified'));
            }

            $response->setAjaxData($kFeedSourceId, $id);

            if (!current_user_can('edit_feed_sources')) {
                throw new Exception($wprss->__(array('User must have sufficient privileges', $id)));
            }

            // Verify admin referer
            if (!wprss_verify_nonce( 'wprss_feed_source_action', 'wprss_admin_ajax_nonce' )) {
                throw new Exception($wprss->__(array('Nonce has expired - Please refresh the page.', $id)));
            }

            // Schedule a job that runs this function with the source id parameter
            $offset = floor(count(wpra_get_ready_cron_jobs()) / 2);
            $success = wp_schedule_single_event( time() + $offset, 'wprss_delete_feed_items_from_source_hook', array( $id ) );
            if (!$success) {
                throw new Exception(__('Failed to schedule cron', 'wprss'));
            }
            // Mark feed as deleting its items
            update_post_meta( $id, 'wprss_feed_is_deleting_items', time() );
        } catch (Exception $e) {
            $response = wprss()->createAjaxErrorResponse($e);
            if (isset($id)) {
                $response->setAjaxData($kFeedSourceId, $id);
            }
            echo $response->getBody();
            exit();
        }

        $response->setAjaxData('message', $wprss->__(array('Items are being deleted', $id)));
        $response->setAjaxData('success', $success);
        echo $response->getBody();
        exit();
    }


    add_action( 'wp_ajax_wprss_toggle_feed_state', 'wprss_ajax_toggle_feed_state' );
    /**
     * The AJAX function for toggling a feed's state from the 'All Feed Sources' page.
     *
     * @since 4.14
     */
    function wprss_ajax_toggle_feed_state() {
        $kFeedSourceId = 'feed_source_id';
        $response = wprss()->createAjaxResponse();
        $wprss = wprss();
        try {
            $id = filter_input(INPUT_POST, 'id', FILTER_DEFAULT);
            if (empty($id)) {
                throw new Exception($wprss->__('Source ID was not specified'));
            }

            $response->setAjaxData($kFeedSourceId, $id);

            if (!current_user_can('edit_feed_sources')) {
                throw new Exception($wprss->__(array('User must have sufficient privileges', $id)));
            }

            // Verify admin referer
            if (!wprss_verify_nonce( 'wprss_feed_source_action', 'wprss_admin_ajax_nonce' )) {
                throw new Exception($wprss->__(array('Nonce has expired - Please refresh the page.', $id)));
            }

            $active = wprss_is_feed_source_active( $id );

            if ( $active ) {
                wprss_pause_feed_source( $id );
            } else {
                wprss_activate_feed_source( $id );
            }

            $response->setAjaxData('active', !$active);
        } catch (Exception $e) {
            $response = wprss()->createAjaxErrorResponse($e);
            if (isset($id)) {
                $response->setAjaxData($kFeedSourceId, $id);
            }
            echo $response->getBody();
            exit();
        }

        $response->setAjaxData('message', $wprss->__(array('Feed state changed successfully', $id)));
        echo $response->getBody();
        exit();
    }

    add_action('manage_posts_extra_tablenav', function($which) {
        $screen = get_current_screen();
        $postType = $screen->post_type;
        // Only add on feed source list
        if ($postType !== 'wprss_feed') {
            return;
        }

        $nonceEl = new \Aventura\Wprss\Core\Block\Html\Span(array(
            'data-value'    => wp_create_nonce('wprss_feed_source_action'),
            'id'            => 'wprss_feed_source_action_nonce',
            'class'         => 'hidden'
        ));
        echo (string) $nonceEl;
//        wp_nonce_field('wprss_feed_source_action', 'wprss_feed_source_action_nonce', false);
    });


    add_filter( 'bulk_actions-edit-wprss_feed_item', 'wprss_custom_feed_item_bulk_actions' );
    /**
     * Allow filtering bulk actions for feed items
     *
     * @since 2.0
     */
    function wprss_custom_feed_item_bulk_actions( $actions ) {
        if (!wpra_is_dev_mode()) {
            unset($actions['edit']);
        }

        return apply_filters( 'wprss_custom_feed_item_bulk_actions', $actions );
    }


    add_action( 'admin_footer-edit.php', 'wprss_remove_a_from_feed_title' );
    /**
     * Remove hyperlink from imported feed titles in list posts screen
     *
     * @since 2.0
     */
    function wprss_remove_a_from_feed_title() {
        if ( 'edit-wprss_feed_item' !== get_current_screen()->id )
        return;
        ?>

        <script type="text/javascript">
            jQuery('table.wp-list-table a.row-title').contents().unwrap();
        </script>
        <?php
    }


    add_action( 'wp_before_admin_bar_render', 'wprss_modify_admin_bar' );
    /**
     * Removes the old "View Source" menu item from the admin bar and adds a new
     * "View items" menu bar item, that opens a new tab, showing the items imported
     * from that feed source.
     *
     * Only shown on the wprss_feed edit page.
     *
     * @since 4.2
     */
    function wprss_modify_admin_bar() {
      global $wp_admin_bar;
      if ( !is_admin() ) return;
      $screen = get_current_screen();
      // Check if we are in the wprss_feed edit page
      if ( $screen->base == 'post' && $screen->post_type == 'wprss_feed' && !empty( $_GET['action'] ) && $_GET['action'] == 'edit' ) {
        // Remove the old 'View Source' menu item
        $wp_admin_bar->remove_node( 'view' );

        // Prepare the view items link and text
        $view_items_link = apply_filters(
          'wprss_view_feed_items_row_action_link',
          admin_url( 'edit.php?post_type=wprss_feed_item&wprss_feed=' . get_the_ID() ),
          get_the_ID()
        );
        $view_items_text = apply_filters( 'wprss_view_feed_items_row_action_text', 'View Items' );

        // Prepare the link target
        $link_target = 'wprss-view-items-' . get_the_ID();

        // Add the new menu item
        $wp_admin_bar->add_node( array(
          'href'    =>  $view_items_link,
          'id'      =>  'view',
          'title'   =>  __( $view_items_text, WPRSS_TEXT_DOMAIN ),
          'meta'    =>  array(
            'target'  =>  $link_target
          )
        ));
      }
    }




    if ( is_admin() ){
      add_filter('pre_get_posts', 'wprss_view_feed_items_query');
      /**
       * Alters the main query in the WordPress admin, when the wprss_feed GET parameter is set.
       * The queried items are then filtered down to the items imported by the feed source with
       * the ID given in the wprss_feed GET parameter.
       *
       * @since 4.2
       */
      function wprss_view_feed_items_query( $query ) {
        if ( is_admin() && $query->is_main_query() && !empty($_GET['wprss_feed']) ) {
          // Get the ID from the GET param
          $id = $_GET['wprss_feed'];
          // Get the existing meta query
          $mq = $query->get('meta_query');
          // If the meta query is not yet set
          if ( !is_array($mq) ) {
            // initialize it
            $mq = array(
              'relation'  =>  'AND',
            );
          }
          // Add the custom meta query
          $mq[] = apply_filters(
            'wprss_view_feed_items_meta_query',
            array(
              'key'   =>  'wprss_feed_id',
              'value'   =>  $id,
              'compare' =>  '='
            ),
            $id
          );
          // Set the new meta query
          $query->set('meta_query', $mq);
        }
        // Return the query
        return $query;
      }
    }
