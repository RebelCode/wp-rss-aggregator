<?php 
    /**
     * Functions for the admin section, columns and row actions 
     * 
     * @package WP RSS Aggregator
     */ 


    add_filter( 'manage_wprss_feed_posts_columns', 'wprss_set_feed_custom_columns'); 
    /**     
     * Set up the custom columns for the wprss_feed list
     * 
     * @since 2.0
     */      
    function wprss_set_feed_custom_columns( $columns ) {

        $columns = array(
            'cb'          =>  '<input type="checkbox" />',
            'errors'      =>  '',
            'title'       =>  __( 'Name', 'wprss' ),
            'id'          =>  __( 'ID', 'wprss' ),
            // 'url'         => __( 'URL', 'wprss' ),
            // 'description' => __( 'Description', 'wprss' )
        );

        $columns = apply_filters( 'wprss_set_feed_custom_columns', $columns );

        // Columns to add when feed is not trashed
        if ( !isset( $_GET['post_status'] ) || $_GET['post_status'] !== 'trash' ) {
            $columns['state'] = __( 'State', 'wprss' );
            $columns['updates'] = __( 'Updates', 'wprss' );
            $columns['feed-count'] = __( apply_filters( 'wprss_feed_items_count_column', 'Imported items' ), 'wprss' );
        }

        return $columns;
    }    


    add_action( "manage_wprss_feed_posts_custom_column", "wprss_show_custom_columns", 10, 2 );
    /**
     * Show up the custom columns for the wprss_feed list
     * 
     * @since 2.0
     */  
    function wprss_show_custom_columns( $column, $post_id ) {
     
      switch ( $column ) {    
        case 'errors':
          $errors = get_post_meta( $post_id, 'wprss_error_last_import', true );
          $showClass = ( $errors === 'true' )? 'wprss-show' : '';

          $msg = "This feed source experienced an error during the last feed fetch or validation check. Re-check the feed source URL or check the Error Log in the Debugging page for more details.";
          echo "<i title=\"$msg\" class=\"fa fa-warning fa-fw wprss-feed-error-symbol $showClass\"></i>";
          break;
        case 'url':
          $url = get_post_meta( $post_id, 'wprss_url', true);
          echo '<a href="' . esc_url($url) . '">' . esc_url($url) . '</a>';
          break;
        case 'description':
          $description = get_post_meta( $post_id, 'wprss_description', true);
          echo esc_html( $description );
          break;      
        case 'id':
          echo esc_html( $post_id );
          break;
        case 'state':
            $active = wprss_is_feed_source_active( $post_id );
            $text       = ( $active )? 'Active' : 'Paused';
            $button     = ( $active )? 'Pause this feed source' : 'Activate this feed source';
            $icon       = ( $active )? 'pause' : 'play';
            $value      = ( $active )? 'paused' : 'active';
            $indicator  = ( $active )? 'green' : 'grey';

            ?>
            <p>
                <span class="wprss-indicator-<?php echo $indicator; ?>" title="<?php echo $text; ?>">
                    <i class="fa fa-circle"></i>
                </span>
                <input type="hidden" name="wprss-redirect" value="1" />
                <button type="submit" class='button-secondary' title="<?php echo $button; ?>" name="wprss-feed-id" value="<?php echo $post_id; ?>">
                    <i class='fa fa-<?php echo $icon; ?>'></i>
                </button>
            </p>
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
            ?>

            <p>
                Next update:
                <code class="next-update">
                    <?php if ( ! wprss_is_feed_source_active( $post_id ) ): ?>
                        Paused
                    <?php elseif ( $next_update === FALSE ) : ?>
                        None
                    <?php else: ?>
                        <?php echo human_time_diff( $next_update, time() ); ?>
                    <?php endif; ?>
                </code>
            </p>

            <?php if ( $last_update !== '' ): ?>
              <p class="last-update-container">
                Last updated:
                <code class="last-update"><?php echo human_time_diff( $last_update, time() ); ?> <?php _e('ago'); ?></code>
                <?php if ( $last_update_items !== '' ): ?>
                    <span class="last-update-imported-container"><br/>Last update imported: <code class="last-update-imported"><?php echo $last_update_items; ?></code> items</span>
                <?php endif; ?>
              </p>
            <?php endif;

            break;

        case 'feed-count':
            $items = wprss_get_feed_items_for_source( $post_id );
            $seconds_for_next_update = wprss_get_next_feed_source_update( $post_id ) - time();
            $showClass = ( ( $seconds_for_next_update < 10 && $seconds_for_next_update > 0 ) || wprss_is_feed_source_deleting( $post_id ) )? 'wprss-show' : '';

            echo '<p>';
            echo "<span class=\"items-imported\">{$items->post_count}</span>";
            echo "<i class=\"fa fa-fw fa-refresh fa-spin wprss-updating-feed-icon $showClass\" title=\"Updating feed source\"></i>";
            echo '</p>';

            break;
      }
    }


    /**
     * Make the custom columns sortable for wprss_feed post type
     * 
     * @since 2.0
     */  
    function wprss_feed_sortable_columns() {
        $sortable_columns = array(
            // meta column id => sortby value used in query
            'title' => 'title',             
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
        if ( ! is_admin() ) {
            return;
        }

        $post_type = $query->get('post_type');

        if ( $post_type == 'wprss_feed' ) {
            /* Post Column: e.g. title */
            if ( $query->get( 'orderby' ) == '' ) {
                $query->set( 'orderby', 'title' );
            }
            /* Post Order: ASC / DESC */
            if( $query->get( 'order' ) == '' ){
                $query->set( 'order', 'ASC' );
            }
        }
    }


    add_filter( 'manage_wprss_feed_item_posts_columns', 'wprss_set_feed_item_custom_columns'); 
    /**
     * Set up the custom columns for the wprss_feed source list
     * 
     * @since 2.0
     */      
    function wprss_set_feed_item_custom_columns( $columns ) {

        $columns = array (
            'cb'          => '<input type="checkbox" />',
            'title'       => __( 'Name', 'wprss' ),
            'permalink'   => __( 'Permalink', 'wprss' ),
            'publishdate' => __( 'Date published', 'wprss' ),
            'source'      => __( 'Source', 'wprss' )
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
                $item_date = get_post_meta( get_the_ID(), 'wprss_item_date', true );
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
                $query->set( 'meta_key', 'wprss_item_date' );
                $query->set( 'orderby', 'meta_value_num' );
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
            1  => __( 'Feed source updated. ', 'wprss' ),
            2  => __( 'Custom field updated.', 'wprss' ),
            3  => __( 'Custom field deleted.', 'wprss' ),
            4  => __( 'Feed source updated.', 'wprss' ),        
            5  => '',
            6  => __( 'Feed source saved.', 'wprss' ),
            7  => __( 'Feed source saved.', 'wprss' ),
            8  => __( 'Feed source submitted.', 'wprss' ),
            9  => '',
            10 => __( 'Feed source updated.', 'wprss' )
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
            unset( $actions[ 'edit' ] );
            unset( $actions[ 'view' ] );
            //unset( $actions[ 'trash' ] );
            unset( $actions[ 'inline hide-if-no-js' ] );
        }
        elseif ( get_post_type($post) === 'wprss_feed' ) {
            unset( $actions[ 'view'] );
            unset( $actions[ 'inline hide-if-no-js'] );
            if ( get_post_status( $post->ID ) !== 'trash' ) {
                $trash = $actions['trash'];
                unset( $actions['trash'] );

                $view_items_link = apply_filters(
                  'wprss_view_feed_items_row_action_link',
                  admin_url( 'edit.php?post_type=wprss_feed_item&wprss_feed=' . $post->ID ),
                  $post->ID
                );
                $view_items_text = apply_filters( 'wprss_view_feed_items_row_action_text', 'View items' );
                $actions['view-items'] = '<a href="' . $view_items_link . '">' . __( $view_items_text, 'wprss' ) . '</a>';

                $fetch_items_row_action_text = apply_filters( 'wprss_fetch_items_row_action_text', 'Fetch items' );
                $actions[ 'fetch' ] = '<a href="javascript:;" class="wprss_ajax_action" pid="'. $post->ID .'" purl="'.home_url().'/wp-admin/admin-ajax.php">' . __( $fetch_items_row_action_text, 'wprss' ) . '</a>';

                $purge_feeds_row_action_text = apply_filters( 'wprss_purge_feeds_row_action_text', 'Delete items' );
                $purge_feeds_row_action_title = apply_filters( 'wprss_purge_feeds_row_action_title', 'Delete feed items imported by this feed source' );
                $actions['purge-posts'] = "<a href='".admin_url("edit.php?post_type=wprss_feed&purge-feed-items=" . $post->ID . $page ) . "' title='" . __( $purge_feeds_row_action_title, 'wprss' ) . "' >" . __( $purge_feeds_row_action_text, 'wprss' ) . "</a>";
                
                $actions['trash'] = $trash;
            }
        }
        return apply_filters( 'wprss_remove_row_actions', $actions );
    }


    add_action( 'admin_init', 'check_delete_for_feed_source' );
    /**
     * Checks the GET data for the delete per feed source action request
     * 
     * @since 3.5
     */
    function check_delete_for_feed_source( $source_id = NULL ) {
        // then we need to check the GET data for the request
        if ( isset( $_GET['purge-feed-items'] ) ) {
            $source_id = $_GET['purge-feed-items'];
            // Schedule a job that runs this function with the source id parameter
            wp_schedule_single_event( time(), 'wprss_delete_feed_items_from_source_hook', array( $source_id ) );
            // Set a transient
            set_transient( 'wprss_delete_posts_by_source_notif', 'true', 30 );
            // Mark feed as deleting its items
            update_post_meta( $source_id, 'wprss_feed_is_deleting_items', time() );
            // check pagination
            $page = isset( $_GET['paged'] )? '&paged=' . $_GET['paged'] : '';
            // Refresh the page without the GET parameter
            header( 'Location: ' . admin_url( 'edit.php?post_type=wprss_feed' . $page ) );
            exit();
        } else {
            // Get the notification transient
            $transient = get_transient( 'wprss_delete_posts_by_source_notif' );
            // If the transient is set and is set to 'true'
            if ( $transient !== FALSE && $transient === 'true' ) {
                // delete it
                delete_transient( 'wprss_delete_posts_by_source_notif' );
                // Add an action to show the notification
                add_action( 'all_admin_notices', 'wprss_notify_about_deleting_source_feed_items' );
            }
        }
    }



    add_action( 'wprss_delete_feed_items_from_source_hook', 'wprss_delete_feed_items_of_feed_source', 10 , 1 );
    /**
     * Deletes the feed items of the feed source identified by the given ID.
     * 
     * @param $source_id The ID of the feed source
     * @since 3.5
     */
    function wprss_delete_feed_items_of_feed_source( $source_id ) {
        $force_delete = apply_filters( 'wprss_force_delete_when_by_source', TRUE );
        // WPML fix: removes the current language from the query WHERE and JOIN clauses
        global $sitepress;
        if ( $sitepress !== NULL ) {
            remove_filter( 'posts_join', array( $sitepress,'posts_join_filter') );
            remove_filter( 'posts_where', array( $sitepress,'posts_where_filter') );
        }
        // Run the query
        $query = new WP_Query(
            array(
                    'meta_key'       => 'wprss_feed_id',
                    'meta_value'     => $source_id,
                    'post_type'      => 'wprss_feed_item',
                    'post_status'    => 'any',
                    'posts_per_page' => -1
                )
        );
        $query = apply_filters( 'wprss_delete_per_source_query', $query, $source_id );
        // Delete the results of the query
        while( $query->have_posts() ) {
            $query->the_post();
            wp_delete_post( get_the_ID(), $force_delete );
        }
    }


    /**
     * Shows a notification that tells the user that feed items for a particular source are being deleted
     * 
     * @since 3.5
     */
    function wprss_notify_about_deleting_source_feed_items() {
        $message = __( apply_filters( 'wprss_notify_about_deleting_source_feed_items_message', 'The feed items for this feed source are being deleted in the background.' ), 'wprss' );
        echo '<div class="updated"><p>' . $message . '</p></div>';
    }


    add_action( 'wp_ajax_wprss_fetch_feeds_row_action', 'wprss_fetch_feeds_action_hook' );
    /**
     * The AJAX function for the 'Fetch Feed Items' row action on the
     * 'All Feed Sources' page.
     *
     * @since 3.3
     */
    function wprss_fetch_feeds_action_hook() {
        if ( isset( $_POST['id'] ) && !empty( $_POST['id'] ) ) {
            $id = $_POST['id'];
            update_post_meta( $id, 'wprss_force_next_fetch', '1' );
            // Prepare the schedule
            $schedule_args = array( strval( $id ) );
            wp_schedule_single_event( time(), 'wprss_fetch_single_feed_hook', $schedule_args );
            die();
        }
    }


    add_filter( 'bulk_actions-edit-wprss_feed_item', 'wprss_custom_feed_item_bulk_actions' );
    /**
     * Remove bulk action link to edit imported feed items
     * 
     * @since 2.0
     */       
    function wprss_custom_feed_item_bulk_actions( $actions ){
        unset( $actions[ 'edit' ] );
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


    add_filter( 'gettext', 'wprss_change_publish_button_text', 10, 2 );
    /**
     * Modify 'Publish' button text when adding a new feed source
     * 
     * @since 2.0
     */     
    function wprss_change_publish_button_text( $translation, $text ) {
        if ( 'wprss_feed' == get_post_type()) {
            if ( $text == 'Publish' )
                return __( 'Publish Feed', 'wprss' );
        }
        return apply_filters( 'wprss_change_publish_button_text', $translation );
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
        $view_items_text = apply_filters( 'wprss_view_feed_items_row_action_text', 'View items' );

        // Prepare the link target
        $link_target = 'wprss-view-items-' . get_the_ID();

        // Add the new menu item
        $wp_admin_bar->add_node( array(
          'href'    =>  $view_items_link,
          'id'      =>  'view',
          'title'   =>  $view_items_text,
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
