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
            'cb'          => '<input type="checkbox" />',
            'title'       => __( 'Name', 'wprss' ),
            'url'         => __( 'URL', 'wprss' ),
        //  'description' => __( 'Description', 'wprss' )
        );

        $columns = apply_filters( 'wprss_set_feed_custom_columns', $columns );
        $columns['id'] = __( 'ID', 'wprss' );

        // Columns to add when feed is not trashed
        if ( !isset( $_GET['post_status'] ) || $_GET['post_status'] !== 'trash' ) {
            $columns['next-update'] = __( 'Next Update', 'wprss' );
            $columns['state'] = __( 'State', 'wprss' );
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

        case 'next-update':
            $interval = get_post_meta( $post_id, 'wprss_update_interval', TRUE );
            $timestamp = wprss_get_next_feed_source_update( $post_id );
            // If using the global interval, get the timestamp of the next glboal update
            if ( $interval === wprss_get_default_feed_source_update_interval() || $interval === '' ) {
              $timestamp = wp_next_scheduled( 'wprss_fetch_all_feeds_hook', array() );
            }
            ?>

            <p>
                <code>
                    <?php if ( ! wprss_is_feed_source_active( $post_id ) ): ?>
                        Paused
                    <?php elseif ( $timestamp === FALSE ) : ?>
                        None
                    <?php else: ?>
                        <?php echo human_time_diff( $timestamp, time() ); ?>
                    <?php endif; ?>
                </code>
            </p>

            <?php

            break;

        case 'feed-count':
            $items = wprss_get_feed_items_for_source( $post_id );
            echo '<p>' . $items->post_count . '</p>';
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


    add_filter( 'post_row_actions', 'wprss_remove_row_actions', 10, 1 );
    /**
     * Remove actions row for imported feed items, we don't want them to be editable or viewable
     * 
     * @since 2.0
     */       
    function wprss_remove_row_actions( $actions )
    {
        if ( get_post_type() === 'wprss_feed_item' )  {
            unset( $actions[ 'edit' ] );
            unset( $actions[ 'view' ] );
            //unset( $actions[ 'trash' ] );
            unset( $actions[ 'inline hide-if-no-js' ] );
        }
        elseif ( get_post_type() === 'wprss_feed' ) {
            unset( $actions[ 'view'] );
            if ( get_post_status( get_the_ID() ) !== 'trash' ) {
                $actions[ 'fetch' ] = '<a href="javascript:;" class="wprss_ajax_action" pid="'. get_the_ID() .'" purl="'.home_url().'/wp-admin/admin-ajax.php" title="'. esc_attr( __( 'Fetch Feeds', 'wprss' ) ) .'" >' . __( 'Fetch Feeds', 'wprss' ) . '</a>';

                $purge_feeds_row_action_text = apply_filters( 'wprss_purge_feeds_row_action_text ', 'Delete Feed Items' );
                $purge_feeds_row_action_title = apply_filters( 'wprss_purge_feeds_row_action_title ', 'Delete feed items imported by this feed source' );
                $actions['purge-posts'] = "<a href='".admin_url("edit.php?post_type=wprss_feed&purge-feed-items=" . get_the_ID() ) . "' title='" . __( $purge_feeds_row_action_title, 'wprss' ) . "' >" . __( $purge_feeds_row_action_text, 'wprss' ) . "</a>";
            }
        }
        return apply_filters( 'wprss_remove_row_actions', $actions );
    }


    add_action( 'init', 'check_delete_for_feed_source' );
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
            // Refresh the page without the GET parameter
            header( 'Location: ' . admin_url( 'edit.php?post_type=wprss_feed' ) );
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
        $message = __( apply_filters( 'wprss_notify_about_deleting_source_feed_items_message', 'The feeds for this feed source are being deleted in the background.' ), 'wprss' );
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
            wprss_fetch_insert_single_feed_items( $id );
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