<?php    

    /** 
     * Contains all custom post type related functions
     *         
     * @package WPRSSAggregator
     */

    /**
     * Create Custom Post Types wprss_feed and wprss_feed_item
     * 
     * @since 2.0
     */                 
    function wprss_register_post_types() {        
        
        // Set up the arguments for the 'wprss_feed' post type
        $feed_args = array(
            'public'        => true,
            'query_var'     => 'feed',
            'menu_position' => 100,
            'menu_icon'     => WPRSS_IMG . 'icon-adminmenu16-sprite.png',
            'show_in_menu'  => true,
            'supports'      => array( 'title' ),
            'rewrite'       => array(
                                'slug'       => 'feeds',
                                'with_front' => false
                                ),            
            'labels'        => array(
                                'name'                  => __( 'Feed Sources' ),
                                'singular_name'         => __( 'Feed' ),
                                'add_new'               => __( 'Add New Feed Source' ),
                                'all_items'             => __( 'All Feed Sources' ),
                                'add_new_item'          => __( 'Add New Feed Source' ),
                                'edit_item'             => __( 'Edit Feed Source' ),
                                'new_item'              => __( 'New Feed Source' ),
                                'view_item'             => __( 'View Feed Source' ),
                                'search_items'          => __( 'Search Feeds' ),
                                'not_found'             => __( 'No Feed Sources Found' ),
                                'not_found_in_trash'    => __( 'No Feed Sources Found In Trash' ),
                                'menu_name'             => __( 'RSS Aggregator' )
                                ),
        );
        
        // Register the 'wprss_feed' post type
        register_post_type( 'wprss_feed', $feed_args );

        // Set up the arguments for the 'wprss_feed_item' post type
        $feed_item_args = array(
            'public'         => true,
            'query_var'      => 'feed_item',
            'show_in_menu'   => 'edit.php?post_type=wprss_feed',
            'rewrite'        => array(
                                 'slug' => 'feeds/items',
                                 'with_front' => false,
                                ),       
            'labels'         => array(
                                 'name'                  => __( 'Imported Feeds' ),
                                 'singular_name'         => __( 'Imported Feed' ),
                                 'all_items'             => __( 'Imported Feeds' ),
                                 'view_item'             => __( 'View Imported Feed' ),                            
                                 'search_items'          => __( 'Search Imported Feeds' ),
                                 'not_found'             => __( 'No Imported Feeds Found' ),
                                 'not_found_in_trash'    => __( 'No Imported Feeds Found In Trash')
                                ),
          /*  'capability_type' => array('feed_item','feed_items'),
            'map_meta_cap'    => true,
            'capabilities' => array(
                            'publish_posts' => 'publish_feed_items',
                            'edit_posts' => 'edit_feed_items',
                            'edit_others_posts' => 'edit_others_feed_items',
                            'delete_posts' => 'delete_feed_items',
                            'delete_others_posts' => 'delete_others_feed_items',
                            'read_private_posts' => 'read_private_feed_items',
                            'edit_post' => 'edit_feed_item',
                            'delete_post' => 'delete_feed_item',
                            'read_post' => 'read_feed_item'
            )*/
        );
        
        // Register the 'feed_item' post type
        register_post_type( 'wprss_feed_item', $feed_item_args );        
    }

    add_action( 'init', 'wprss_register_post_types' );
    

    /**     
     * Set up the custom columns for the wprss_feed list
     * 
     * @since 2.0
     */      
    function wprss_set_custom_columns( $columns ) {

        $columns = array (
            'cb'          => '<input type="checkbox" />',
            'title'       => __( 'Name', 'wprss' ),
            'url'         => __( 'URL', 'wprss' ),
            'description' => __( 'Description', 'wprss' ),
            //'category' => __( 'Category' ),
        );
        return $columns;
    }
    
    // Set the feed source list page columns 
    add_filter( 'manage_edit-wprss_feed_columns', 'wprss_set_custom_columns'); 


    /**
     * Show up the custom columns for the wprss_feed list
     * 
     * @since 2.0
     */  
    function wprss_show_custom_columns( $column, $post_id ) {
     
      switch ( $column ) {
        case "url":
          $url = get_post_meta( $post_id, 'wprss_url', true);
          echo '<a href="' . $url . '">' . $url. '</a>';
          break;
        case "description":
          $description = get_post_meta( $post_id, 'wprss_description', true);
          echo $description;
          break;      
      }
    }
    
    // Show the feed source list page custom columns 
    add_action( "manage_wprss_feed_posts_custom_column", "wprss_show_custom_columns", 10, 2 );


    /**
     * Make the custom columns sortable
     * 
     * @since 2.0
     */  
    function wprss_sortable_columns() {
        return array(
            // meta column id => sortby value used in query
            'title' => 'title',             
        );
    }


    /**
     * Set up the custom columns for the wprss_feed list
     * 
     * @since 2.0
     */      
    function wprss_set_feed_item_custom_columns( $columns ) {

        $columns = array (
            'cb'          => '<input type="checkbox" />',
            'title'       => __( 'Name', 'wprss' ),
            'permalink'   => __( 'Permalink', 'wprss' ),
            'description' => __( 'Excerpt', 'wprss' ),
            'publishdate' => __( 'Date published', 'wprss' ),
            'source'      => __( 'Source', 'wprss' )
            //'category' => __( 'Category' ),
        );
        return $columns;
    }
    
    // Set the feed source list page columns 
    add_filter( 'manage_edit-wprss_feed_item_columns', 'wprss_set_feed_item_custom_columns'); 


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
            
            case "description":
                $description = get_post_meta( $post_id, 'wprss_item_description', true);
                echo $description;
                break;     
            
            case "publishdate":
                $publishdate = date( 'Y-m-d H:i:s', get_post_meta( get_the_ID(), 'wprss_item_date', true ) ) ;          
                echo $publishdate;
                break;   
            
            case "source":        
                $query = new WP_Query();                 
                $source = get_the_title( get_post_meta( $post_id, 'wprss_feed_id', true ) );                
                echo $source;
                break;   
        }
    }
    
    // Show the feed source list page custom columns 
    add_action( "manage_wprss_feed_item_posts_custom_column", "wprss_show_feed_item_custom_columns", 10, 2 );


    /**     
     * Make the custom columns sortable
     * 
     * @since 2.0
     */  
    function wprss_feed_item_sortable_columns() {
        return array(
            // meta column id => sortby value used in query
            'publishdate' => 'publishdate',
            'source' => 'source'
        );
    }

    add_filter( "manage_edit-wprss_feed_item_sortable_columns", "wprss_feed_item_sortable_columns" );


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

    add_action( 'pre_get_posts', 'wprss_feed_item_orderby' );


    /**
     * Set up the input boxes for the wprss_feed post type
     * 
     * @since 2.0
     */   
    function wprss_add_meta_boxes() {
        global $wprss_meta_fields;

        // Remove the default WordPress Publish box, because we will be using custom ones
        remove_meta_box( 'submitdiv', 'wprss_feed', 'side' );
        add_meta_box(
            'submitdiv',
            __('Save Feed Source'),
            'post_submit_meta_box',
            'wprss_feed',
            'side',
            'low');

      /*  add_meta_box(
            'wprss-save-link-side-meta',
            'Save Feed Source',
            'wprss_save_feed_source_meta_box',
            'wprss_feed',
            'side',
            'high'
        );
        
        add_meta_box(
            'wprss-save-link-bottom-meta',
            __( 'Save Feed Source', 'wprss' ),
            'wprss_save_feed_source_meta_box',
            'wprss_feed',
            'normal',
            'low'
        );*/

        add_meta_box(
            'wprss-help-meta',
            'WP RSS Aggregator Pro',
            'wprss_help_meta_box',
            'wprss_feed',
            'side',
            'low'
        );       

        add_meta_box(
            'custom_meta_box', // $id
            __( 'Feed Source Details', 'wprss' ), // $title 
            'wprss_show_meta_box', // $callback
            'wprss_feed', // $page
            'normal', // $context
            'high'); // $priority
  

        add_meta_box(
            'preview_meta_box', // $id
            __( 'Feed Preview', 'wprss' ), // $title 
            'wprss_preview_meta_box', // $callback
            'wprss_feed', // $page
            'normal', // $context
            'low'); // $priority
    }    


    /**     
     * Set up the meta box for the wprss_feed post type
     * 
     * @since 2.0
     */       
    function wprss_custom_fields() {
        $prefix = 'wprss_';
        
        // Field Array
        $wprss_meta_fields['url'] = array(
            'label' => __( 'URL', 'wprss' ),
            'desc'  => __( 'Enter feed URL (including http://)', 'wprss' ),
            'id'    => $prefix.'url',
            'type'  => 'text'
        );
        
        $wprss_meta_fields['description'] = array(
            'label' => __( 'Description', 'wprss' ),
            'desc'  => __( 'A short description about this feed source', 'wprss' ),
            'id'    => $prefix.'description',
            'type'  => 'textarea'
        );    
        
        // for extensibility, allows more meta fields to be added
        return apply_filters( 'wprss_fields', $wprss_meta_fields );
    }


    /**     
     * Set up the meta box for the wprss_feed post type
     * 
     * @since 2.0
     */ 
    function wprss_show_meta_box() {
        global $post;
        $meta_fields = wprss_custom_fields();

        // Use nonce for verification
        echo '<input type="hidden" name="wprss_meta_box_nonce" value="' . wp_create_nonce( basename( __FILE__ ) ) . '" />';
            
            // Begin the field table and loop
            echo '<table class="form-table">';
            foreach ( $meta_fields as $field ) {
                // get value of this field if it exists for this post
                $meta = get_post_meta( $post->ID, $field['id'], true );
                // begin a table row with
                echo '<tr>
                        <th><label for="' . $field['id'] . '">' . $field['label'] . '</label></th>
                        <td>';
                        
                        switch( $field['type'] ) {
                        
                            // text
                            case 'text':
                                echo '<input type="text" name="'.$field['id'].'" id="'.$field['id'].'" value="'.$meta.'" size="55" />
                                    <br /><span class="description">'.$field['desc'].'</span>';
                            break;
                        
                            // textarea
                            case 'textarea':
                                echo '<textarea name="'.$field['id'].'" id="'.$field['id'].'" cols="60" rows="4">'.$meta.'</textarea>
                                    <br /><span class="description">'.$field['desc'].'</span>';
                            break;
                        
                            // checkbox
                            case 'checkbox':
                                echo '<input type="checkbox" name="'.$field['id'].'" id="'.$field['id'].'" ',$meta ? ' checked="checked"' : '','/>
                                    <label for="'.$field['id'].'">'.$field['desc'].'</label>';
                            break;    
                        
                            // select
                            case 'select':
                                echo '<select name="'.$field['id'].'" id="'.$field['id'].'">';
                                foreach ($field['options'] as $option) {
                                    echo '<option', $meta == $option['value'] ? ' selected="selected"' : '', ' value="'.$option['value'].'">'.$option['label'].'</option>';
                                }
                                echo '</select><br /><span class="description">'.$field['desc'].'</span>';
                            break;                                            
                        
                        } //end switch
                echo '</td></tr>';
            } // end foreach
            echo '</table>'; // end table
    }
  

    /**     
     * Save the custom fields
     * 
     * @since 2.0
     */ 
    function wprss_save_custom_fields( $post_id ) {
        $meta_fields = wprss_custom_fields();
        
        // verify nonce
        if ( !wp_verify_nonce( $_POST[ 'wprss_meta_box_nonce' ], basename( __FILE__ ) ) ) 
            return $post_id;
        
        // check autosave
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE)
            return $post_id;
        
        // check permissions
        if ('page' == $_POST['post_type']) {
            if (!current_user_can('edit_page', $post_id))
                return $post_id;
            } elseif (!current_user_can('edit_post', $post_id)) {
                return $post_id;
        }
        
        // loop through fields and save the data
        foreach ($meta_fields as $field) {
            $old = get_post_meta($post_id, $field['id'], true);
            $new = $_POST[$field['id']];
            if ($new && $new != $old) {
                update_post_meta($post_id, $field['id'], $new);
            } elseif ('' == $new && $old) {
                delete_post_meta($post_id, $field['id'], $old);
            }
        } // end foreach
    }

    add_action( 'save_post', 'wprss_save_custom_fields' );  

      
    /**     
     * Generate the Save Feed Source meta box
     * 
     * @since 2.0
     */  
    function wprss_save_feed_source_meta_box() {
        global $post;
        
        // insert nonce??

        echo '<input type="submit" name="publish" id="publish" class="button-primary" value="Save" tabindex="5" accesskey="s">';
                
        /**
         * Check if user has disabled trash, in that case he can only delete feed sources permanently,
         * else he can deactivate them. By default, if not modified in wp_config.php, EMPTY_TRASH_DAYS is set to 30.
         */
        if ( current_user_can( "delete_post", $post->ID ) ) {
            if (!EMPTY_TRASH_DAYS)
                $delete_text = __('Delete Permanently');
            else
                $delete_text = __('Move to Trash');
                
        echo '&nbsp;&nbsp;<a class="submitdelete deletion" href="' . get_delete_post_link( $post->ID ) . '">' . $delete_text . '</a>';
        }
    }

    /**     
     * Generate a preview of the latest 5 posts from the feed source being added/edited
     * 
     * @since 2.0
     */  
    function wprss_preview_meta_box() {
        global $post;
        $feed_url = get_post_meta( $post->ID, 'wprss_url', true );
        
        if( !empty( $feed_url ) ) {             
            $feed = fetch_feed( $feed_url ); 
            if (!is_wp_error( $feed ) ) {
                $items = $feed->get_items();        

                echo '<h4>Latest 5 feeds available from ' . get_the_title() . '</h4>'; 
                $count = 0;
                $feedlimit = 5;
                foreach ( $items as $item ) { 
                    echo '<ul>';
                    echo '<li>' . $item->get_title() . '</li>';
                    echo '</ul>';
                    if( ++$count == $feedlimit ) break; //break if count is met
                } 
            }
            else echo '<strong>Invalid feed URL</strong> - Double check the feed source URL setting above.';
        }

        else echo 'No feed URL defined yet';
    }


    /**     
     * Generate Help meta box
     * 
     * @since 2.0
     */      
    function wprss_help_meta_box() {
     echo '<p><strong>';
     _e( 'Need help?');
     echo '</strong> ';
     _e( 'Here are a few options:'); 

     /*'</p>
                    <ol>
                        <li><a class="<?php echo CPT_ONOMIES_UNDERSCORE; ?>_show_help_tab" href="#"><?php _e( 'The \'Help\' tab', CPT_ONOMIES_TEXTDOMAIN ); ?></a></li>
                        <li><a href="http://wordpress.org/support/plugin/cpt-onomies" title="<?php printf( esc_attr__( '%s support forums', CPT_ONOMIES_TEXTDOMAIN ), 'CPT-onomies' ); ?>" target="_blank"><?php printf( __( 'The %s support forums', CPT_ONOMIES_TEXTDOMAIN ), 'CPT-onomies\'' ); ?></a></li>
                        <li><a href="http://rachelcarden.com/cpt-onomies/" title="<?php esc_attr_e( 'Visit my web site', CPT_ONOMIES_TEXTDOMAIN ); ?>" target="_blank"><?php _e( 'My web site', CPT_ONOMIES_TEXTDOMAIN ); ?></a></li>
                    </ol>
                    <p><?php printf( __( 'If you notice any bugs or problems with the plugin, %1$splease let me know%2$s.', CPT_ONOMIES_TEXTDOMAIN ), '<a href="http://rachelcarden.com/contact/" target="_blank">', '</a>' ); ?></p>

                    <p><strong><a href="<?PHP echo CPT_ONOMIES_PLUGIN_DIRECTORY_URL; ?>" title="<?php esc_attr_e( CPT_ONOMIES_PLUGIN_NAME, CPT_ONOMIES_TEXTDOMAIN ); ?>" target="_blank"><?php _e( CPT_ONOMIES_PLUGIN_NAME, CPT_ONOMIES_TEXTDOMAIN ); ?></a></strong></p>
                    <p><strong><?php _e( 'Version', CPT_ONOMIES_TEXTDOMAIN ); ?>:</strong> <?php echo CPT_ONOMIES_VERSION; ?><br />
                    <strong><?php _e( 'Author', CPT_ONOMIES_TEXTDOMAIN ); ?>:</strong> <a href="http://www.rachelcarden.com" title="Rachel Carden" target="_blank">Rachel Carden</a></p>
   */ }


    /**
     * Change default notification message when new feed is added or updated
     * 
     * @since 2.0
     */   
    function wprss_feed_updated_messages( $messages ) {
        global $post, $post_ID;

        $messages['wprss_feed'] = array(
        0 => '', // Unused. Messages start at index 1.
        1 => __('Feed source updated. '),
        2 => __('Custom field updated.'),
        3 => __('Custom field deleted.'),
        4 => __('Feed source updated.'),        
        5 => '',
        6 => __('Feed source saved.'),
        7 => __('Feed source saved.'),
        8 => __('Feed source submitted.'),
        9 => '',
        10 =>__('Feed source updated.')
        );

        return $messages;
    }
    


  

?>