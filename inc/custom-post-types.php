<?php    

    /**
     * wprss_register_post_type()
     * Create Custom Post Type
     * @since 1.2
     */             
    
    function wprss_register_post_types() {        
        
        // Set up the arguments for the 'feed' post type
        $feed_args = array(
            'public' => true,
            'query_var' => 'feed',
            'rewrite' => array(
                'slug' => 'feeds',
                'with_front' => false
            ),
            'supports' => array(    
                ''                          
                /*  'thumbnail',  FUTURE */
            ),
            'show_in_menu' => true,
            'labels' => array(
                'name' => __('Feed Sources'),
                'singular_name' => __('Feed'),
                'add_new' => __('Add New Feed'),
                'all_items' => __('All Feeds'),
                'add_new_item' => __('Add New Feed'),
                'edit_item' => __('Edit Feed'),
                'new_item' => __('New Feed'),
                'view_item' => __('View Feed'),
                'search_items' => __('Search Feeds'),
                'not_found' => __('No Feeds Found'),
                'not_found_in_trash' => __('No Feeds Found In Trash'),
                'menu_name' => __('RSS Aggregator')
            ),
            'menu_position' => 100,
            'menu_icon' => WPRSS_IMG . 'icon-adminmenu16-sprite.png'
        );
        
        // Register the 'feed' post type
        register_post_type( 'wprss_feed', $feed_args );
        

        // Set up the arguments for the 'feed_item' post type
        $feed_item_args = array(
            'public' => true,
            'query_var' => 'feed_item',
            'rewrite' => array(
                'slug' => 'feeds/items',
                'with_front' => false,
            ),
            'supports' => array(
                'title',
                'editor',
                'thumbnail',
                'custom-fields'
            ),
            'show_in_menu' => 'edit.php?post_type=wprss_feed',
            'labels' => array(
                'name' => 'Feed Items',
                'singular_name' => 'Feed',
                'add_new' => 'Add New Feed',
                'all_items' => 'Imported Feeds',
                'add_new_item' => 'Add New Feed',
                'edit_item' => 'Edit Feed',
                'new_item' => 'New Feed',
                'view_item' => 'View Feed',
                'search_items' => 'Search Feeds',
                'not_found' => 'No Feeds Found',
                'not_found_in_trash' => 'No Feeds Found In Trash',
                //'menu_name' => 'RSS Aggregator'
            ),            
        );
        
        // Register the 'feed_item' post type
        register_post_type( 'wprss_feed_item', $feed_item_args );        
    }
    
    
    
    /**
     * wprss_register_taxonomies
     * Create Taxonomy for storing feed source
     * @since 1.0
     */         

    // Registers taxonomies. 
    function wprss_register_taxonomies() {

        // Set up the feed_source taxonomy arguments.
        $source_args = array(
            'hierarchical' => true,
            'query_var' => 'source', 
            'show_tagcloud' => false,
            'rewrite' => array(
                'slug' => 'feed/sources',
                'with_front' => false
            ),
            /*  'labels' => array(
                'name' => 'Genres',
                'singular_name' => 'Genre',
                'edit_item' => 'Edit Genre',
                'update_item' => 'Update Genre',
                'add_new_item' => 'Add New Genre',
                'new_item_name' => 'New Genre Name',
                'all_items' => 'All Genres',
                'search_items' => 'Search Genres',
                'parent_item' => 'Parent Genre',
                'parent_item_colon' => 'Parent Genre:',
            ),*/
        );
    
        // Register the feed_source taxonomy
        register_taxonomy( 'feed_source', array( 'feed_item' ), $source_args );
        
        // Set up the aggregator taxonomy arguments.
        $source_args = array(
            'hierarchical' => true,
            'query_var' => 'aggregator', 
            'show_tagcloud' => false,
            'rewrite' => array(
                'slug' => 'aggregators',
                'with_front' => false
            ),
            /*  'labels' => array(
                'name' => 'Genres',
                'singular_name' => 'Genre',
                'edit_item' => 'Edit Genre',
                'update_item' => 'Update Genre',
                'add_new_item' => 'Add New Genre',
                'new_item_name' => 'New Genre Name',
                'all_items' => 'All Genres',
                'search_items' => 'Search Genres',
                'parent_item' => 'Parent Genre',
                'parent_item_colon' => 'Parent Genre:',
            ),*/
        );
    
        // Register the aggregator taxonomy
        register_taxonomy( 'aggregator', array( 'feed_item', 'feed' ), $source_args );

 

    //    add_filter('manage_wprss_feed_posts_columns', 'wprss_add_category_column');
      //  add_action('manage_pages_custom_column', 'wprss_show_category_column');
           
    }

   /* Set the list page columns */
    add_filter( 'manage_edit-wprss_feed_columns', 'my_edit_columns');
    
    function my_edit_columns( $columns ) {

        $columns = array (
            'cb' => '<input type="checkbox" />',
            'name' => __( 'Name' ),
            'description' => __( 'Description' ),
            'genre' => __( 'Genre' ),
        );
        return $columns;
    }


    /**
     * wprss_add_meta_boxes
     * Set up the input boxes for the wprss_feed post type
     * @since 1.0
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
        
        /*
        FUTURE VERSION
        add_meta_box(
            'wprss-feed-thumbnail-meta',
            'Set Thumbnail',
            'wprss_feed_thumbnail_meta',
            'wprss_feed',
            'normal',
            'high'
        );
        */
        
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
     * @since 1.0
     */  

    function wprss_save_feed_source_meta() {
        global $post;
        
        //echo '<input name="original_publish" type="hidden" id="original_publish" value="Save" />';
        echo '<input name="save" type="submit" class="button-primary" id="publish" tabindex="5" accesskey="p" value="Save">';
        
        if (current_user_can("delete_post", $post->ID)) {
            if (!EMPTY_TRASH_DAYS)
                $delete_text = __('Delete Permanently');
            else
                $delete_text = __('Move to Trash');
                
            echo '&nbsp;&nbsp;<a class="submitdelete deletion" href="' . get_delete_post_link($post->ID) . '">' . $delete_text . '</a>';
        }
    }

    /**
     * wprss_feed_name_meta()
     * Generate the Feed Name meta box
     * @since 1.0
     */  

    function wprss_feed_name_meta() {
        wp_nonce_field( plugin_basename(__FILE__), 'wprss_noncename' );
        
        global $post;
        $feedData = unserialize(get_post_meta($post->ID, 'feedData', true));
        $feedData['nofollow'] = isset($feeedData['nofollow']) ? 'checked="checked"' : '';
        $feedData['newwindow'] = isset($feedData['newwindow']) ? 'checked="checked"' : '';
        
        $wprss_options = get_option('wprss_options');
        //echo '<p><label class="infolabel" for="post_title">Feed Name:</label></p>';
        echo '<p><input id="wprss-feed-name" name="post_title" value="' . $feedData['feed_name'] . '" size="50" type="text" /></p>';
        
    }


    /**
     * wprss_feed_description_meta()
     * Generate the Feed Description meta box
     * @since 1.0
     */  

    function wprss_feed_description_meta() {
        wp_nonce_field( plugin_basename(__FILE__), 'wprss_noncename' );
        
        global $post;
        $feedData = unserialize(get_post_meta($post->ID, 'feedData', true));
        $feedData['nofollow'] = isset($feeedData['nofollow']) ? 'checked="checked"' : '';
        $feedData['newwindow'] = isset($feedData['newwindow']) ? 'checked="checked"' : '';
        
        $wprss_options = get_option('wprss_options');
        //echo '<p><label class="infolabel" for="post_title">Feed Name:</label></p>';
        echo '<p><input id="wprss-feed-description" name="post_title" value="' . $feedData['feed_description'] . '" size="150" type="text" /></p>';
        
    }   


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
     * wprss_feed_url_meta()
     * Generate the Feed URL meta box
     * @since 1.0
     */  

    function wprss_feed_url_meta() {
        wp_nonce_field( plugin_basename(__FILE__), 'wprss_noncename' );
        
        global $post;
        $feedData = unserialize(get_post_meta($post->ID, 'feedData', true));
        $feedData['nofollow'] = isset($feedData['nofollow']) ? 'checked="checked"' : '';
        $feedData['newwindow'] = isset($feedData['newwindow']) ? 'checked="checked"' : '';
        
        $wprss_Options = get_option('wprss_options');
        
        echo '<p><label class="infolabel" for="thirsty[linkurl]">Destination URL:</label></p>';
        echo '<p><input id="thirsty_linkurl" name="thirsty[linkurl]" value="' . $linkData['linkurl'] . '" size="100" type="text" /></p>';
        
        /* Only show permalink if it's an existing post */
        if (!empty($post->post_title)) {
            echo '<p><label class="infolabel">Cloaked URL:</label></p>';
            echo '<input type="text" readonly="readonly" id="thirsty_cloakedurl" value="' . get_permalink($post->ID) . '"> <span class="button-secondary" id="thirstyEditSlug">Edit Slug</span> <a href="' . get_permalink($post->ID) . '" target="_blank"><span class="button-secondary" id="thirstyVisitLink">Visit Link</span></a><input id="thirsty_linkslug" name="post_name" value="' . $post->post_name . '" size="50" type="text" /></span> <input id="thirstySaveSlug" type="button" value="Save" class="button-secondary" /></p>';
        }
        
        /* Only display link nofollow setting if the global nofollow setting is disabled */
        if ($thirstyOptions['nofollow'] != 'on') {
            echo '<p><label class="infolabel" for="thirsty[nofollow]">No follow this link?:</label></p>';
            echo '<p>&nbsp;<input id="thirsty_nofollow" name="thirsty[nofollow]" ' . $linkData['nofollow'] . ' type="checkbox" />';
            echo '<span class="thirsty_description">Overrides the global no follow setting for this link</span</p>';
        }
        
        /* Only display link new window setting if the global new window setting is disabled */
        if ($thirstyOptions['newwindow'] != 'on') {
            echo '<p><label class="infolabel" for="thirsty[newwindow]">Open this link in new window?</label></p>';
            echo '<p>&nbsp;<input id="thirsty_newwindow" name="thirsty[newwindow]" ' . $linkData['newwindow'] . ' type="checkbox" />';
            echo '<span class="thirsty_description">Overrides the global new window setting for this link</span></p>';
        }
    }





    /**
    * wprss_draft_to_publish()
    * Don't let user save drafts, make them go straight to published
    * @since 1.0
    */

    function wprss_draft_to_publish( $post_id ) {
        $update_status_post = array();
        $update_status_post['ID'] = $post_id;
        $update_status_post['post_status'] = 'publish';
        
        // Update the post into the database
        wp_update_post( $update_status_post );
    }

?>