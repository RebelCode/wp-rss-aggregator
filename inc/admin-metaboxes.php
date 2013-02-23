<?php


    /**     
     * Set up fields for the meta box for the wprss_feed post type
     * 
     * @since 2.0
     */       
    function wprss_custom_fields() {
        $prefix = 'wprss_';
        
        // Field Array
        $wprss_meta_fields[ 'url' ] = array(
            'label' => __( 'URL', 'wprss' ),
            'desc'  => __( 'Enter feed URL (including http://)', 'wprss' ),
            'id'    => $prefix.'url',
            'type'  => 'text'
        );
        
        $wprss_meta_fields[' description' ] = array(
            'label' => __( 'Description', 'wprss' ),
            'desc'  => __( 'A short description about this feed source (optional)', 'wprss' ),
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
  

    add_action( 'save_post', 'wprss_save_custom_fields' ); 
    /**     
     * Save the custom fields
     * 
     * @since 2.0
     */ 
    function wprss_save_custom_fields( $post_id ) {
        $meta_fields = wprss_custom_fields();
        
        // verify nonce
        if ( ! wp_verify_nonce( $_POST[ 'wprss_meta_box_nonce' ], basename( __FILE__ ) ) ) 
           return $post_id; 
        
        // check autosave
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE)
            return $post_id;
        
        // check permissions
        if ( 'page' == $_POST[ 'post_type' ] ) {
            if ( ! current_user_can( 'edit_page', $post_id ) )
                return $post_id;
            } elseif ( ! current_user_can( 'edit_post', $post_id ) ) {
                return $post_id;
        }
        
        // loop through fields and save the data
        foreach ( $meta_fields as $field ) {
            $old = get_post_meta( $post_id, $field[ 'id' ], true );
            $new = $_POST[ $field[ 'id' ] ];
            if ( $new && $new != $old ) {
                update_post_meta( $post_id, $field[ 'id' ], $new );
            } elseif ( '' == $new && $old ) {
                delete_post_meta( $post_id, $field[ 'id' ], $old );
            }
        } // end foreach
    } 

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
            if ( ! EMPTY_TRASH_DAYS )
                $delete_text = __( 'Delete Permanently', 'wprss' );
            else
                $delete_text = __( 'Move to Trash', 'wprss' );
                
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
        
        if( ! empty( $feed_url ) ) {             
            $feed = fetch_feed( $feed_url ); 
            if ( ! is_wp_error( $feed ) ) {
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
            else _e( '<span class="invalid-feed-url"><strong>Invalid feed URL</strong> - Double check the feed source URL setting above.</span>
                      <p>Not sure where to find the RSS feed on a website? 
                      <a target="_blank" href="http://webtrends.about.com/od/webfeedsyndicationrss/ss/rss_howto.htm">Click here</a> for a visual guide' , 'wprss' );
        }

        else _e( 'No feed URL defined yet', 'wprss' );
    }


    /**     
     * Generate Help meta box
     * 
     * @since 2.0
     * 
     */      
    function wprss_help_meta_box() {
       echo '<p><strong>';
       _e( 'Need help?', 'wprss' );
       echo '</strong> <a target="_blank" href="http://wordpress.org/support/plugin/wp-rss-aggregator">';
       _e( 'Check out the support forum', 'wprss' ); 
       echo '</a></p>';
    }

    /**     
     * Generate Like this plugin meta box
     * 
     * @since 2.0
     * 
     */      
    function wprss_like_meta_box() { ?>
        <p><?php _e( 'Why not do any or all of the following', 'wprss' ) ?>:</p>
        <ul>
            <li><a href="http://wordpress.org/extend/plugins/wp-rss-aggregator/"><?php _e( 'Give it a 5 star rating on WordPress.org.', 'wprss' ) ?></a></li>                               
            <li class="donate_link"><a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=X9GP6BL4BLXBJ"><?php _e('Donate a token of your appreciation.', 'wprss' ); ?></a></li>
        </ul>       
         </p>
    <?php } 


    /**     
     * Generate Like this plugin meta box
     * 
     * @since 2.0
     * 
     */      
    function wprss_follow_meta_box() {    
        ?>                         
        <ul>
            <li class="twitter"><a href="http://twitter.com/wpmayor"><?php _e( 'Follow WP Mayor on Twitter.', 'wprss' ) ?></a></li>
            <li class="facebook"><a href="https://www.facebook.com/wpmayor"><?php _e( 'Like WP Mayor on Facebook.', 'wprss' ) ?></a></li>

        </ul>                               
    <?php }


    add_action( 'add_meta_boxes', 'wprss_remove_meta_boxes', 100 );
    /**
     * Remove unneeded meta boxes from add feed source screen
     * 
     * @since 2.0
     */       
    function wprss_remove_meta_boxes() {
        if ( 'wprss_feed' !== get_current_screen()->id ) return;     
        remove_meta_box( 'wpseo_meta', 'wprss_feed' ,'normal' );
        remove_meta_box( 'woothemes-settings', 'wprss_feed' ,'normal' ); 
        remove_meta_box( 'wpcf-post-relationship', 'wprss_feed' ,'normal' );                 
        remove_meta_box( 'sharing_meta', 'wprss_feed' ,'advanced' );
        remove_meta_box( 'content-permissions-meta-box', 'wprss_feed' ,'advanced' );       
        remove_meta_box( 'theme-layouts-post-meta-box', 'wprss_feed' ,'side' );
        remove_meta_box( 'post-stylesheets', 'wprss_feed' ,'side' );
        remove_meta_box( 'hybrid-core-post-template', 'wprss_feed' ,'side' );
        remove_meta_box( 'wpcf-marketing', 'wprss_feed' ,'side' );
        remove_meta_box( 'trackbacksdiv22', 'wprss_feed' ,'advanced' );     
        remove_action( 'post_submitbox_start', 'fpp_post_submitbox_start_action' );    
    }