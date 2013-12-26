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
            'high'                                   // $priority
        );

        add_meta_box(
            'preview_meta_box', 
            __( 'Feed Preview', 'wprss' ), 
            'wprss_preview_meta_box_callback', 
            'wprss_feed', 
            'side', 
            'high'
        );

         add_meta_box(
            'wprss-feed-processing-meta', 
            __( 'Feed Processing', 'wprss' ), 
            'wprss_feed_processing_meta_box_callback', 
            'wprss_feed', 
            'side', 
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
            __( 'Like This Plugin?', 'wprss' ),
            'wprss_like_meta_box_callback',
            'wprss_feed',
            'side',
            'low'
        );   

        add_meta_box(
            'wprss-follow-meta',
            __( 'Follow Us', 'wprss' ),
            'wprss_follow_meta_box_callback',
            'wprss_feed',
            'side',
            'low'
        );   

        
        add_meta_box(
            'custom_meta_box', 
            __( 'Feed Source Details', 'wprss' ), 
            'wprss_show_meta_box_callback', 
            'wprss_feed', 
            'normal', 
            'high'
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
            'type'  => 'text',
            'after' => 'wprss_validate_feed_link',
        );
        
        $wprss_meta_fields[ 'description' ] = array(
            'label' => __( 'Description', 'wprss' ),
            'desc'  => __( 'A short description about this feed source (optional)', 'wprss' ),
            'id'    => $prefix .'description',
            'type'  => 'textarea'
        );    

        $wprss_meta_fields[ 'limit' ] = array(
            'label' => __( 'Limit', 'wprss' ),
            'desc'  => __( 'Enter a feed item import/display limit. Leave blank to use the default setting.', 'wprss' ),
            'id'    => $prefix . 'limit',
            'type'  => 'number'
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
            echo '<table class="form-table wprss-form-table">';

            foreach ( $meta_fields as $field ) {
                // get value of this field if it exists for this post
                $meta = get_post_meta( $post->ID, $field['id'], true );
                // begin a table row with
                echo '<tr>
                        <th><label for="' . $field['id'] . '">' . $field['label'] . '</label></th>
                        <td>';
                        
                        if ( isset( $field['before'] ) && !empty( $field['before'] ) ) {
                            call_user_func( $field['before'] );
                        }

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
                        
                            // number
                            case 'number':
                                echo '<input class="wprss-number-roller" type="number" placeholder="Default" min="0" name="'.$field['id'].'" id="'.$field['id'].'" value="'.esc_attr( $meta ).'" />
                                    <label for="'.$field['id'].'"><span class="description">'.$field['desc'].'</span></label>';

                            break;

                        } //end switch

                        if ( isset( $field['after'] ) && !empty( $field['after'] ) ) {
                            call_user_func( $field['after'] );
                        }

                echo '</td></tr>';
            } // end foreach
            echo '</table>'; // end table
    }
  

    /**
     * Adds the link that validates the feed
     * @since 3.9.5 
     */
    function wprss_validate_feed_link() {
        ?>
            <a href="#" id="validate-feed-link">Validate feed</a>
            <script type="text/javascript">
                (function($){
                    // When the DOM is ready
                    $(document).ready( function(){
                        // Move the link immediately after the url text field, and add the click event handler
                        $('#validate-feed-link').insertAfter('#wprss_url').click(function(e){
                            // Get the url and proceed only if the url is not empty
                            var url = $('#wprss_url').val();
                            if ( url.trim().length > 0 ) {
                                // Encode the url and generate the full url to the w3 feed validator
                                var encodedUrl = encodeURIComponent( url );
                                var fullURL = 'http://validator.w3.org/feed/check.cgi?url=' + encodedUrl;
                                // Open the window / tab
                                window.open( fullURL, 'wprss-feed-validator' );
                            }
                            // Suppress the default link click behaviour
                            e.preventDefault();
                            e.stopPropagation();
                            return false;
                        });
                    });
                })(jQuery);
            </script>
        <?php
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
        
        // Change the limit, if it is zero, to an empty string
        if ( isset( $_POST['wprss_limit'] ) && strval( $_POST['wprss_limit'] ) == '0' ) {
            $_POST['wprss_limit'] = '';
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

        $state = ( isset( $_POST['wprss_state'] ) )? $_POST['wprss_state'] : 'active';
        $activate = ( isset( $_POST['wprss_activate_feed'] ) )? stripslashes( $_POST['wprss_activate_feed'] ) : '';
        $pause = ( isset( $_POST['wprss_pause_feed'] ) )? stripslashes( $_POST['wprss_pause_feed'] ) : '';
        $age_limit = ( isset( $_POST['wprss_age_limit'] ) )? stripslashes( $_POST['wprss_age_limit'] ) : '';
        $age_unit = ( isset( $_POST['wprss_age_unit'] ) )? stripslashes( $_POST['wprss_age_unit'] ) : '';
        $update_interval = ( isset( $_POST['wprss_update_interval'] ) )? stripslashes( $_POST['wprss_update_interval'] ) : wprss_get_default_feed_source_update_interval();
        $old_update_interval = get_post_meta( $post_id, 'wprss_update_interval', TRUE );

        // Update the feed source meta
        update_post_meta( $post_id, 'wprss_activate_feed', $activate );
        update_post_meta( $post_id, 'wprss_pause_feed', $pause );
        update_post_meta( $post_id, 'wprss_age_limit', $age_limit );
        update_post_meta( $post_id, 'wprss_age_unit', $age_unit );
        update_post_meta( $post_id, 'wprss_update_interval', $update_interval );

        // Check if the state or the update interval has changed
        if ( get_post_meta( $post_id, 'wprss_state', TRUE ) !== $state || $old_update_interval !== $update_interval ) {
            // Pause the feed source, and if it is active, re-activate it.
            // This should update the feed's scheduling
            wprss_pause_feed_source( $post_id );
            if ( $state === 'active' )
                wprss_activate_feed_source( $post_id );
        }

        // Update the schedules
        wprss_update_feed_processing_schedules( $post_id );

        // If the feed source uses the global updating system, update the feed on publish
        if ( $update_interval === wprss_get_default_feed_source_update_interval() ) {
            wp_schedule_single_event( time(), 'wprss_fetch_single_feed_hook', array( $post_id ) );
        }
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
                // Figure out how many total items there are, but limit it to 5. 
                $maxitems = $feed->get_item_quantity(5); 

                // Build an array of all the items, starting with element 0 (first element).
                $items = $feed->get_items( 0, $maxitems );  
                echo '<h4>Latest 5 feeds available from ' . get_the_title() . '</h4>'; 
                echo '<ul>';
                foreach ( $items as $item ) { 
                    // Get human date (comment if you want to use non human date)
                    $item_date = human_time_diff( $item->get_date('U'), current_time('timestamp')).' '.__( 'ago', 'rc_mdm' );                                   
                    // Start displaying item content within a <li> tag
                    echo '<li>';
                    // create item link
                    //echo '<a href="'.esc_url( $item->get_permalink() ).'" title="'.$item_date.'">';
                    // Get item title
                    echo esc_html( $item->get_title() );
                    //echo '</a>';
                    // Display date
                    echo ' <div class="rss-date"><small>'.$item_date.'</small></div>';
                    // End <li> tag
                    echo '</li>';
                }  
                echo '</ul>';
            }
            else _e( '<span class="invalid-feed-url"><strong>Invalid feed URL</strong> - Double check the feed source URL setting above.</span>
                      <p>Not sure where to find the RSS feed on a website? 
                      <a target="_blank" href="http://webtrends.about.com/od/webfeedsyndicationrss/ss/rss_howto.htm">Click here</a> for a visual guide' , 'wprss' );
        }

        else _e( 'No feed URL defined yet', 'wprss' );
    }



    /**
     * Renders the Feed Processing metabox
     * 
     * @since 3.7
     */
    function wprss_feed_processing_meta_box_callback() {
        global $post;
        // Get the post meta
        $state = get_post_meta( $post->ID, 'wprss_state', TRUE );
        $activate = get_post_meta( $post->ID, 'wprss_activate_feed', TRUE );
        $pause = get_post_meta( $post->ID, 'wprss_pause_feed', TRUE );
        $update_interval = get_post_meta( $post->ID, 'wprss_update_interval', TRUE );

        $age_limit = get_post_meta( $post->ID, 'wprss_age_limit', FALSE );
        $age_unit = get_post_meta( $post->ID, 'wprss_age_unit', FALSE );

        $age_limit = ( count( $age_limit ) === 0 )? wprss_get_general_setting( 'limit_feed_items_age' ) : $age_limit[0];
        $age_unit = ( count( $age_unit ) === 0 )? wprss_get_general_setting( 'limit_feed_items_age_unit' ) : $age_unit[0];

        // Set default strings for activate and pause times
        $default_activate = 'immediately';
        $default_pause = 'never';

        // Prepare the states
        $states = array(
            'active'    =>  __( 'Active', 'wprss' ),
            'paused'    =>  __( 'Paused', 'wprss' ),
        );

        // Prepare the schedules
        $default_interval = __( 'Default', 'wprss' );
        $wprss_schedules = apply_filters( 'wprss_schedules', wprss_get_schedules() );
        $default_interval_key = wprss_get_default_feed_source_update_interval();
        $schedules = array_merge(
            array(
                $default_interval_key => array(
                    'display'   =>  $default_interval,
                    'interval'  =>  $default_interval,
                ),
            ),
            $wprss_schedules
        );

        ?>

        <div class="wprss-meta-side-setting">
            <label for="wprss_state">Feed state:</label>
            <select id="wprss_state" name="wprss_state">
                <?php foreach( $states as $value => $label ) : ?>
                    <option value="<?php echo $value; ?>" <?php selected( $state, $value ) ?> ><?php echo $label; ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="wprss-meta-side-setting">
            <p>
                <label for="">Activate feed: </label>
                <strong id="wprss-activate-feed-viewer"><?php echo ( ( $activate !== '' )? $activate : $default_activate ); ?></strong>
                <a href="#">Edit</a>
            </p>
            <div class="wprss-meta-slider" data-collapse-viewer="wprss-activate-feed-viewer" data-default-value="<?php echo $default_activate; ?>">
                <input id="wprss_activate_feed" class="wprss-datetimepicker-from-today" name="wprss_activate_feed" value="<?php echo $activate; ?>" />

                <label class="description" for="wprss_activate_feed">
                    Leave blank to activate the feed immediately.
                </label>

                <br/><br/>

                <span class="description">
                    <b>Note:</b> WordPress uses UTC time for schedules, not local time. Current UTC time is: <code><?php echo date( 'd/m/Y H:i:s', current_time('timestamp',1) ); ?></code>
                </span>
            </div>
        </div>

        <div class="wprss-meta-side-setting">
            <p>
                <label for="">Pause feed: </label>
                <strong id="wprss-pause-feed-viewer"><?php echo ( ( $pause !== '' )? $pause : $default_pause ); ?></strong>
                <a href="#">Edit</a>
            </p>
            <div class="wprss-meta-slider" data-collapse-viewer="wprss-pause-feed-viewer" data-default-value="<?php echo $default_pause; ?>">
                <input id="wprss_pause_feed" class="wprss-datetimepicker-from-today" name="wprss_pause_feed" value="<?php echo $pause; ?>" />
                <label class="description" for="wprss_pause_feed">
                    Leave blank to never pause the feed.
                </label>
                <br/><br/>
                <span class="description">
                    <b>Note:</b> WordPress uses UTC time for schedules, not local time. Current UTC time is: <code><?php echo date( 'd/m/Y H:i:s', current_time('timestamp',1) ); ?></code>
                </span>
            </div>
        </div>


        <div class="wprss-meta-side-setting">
            <p>
                <label for="">Update interval: </label>
                <strong id="wprss-feed-update-interval-viewer">
                    <?php
                        if ( $update_interval === '' || $update_interval === wprss_get_default_feed_source_update_interval() ) {
                            echo $default_interval;
                        }
                        else {
                            echo wprss_interval( $schedules[$update_interval]['interval'] );
                        }
                    ?>
                </strong>
                <a href="#">Edit</a>
            </p>
            <div class="wprss-meta-slider" data-collapse-viewer="wprss-feed-update-interval-viewer" data-default-value="<?php echo $default_interval; ?>">
                <select id="feed-update-interval" name="wprss_update_interval">
                <?php foreach ( $schedules as $value => $schedule ) : ?>
                    <?php $text = ( $value === wprss_get_default_feed_source_update_interval() )? $default_interval : wprss_interval( $schedule['interval'] ); ?>
                    <option value="<?php echo $value; ?>" <?php selected( $update_interval, $value ); ?> ><?php echo $text; ?></option>
                <?php endforeach; ?>
                </select>
                
                <br/>
                <span class='description' for='feed-update-interval'>
                    Enter the interval at which to update this feed. The feed will only be updated if it is <strong>active</strong>.
                </span>
            </div>
        </div>


        <div class="wprss-meta-side-setting">
            <p>
                <label id="wprss-age-limit-feed-label" for="" data-when-empty="Delete old feed items:">Delete feed items older than: </label>
                <strong id="wprss-age-limit-feed-viewer"><?php echo $age_limit . ' ' . $age_unit; ?></strong>
                <a href="#">Edit</a>
            </p>
            <div class="wprss-meta-slider" data-collapse-viewer="wprss-age-limit-feed-viewer" data-label="#wprss-age-limit-feed-label" data-default-value="" data-empty-controller="#limit-feed-items-age" data-hybrid="#limit-feed-items-age, #limit-feed-items-age-unit">
                <input id="limit-feed-items-age" name="wprss_age_limit" type="number" min="0" class="wprss-number-roller" placeholder="No limit" value="<?php echo $age_limit; ?>" />

                <select id="limit-feed-items-age-unit" name="wprss_age_unit">
                <?php foreach ( wprss_age_limit_units() as $unit ) : ?>
                    <option value="<?php echo $unit; ?>" <?php selected( $age_unit, $unit ); ?> ><?php echo $unit; ?></option>
                <?php endforeach; ?>
                </select>
                
                <br/>
                <span class='description' for='limit-feed-items-age'>
                    Enter the maximum age of feed items to be stored in the database. Feed items older than the specified age will be deleted everyday at midnight.
                    <br/>
                    Leave empty for no limit.
                </span>
            </div>
        </div>

        <?php
    }



    /**     
     * Generate Help meta box
     * 
     * @since 2.0
     * 
     */      
    function wprss_help_meta_box_callback() {        
       echo '<p><a href="http://www.wprssaggregator.com/documentation/">View the documentation</p>';
       echo '<p><strong>';
       _e( 'Need help?', 'wprss' );
       echo '</strong> <a target="_blank" href="http://wordpress.org/support/plugin/wp-rss-aggregator">';
       _e( 'Check out the support forum', 'wprss' ); 
       echo '</a></p>';
       echo '</strong> <a target="_blank" href="http://www.wprssaggregator.com/feature-requests/">';       
       _e( 'Suggest a new feature', 'wprss' ); 
       echo '</a></p>';       
    }

    /**     
     * Generate Like this plugin meta box
     * 
     * @since 2.0
     * 
     */      
    function wprss_like_meta_box_callback() { ?>
        
        <ul>
            <li><a href="http://wordpress.org/extend/plugins/wp-rss-aggregator/"><?php _e( 'Give it a 5 star rating on WordPress.org', 'wprss' ) ?></a></li>                               
            <li class="donate_link"><a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=X9GP6BL4BLXBJ"><?php _e('Donate a token of your appreciation', 'wprss' ); ?></a></li>
        </ul>       
        <?php
        echo '<p><strong>'; 
        _e( 'Check out the Premium Extensions:', 'wprss' );
        echo '</strong>'; ?>
        <ul>
            <li><a href="http://www.wprssaggregator.com/extension/feed-to-post/"><?php echo 'Feed to Post'; ?></a></li>                                         
            <li><a href="http://www.wprssaggregator.com/extension/excerpts-thumbnails/"><?php echo 'Excerpts & Thumbnails'; ?></a></li>                               
            <li><a href="http://www.wprssaggregator.com/extension/categories/"><?php echo 'Categories'; ?></a></li>
            <li><a href="http://www.wprssaggregator.com/extension/keyword-filtering/"><?php echo 'Keyword Filtering'; ?></a></li>
        </ul>   
         </p>
    <?php } 


    /**     
     * Generate Follow us plugin meta box
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
        remove_meta_box( 'wpar_plugin_meta_box ', 'wprss_feed' ,'normal' );                      
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