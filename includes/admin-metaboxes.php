<?php

    add_action( 'add_meta_boxes', 'wprss_add_meta_boxes');
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
            'submitdiv',                            // $id
            __( 'Save Feed Source', 'wprss' ),      // $title 
            'post_submit_meta_box',                 // $callback
            'wprss_feed',                           // $page
            'side',                                 // $context
            'low'                                   // $priority
        );

        add_meta_box(
            'custom_meta_box', 
            __( 'Feed Source Details', 'wprss' ), 
            'wprss_show_meta_box_callback', 
            'wprss_feed', 
            'normal', 
            'high'
        );         

        add_meta_box(
            'wprss-help-meta',
            __( 'WP RSS Aggregator Help', 'wprss' ),
            'wprss_help_meta_box_callback',
            'wprss_feed',
            'side',
            'low'
        );  

        add_meta_box(
            'wprss-like-meta',
            __( 'Like this plugin?', 'wprss' ),
            'wprss_like_meta_box_callback',
            'wprss_feed',
            'side',
            'low'
        );   

        add_meta_box(
            'wprss-follow-meta',
            __( 'Follow us', 'wprss' ),
            'wprss_follow_meta_box_callback',
            'wprss_feed',
            'side',
            'low'
        );   

        add_meta_box(
            'preview_meta_box', 
            __( 'Feed Preview', 'wprss' ), 
            'wprss_preview_meta_box_callback', 
            'wprss_feed', 
            'normal', 
            'low'
        ); 
    } 


    /**     
     * Set up fields for the meta box for the wprss_feed post type
     * 
     * @since 2.0
     */       
    function wprss_get_custom_fields() {
        $prefix = 'wprss_';
        
        // Field Array
        $wprss_meta_fields[ 'url' ] = array(
            'label' => __( 'URL', 'wprss' ),
            'desc'  => __( 'Enter feed URL (including http://)', 'wprss' ),
            'id'    => $prefix .'url',
            'type'  => 'text'
        );
        
        $wprss_meta_fields[' description' ] = array(
            'label' => __( 'Description', 'wprss' ),
            'desc'  => __( 'A short description about this feed source (optional)', 'wprss' ),
            'id'    => $prefix .'description',
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
    function wprss_show_meta_box_callback() {
        global $post;
        $meta_fields = wprss_get_custom_fields();

        // Use nonce for verification
        wp_nonce_field( basename( __FILE__ ), 'wprss_meta_box_nonce' ); 

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
                                echo '<input type="text" name="'.$field['id'].'" id="'.$field['id'].'" value="'. esc_attr( $meta ) .'" size="55" />
                                    <br><span class="description">'.$field['desc'].'</span>';
                            break;
                        
                            // textarea
                            case 'textarea':
                                echo '<textarea name="'.$field['id'].'" id="'.$field['id'].'" cols="60" rows="4">'. esc_attr( $meta ) .'</textarea>
                                    <br><span class="description">'.$field['desc'].'</span>';
                            break;
                        
                            // checkbox
                            case 'checkbox':
                                echo '<input type="checkbox" name="'.$field['id'].'" id="'.$field['id'].'" ', esc_attr( $meta ) ? ' checked="checked"' : '','/>
                                    <label for="'.$field['id'].'">'.$field['desc'].'</label>';
                            break;    
                        
                            // select
                            case 'select':
                                echo '<select name="'.$field['id'].'" id="'.$field['id'].'">';
                                foreach ($field['options'] as $option) {
                                    echo '<option', $meta == $option['value'] ? ' selected="selected"' : '', ' value="'.$option['value'].'">'.$option['label'].'</option>';
                                }
                                echo '</select><br><span class="description">'.$field['desc'].'</span>';
                            break;                                            
                        
                        } //end switch
                echo '</td></tr>';
            } // end foreach
            echo '</table>'; // end table
    }
  

    add_action( 'save_post', 'wprss_save_custom_fields', 10, 2 ); 
    /**     
     * Save the custom fields
     * 
     * @since 2.0
     */ 
    function wprss_save_custom_fields( $post_id, $post ) {
        $meta_fields = wprss_get_custom_fields();

        /* Verify the nonce before proceeding. */
        if ( !isset( $_POST['wprss_meta_box_nonce'] ) || !wp_verify_nonce( $_POST['wprss_meta_box_nonce'], basename( __FILE__ ) ) )
            return $post_id;               

        /* Get the post type object. */
        $post_type = get_post_type_object( $post->post_type );

        /* Check if the current user has permission to edit the post. */
        if ( !current_user_can( $post_type->cap->edit_post, $post_id ) )
            return $post_id;        

     /*  // Stop WP from clearing custom fields on autosave - maybe not needed
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
            return;

        // Prevent quick edit from clearing custom fields - maybe not needed
        if (defined('DOING_AJAX') && DOING_AJAX)
            return;     */

        /** Bail out if running an autosave, ajax or a cron */
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
            return;
        if ( defined( 'DOING_AJAX' ) && DOING_AJAX )
            return;
        if ( defined( 'DOING_CRON' ) && DOING_CRON )
            return;        
        
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
     * Generate a preview of the latest 5 posts from the feed source being added/edited
     * 
     * @since 2.0
     */  
    function wprss_preview_meta_box_callback() {
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
    function wprss_help_meta_box_callback() {
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
    function wprss_like_meta_box_callback() { ?>
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
    function wprss_follow_meta_box_callback() {    
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
        // Remove meta boxes of other plugins that tend to appear on all posts          
        remove_meta_box( 'wpseo_meta', 'wprss_feed' ,'normal' );
        remove_meta_box( 'postpsp', 'wprss_feed' ,'normal' );
        remove_meta_box( 'su_postmeta', 'wprss_feed' ,'normal' );
        remove_meta_box( 'woothemes-settings', 'wprss_feed' ,'normal' ); 
        remove_meta_box( 'wpcf-post-relationship', 'wprss_feed' ,'normal' );                 
        remove_meta_box( 'sharing_meta', 'wprss_feed' ,'advanced' );
        remove_meta_box( 'content-permissions-meta-box', 'wprss_feed' ,'advanced' );       
        remove_meta_box( 'theme-layouts-post-meta-box', 'wprss_feed' ,'side' );
        remove_meta_box( 'post-stylesheets', 'wprss_feed' ,'side' );
        remove_meta_box( 'hybrid-core-post-template', 'wprss_feed' ,'side' );
        remove_meta_box( 'wpcf-marketing', 'wprss_feed' ,'side' );
        remove_meta_box( 'trackbacksdiv22', 'wprss_feed' ,'advanced' ); 
        remove_meta_box( 'aiosp', 'wprss_feed' ,'advanced' );                             
        remove_action( 'post_submitbox_start', 'fpp_post_submitbox_start_action' );    
    }