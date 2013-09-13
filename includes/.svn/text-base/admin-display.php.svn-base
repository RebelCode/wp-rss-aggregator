<?php 

    add_filter( 'manage_edit-wprss_feed_columns', 'wprss_set_feed_custom_columns'); 
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
            'description' => __( 'Description', 'wprss' )
        );
        $columns = apply_filters( 'wprss_set_feed_custom_columns', $columns );
        $columns['id'] = __( 'ID', 'wprss' );
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


    add_filter( 'manage_edit-wprss_feed_item_columns', 'wprss_set_feed_item_custom_columns'); 
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
                $publishdate = date( 'Y-m-d H:i:s', get_post_meta( get_the_ID(), 'wprss_item_date', true ) ) ;          
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
                $actions[ 'fetch' ] = '<a href="javascript:;" class="wprss_ajax_action" pid="'. get_the_ID() .'" purl="'.home_url().'/wp-admin/admin-ajax.php" title="'. esc_attr( __( 'Fetch Latest Feed Items', 'wprss' ) ) .'" >' . __( 'Fetch Latest Feed Items', 'wprss' ) . '</a>';
            }
        }
        return apply_filters( 'wprss_remove_row_actions', $actions );
    }


    add_action( 'wp_ajax_wprss_fetch_feeds_action', 'wprss_fetch_feeds_action_hook' );
    /**
     * The AJAX function for the 'Fetch Feed Items' row action on the
     * 'All Feed Sources' page.
     *
     * @since 3.3
     */
    function wprss_fetch_feeds_action_hook() {
        if ( isset( $_POST['id'] ) && !empty( $_POST['id'] ) ) {
            wprss_fetch_insert_single_feed_items( $_POST['id'] );
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