<?php    

    /**
     * wprss_register_post_type()
     * Create Custom Post Types wprss_feed and wprss_feed_item
     * @since 1.2
     */                 
    function wprss_register_post_types() {        
        
        // Set up the arguments for the 'wprss_feed' post type
        $feed_args = array(
            'public'        => true,
            'query_var'     => 'feed',
            'menu_position' => 100,
            'menu_icon'     => WPRSS_IMG . 'icon-adminmenu16-sprite.png',
            'show_in_menu'  => true,
            'supports'      => array('title'),
            'rewrite'       => array(
                                'slug'       => 'feeds',
                                'with_front' => false
                                ),            
            'labels'        => array(
                                'name'                  => __( 'Feed Sources' ),
                                'singular_name'         => __( 'Feed' ),
                                'add_new'               => __( 'Add New Feed source' ),
                                'all_items'             => __( 'All Feed sources' ),
                                'add_new_item'          => __( 'Add New Feed' ),
                                'edit_item'             => __( 'Edit Feed' ),
                                'new_item'              => __( 'New Feed' ),
                                'view_item'             => __( 'View Feed' ),
                                'search_items'          => __( 'Search Feeds' ),
                                'not_found'             => __( 'No Feeds Found' ),
                                'not_found_in_trash'    => __( 'No Feeds Found In Trash' ),
                                'menu_name'             => __( 'RSS Aggregator' )
                                ),
        );
        
        // Register the 'wprss_feed' post type
        register_post_type( 'wprss_feed', $feed_args );


        // Set up the arguments for the 'wprss_feed_item' post type
        $feed_item_args = array(
            'public'        => true,
            'query_var'     => 'feed_item',
            'show_in_menu'  => 'edit.php?post_type=wprss_feed',
            'rewrite'       => array(
                                'slug' => 'feeds/items',
                                'with_front' => false,
                                ),       
            'labels'        => array(
                                'all_items'             => __( 'Imported Feeds' )
                                ),
        );
        
        // Register the 'feed_item' post type
        register_post_type( 'wprss_feed_item', $feed_item_args );        
    }
    


    //    add_filter('manage_wprss_feed_posts_columns', 'wprss_add_category_column');
      //  add_action('manage_pages_custom_column', 'wprss_show_category_column');
           
    /*}

   /* Set the list page columns */
   /* add_filter( 'manage_edit-wprss_feed_columns', 'my_edit_columns');
    
    function my_edit_columns( $columns ) {

        $columns = array (
            'cb' => '<input type="checkbox" />',
            'name' => __( 'Name' ),
            'description' => __( 'Description' ),
            'genre' => __( 'Genre' ),
        );
        return $columns;
    }
 */

    /**
     * wprss_add_meta_boxes
     * Set up the input boxes for the wprss_feed post type
     * @since 1.2
     */   
    function wprss_add_meta_boxes() {
        add_meta_box(
            'wprss-feed-name-meta',
            'Feed Name',
            'wprss_feed_name_meta',
            'wprss_feed',
            'normal',
            'high'
        );
        
        add_meta_box(
            'wprss-feed-description-meta',
            'Feed Description',
            'wprss_feed_description_meta',
            'wprss_feed',
            'normal',
            'default'
        );

        add_meta_box(
            'wprss-feed-url-meta',
            'Feed URL',
            'wprss_feed_url_meta',
            'wprss_feed',
            'normal',
            'high'
        );
        
        // Remove the default WordPress Publish box, because we will be using custom ones
       remove_meta_box( 'submitdiv', 'wprss_feed', 'side' );
        
        /*add_meta_box(
            'wprss-save-link-side-meta',
            'Save Feed Source',
            'wprss_save_feed_source_meta',
            'wprss_feed',
            'side',
            'high'
        );*/
        
        add_meta_box(
            'wprss-save-link-bottom-meta',
            'Save Feed Source',
            'wprss_save_feed_source_meta',
            'wprss_feed',
            'normal',
            'low'
        );

        add_meta_box(
            'wprss-help-meta',
            'WP RSS Aggregator Pro',
            'wprss_help_meta',
            'wprss_feed',
            'side',
            'low'
        );        
    }


    /**
     * wprss_save_feed_source_meta()
     * Generate the Save Feed Source meta box
     * @since 1.2
     */  

    function wprss_save_feed_source_meta() {
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
     * wprss_feed_name_meta()
     * Generate the Feed Name meta box
     * @since 1.2
     */  
    function wprss_feed_name_meta() {
        wp_nonce_field( plugin_basename( __FILE__ ), 'wprss_noncename' );        
                        
        echo '<p><input id="wprss-feed-name" name="post_title" value="' . $feedData['feed_name'] . '" size="50" type="text" /></p>';        
    }


    /**
     * wprss_feed_description_meta()
     * Generate the Feed Description meta box
     * @since 1.2
     */  
    function wprss_feed_description_meta() {
        wp_nonce_field( plugin_basename( __FILE__ ), 'wprss_noncename' );
        
        global $post;
        $feedData = unserialize( get_post_meta( $post->ID, 'feedData', true ) );
        echo '<p><input id="wprss-feed-description" name="wprss[feed_description]" value="' . $feedData['feed_description'] . '" size="150" type="text" /></p>';
        
    }   


    /**
     * wprss_feed_url_meta()
     * Generate the Feed URL meta box
     * @since 1.2
     */  
    function wprss_feed_url_meta() {
        wp_nonce_field( plugin_basename(__FILE__), 'wprss_noncename' );
        
        global $post;         
        $feedData = unserialize( get_post_meta( $post->ID, 'feedData', true ) );
        echo '<p><label class="infolabel" for="wprss[feedurl]">Feed URL (include http://)</label></p>';
        echo '<p><input id="wprss-feedurl" name="wprss[feedurl]" value="' . $feedData['feedurl'] . '" size="100" type="text" /></p>';
    }

    /**
     * wprss_help_meta()
     * Generate Help meta box
     * @since 1.2
     */  
    
    function wprss_help_meta() {
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
     * wprss_save_post() 
     * Save the custom fields (post meta) for the wprss_feed post type
     * @since  1.2
     */
    function wprss_save_post( $post_id ) {
        /* Make sure we only do this for wprss_feed on regular saves and we have permission */
        if ( $_POST[ 'post_type' ] != 'wprss_feed') {
            return $post_id;
        }
        
        if ( !wp_verify_nonce( $_POST[ 'wprss_noncename' ], plugin_basename( __FILE__ ) ) ||
            ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) ||
            !current_user_can( 'edit_page', $post_id ) ) {
            return $post_id;
        }

        $feedDataOrig = array();
        $feedDataOrig = unserialize( get_post_meta( $post_id, 'feedData', true ) );
        
        $feedDataNew = array();
        $feedDataNew = $_POST['wprss'];
        
        if (!empty($linkDataOrig)) {
            $linkData = array_merge($linkDataOrig, $linkDataNew);
        } else {
            $linkData = $linkDataNew;
        }
        
        /* Because we trick wordpress into setting the post title by using our field
        ** name as post_title we need to make sure our meta data is updated to reflect
        ** that correct name */
        $linkData['linkname'] = $_POST['post_title'];
        
        /* Update the link data */
        update_post_meta($post_id, 'thirstyData', serialize($linkData));
        
        if (isset($linkData['linkslug']) && !empty($linkData['linkslug'])) {
            $_POST['post_name'] = $linkData['linkslug'];
        }
        
        $_POST['post_status'] = 'publish';
    }


    /**
    * wprss_draft_to_publish()
    * Not yet in use by the plugin, need to implement it
    * Don't let user save drafts, make them go straight to published
    * @since 1.2
    */

    function wprss_draft_to_publish( $post_id ) {
        $current_post = get_post( $post_id, 'ARRAY_A' );
        $current_post[ 'post_status' ] = 'published';
        // Update the post into the database
        wp_update_post( $current_post );        
    }

?>