<?php

    add_action( 'add_meta_boxes', function () {
        // Remove some plugin's metaboxes because they're not relevant to the wprss_feed post type.
        wprss_remove_unrelated_meta_boxes();

        // Remove the default WordPress Publish box, because we will be using custom ones
        remove_meta_box( 'submitdiv', 'wprss_feed', 'side' );
        // Custom Publish box
        add_meta_box(
            'submitdiv',
            __( 'Save Feed Source', WPRSS_TEXT_DOMAIN ),
            'post_submit_meta_box',
            'wprss_feed',
            'side',
            'high'
        );
    });

    add_action( 'add_meta_boxes', 'wprss_add_meta_boxes', 99);
    /**
     * Set up the input boxes for the wprss_feed post type
     *
     * @since 2.0
     */
    function wprss_add_meta_boxes() {
        global $wprss_meta_fields;

        add_meta_box(
            'preview_meta_box',
            __( 'Feed Preview', WPRSS_TEXT_DOMAIN ),
            'wprss_preview_meta_box_callback',
            'wprss_feed',
            'side',
            'high'
        );

         add_meta_box(
            'wprss-feed-processing-meta',
            __( 'Feed Processing', WPRSS_TEXT_DOMAIN ),
            'wprss_feed_processing_meta_box_callback',
            'wprss_feed',
            'side',
            'high'
        );

        if ( !defined('WPRSS_FTP_VERSION') && !defined('WPRSS_ET_VERSION') && !defined('WPRSS_C_VERSION') ) {
            add_meta_box(
                'wprss-like-meta',
                __( 'Share The Love', WPRSS_TEXT_DOMAIN ),
                'wprss_like_meta_box_callback',
                'wprss_feed',
                'side',
                'low'
            );
        }

        add_meta_box(
            'custom_meta_box',
            __( 'Feed Source Details', WPRSS_TEXT_DOMAIN ),
            'wprss_show_meta_box_callback',
            'wprss_feed',
            'normal',
            'high'
        );

    }


    /**
     * Removes some other plugin's metaboxes because they're not relevant to the wprss_feed post type.
     *
     * @since 4.7
     */
    function wprss_remove_unrelated_meta_boxes() {
        $post_type = 'wprss_feed';
        remove_meta_box( 'wpseo_meta', $post_type, 'normal');                 // WP SEO Yoast
        remove_meta_box( 'ta-reviews-post-meta-box', $post_type, 'normal');   // Author hReview
        remove_meta_box( 'wpdf_editor_section', $post_type, 'advanced');      // ImageInject
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
            'label'			=> __( 'URL', WPRSS_TEXT_DOMAIN ),
            'id'			=> $prefix .'url',
            'type'			=> 'url',
            'after'			=> 'wprss_after_url',
			'placeholder'	=>	'https://'
        );

        $wprss_meta_fields[ 'limit' ] = array(
            'label' => __( 'Limit', WPRSS_TEXT_DOMAIN ),
            'id'    => $prefix . 'limit',
            'type'  => 'number'
        );

        $wprss_meta_fields[ 'unique_titles' ] = array(
            'label' => __( 'Unique titles only', WPRSS_TEXT_DOMAIN ),
            'id'    => $prefix . 'unique_titles',
            'type'  => 'select',
            'options' => [
                ['value' => '', 'label' => 'Default'],
                ['value' => '1', 'label' => 'Yes'],
                ['value' => '0', 'label' => 'No'],
            ]
        );

        $wprss_meta_fields[ 'enclosure' ] = array(
            'label' => __( 'Link to enclosure', WPRSS_TEXT_DOMAIN ),
            'id'    => $prefix . 'enclosure',
            'type'  => 'checkbox'
        );

        if (wprss_is_et_active()) {
            $wprss_meta_fields[ 'source_link' ] = array(
                'label' => __( 'Link source', WPRSS_TEXT_DOMAIN ),
                'id'    => $prefix . 'source_link',
                'type'  => 'boolean_fallback'
            );
        }

        $wprss_meta_fields[ 'import_source' ] = array(
            'label'   => __( 'Use source info', WPRSS_TEXT_DOMAIN ),
            'id'      => $prefix . 'import_source',
            'type'    => 'checkbox',
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
		$field_tooltip_id_prefix = 'field_';
		$help = WPRSS_Help::get_instance();

        // Use nonce for verification
        wp_nonce_field( 'wpra_feed_source', 'wprss_meta_box_nonce' );

            // Fix for WordpRess SEO JS issue
            ?><input type="hidden" id="content" value="" /><?php

            // Begin the field table and loop
            ?><table class="form-table wprss-form-table"><?php

            foreach ( $meta_fields as $field ) {
                // get value of this field if it exists for this post
                $meta = get_post_meta( $post->ID, $field['id'], true );
                // begin a table row with
                ?><tr>
                        <th><label for="<?php echo $field['id'] ?>"><?php echo $field['label'] /* Should be already translated */ ?></label></th>
                        <td><?php

                        if ( isset( $field['before'] ) && !empty( $field['before'] ) ) {
                            call_user_func( $field['before'] );
                        }

						// Add default placeholder value
						$field = wp_parse_args( $field, array(
                            'desc'          => '',
                            'placeholder'   => '',
                            'type'          => 'text'
                        ) );

						$tooltip = isset( $field['tooltip'] ) ? trim( $field['tooltip'] ) : null;
						$tooltip_id = isset( $field['id'] ) ? $field_tooltip_id_prefix . $field['id'] : uniqid( $field_tooltip_id_prefix );

						$field_description = __( $field['desc'], WPRSS_TEXT_DOMAIN );

						/*
						 * So, here's how tooltips work here.
						 * Tooltip output will be attempted in any case.
						 * If 'tooltip' index is not defined, or is null, then
						 * a registered tooltip will be attempted. If that is
						 * not found, default value will be output. This value
						 * is by default an empty string, but can be altered
						 * by the `tooltip_not_found_handle_html` option of `WPRSS_Help`.
						 */

                        switch( $field['type'] ) {

                            // text/url
                            case 'url':
                            case 'text':
                                ?><input type="<?php echo $field['type'] ?>" name="<?php echo $field['id'] ?>" id="<?php echo $field['id'] ?>" value="<?php echo esc_attr( $meta ) ?>" placeholder="<?php _e( $field['placeholder'], WPRSS_TEXT_DOMAIN ) ?>" class="wprss-text-input"/><?php
								echo $help->tooltip( $tooltip_id, $tooltip );
                                if ( strlen( trim( $field['desc'] ) ) > 0 ) {
                                    ?><br /><label for="<?php echo $field['id'] ?>"><span class="description"><?php _e( $field['desc'], WPRSS_TEXT_DOMAIN ) ?></span></label><?php
                                }
                            break;

                            // textarea
                            case 'textarea':
                                ?><textarea name="<?php echo $field['id'] ?>" id="<?php echo $field['id'] ?>" cols="60" rows="4"><?php echo esc_attr( $meta ) ?></textarea><?php
                                echo $help->tooltip( $tooltip_id, $tooltip );
                                if ( strlen( trim( $field['desc'] ) ) > 0 ) {
                                    ?><br /><label for="<?php echo $field['id'] ?>"><span class="description"><?php echo $field_description ?></span></label><?php
                                }
                            break;

                            // checkbox
                            case 'checkbox':
                                ?>
								<input type="hidden" name="<?php echo $field['id'] ?>" value="false" />
                                <input type="checkbox" name="<?php echo $field['id'] ?>" id="<?php echo $field['id'] ?>" value="true" <?php checked( $meta, 'true' ) ?> />
                                <?php
                                echo $help->tooltip( $tooltip_id, $tooltip );
                                if ( strlen( trim( $field['desc'] ) ) > 0 ) {
                                    ?><label for="<?php echo $field['id'] ?>"><span class="description"><?php echo $field_description ?></span></label><?php
                                }
                            break;

                            // improved checkbox
                            case 'checkbox2':
                                ?>
                                <input type="hidden" name="<?php echo $field['id'] ?>" value="0" />
                                <input type="checkbox"
                                       id="<?php echo $field['id'] ?>"
                                       name="<?php echo $field['id'] ?>"
                                       value="1"
                                        <?php checked( $meta, '1' ) ?>
                                />
                                <?php
                                echo $help->tooltip( $tooltip_id, $tooltip );
                                if ( strlen( trim( $field['desc'] ) ) > 0 ) {
                                    ?><label for="<?php echo $field['id'] ?>"><span class="description"><?php echo $field_description ?></span></label><?php
                                }
                                break;

                            // select
                            case 'select':
								?><select name="<?php echo $field['id'] ?>" id="<?php $field['id'] ?>"><?php
                                foreach ($field['options'] as $option) {
                                    ?><option<?php if ( $meta == $option['value'] ): ?> selected="selected"<?php endif ?> value="<?php echo $option['value'] ?>"><?php echo $option['label'] ?></option><?php
                                }

                                ?></select><?php
								echo $help->tooltip( $tooltip_id, $tooltip );
								if ( strlen( trim( $field['desc'] ) ) > 0 ) {
                                    ?><label for="<?php echo $field['id'] ?>"><span class="description"><?php echo $field_description ?></span></label><?php
                                }
                            break;

                            // A select with "On" and "Off" values, and a special option to fall back to General setting
                            case 'boolean_fallback':
                                $options = wprss_settings_get_feed_source_boolean_options();
                                if ($meta === '') {
                                    $meta = -1;
                                }
                                echo wprss_settings_render_select($field['id'], $field['id'], $options, $meta);
								echo $help->tooltip( $tooltip_id, $tooltip );
                            break;

                            // number
                            case 'number':
                                ?><input class="wprss-number-roller" type="number" placeholder="<?php _e( 'Default', WPRSS_TEXT_DOMAIN ) ?>" min="0" name="<?php echo $field['id'] ?>" id="<?php echo $field['id'] ?>" value="<?php echo esc_attr( $meta ) ?>" /><?php
								echo $help->tooltip( $tooltip_id, $tooltip );
                                if ( strlen( trim( $field['desc'] ) ) > 0 ) {
                                    ?><label for="<?php echo $field['id'] ?>"><span class="description"><?php echo $field_description ?></span></label><?php
                                }
                            break;

                        } //end switch

                        if ( isset( $field['after'] ) && !empty( $field['after'] ) ) {
                            call_user_func( $field['after'] );
                        }

                ?></td></tr><?php
            } // end foreach
            ?></table><?php
    }


    /**
     * Renders content after the URL field
     *
     * @since 3.9.5
     */
    function wprss_after_url() {
        ?>
            <i id="wprss-url-spinner" class="fa fa-fw fa-refresh fa-spin wprss-updating-feed-icon" title="<?php _e( 'Updating feed source', WPRSS_TEXT_DOMAIN ) ?>"></i>
            <div id="wprss-url-error" style="color:red"></div>
            <a href="#" id="validate-feed-link" class="wprss-after-url-link">Validate feed</a>
            <span> | </span>
            <a href="https://kb.wprssaggregator.com/article/55-how-to-find-an-rss-feed"
               class="wprss-after-url-link"
                target="_blank">
                <?= __('How to find an RSS feed', 'wprss') ?>
            </a>
            <script type="text/javascript">
                (function($){
                    // When the DOM is ready
                    $(document).ready( function(){
                        // Move the link immediately after the url text field, and add the click event handler
                        $('#validate-feed-link').click(function(e){
                            // Get the url and proceed only if the url is not empty
                            var url = $('#wprss_url').val();
                            if ( url.trim().length > 0 ) {
                                // Encode the url and generate the full url to the w3 feed validator
                                var encodedUrl = encodeURIComponent( url );
                                var fullURL = 'https://validator.w3.org/feed/check.cgi?url=' + encodedUrl;
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
        if ( !isset( $_POST['wprss_meta_box_nonce'] ) ||
             !wp_verify_nonce( $_POST['wprss_meta_box_nonce'], 'wpra_feed_source' ) ) {
            return $post_id;
        }

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

        $postType = class_exists('WPRSS_FTP_Meta')
            ? WPRSS_FTP_Meta::get_instance()->get($post_id, 'post_type')
            : 'wprss_feed_item';

        if ($postType === 'wprss_feed_item' && isset($_POST['wpra_feed_def_ft_image']) ) {
            $def_ft_image_id = $_POST['wpra_feed_def_ft_image'];

            if (empty($def_ft_image_id)) {
                // Does not actually delete the image
                delete_post_thumbnail( $post_id );
            } else {
                set_post_thumbnail( $post_id, $def_ft_image_id );
            }
        }

        // Change the limit, if it is zero, to an empty string
        if ( isset( $_POST['wprss_limit'] ) && strval( $_POST['wprss_limit'] ) == '0' ) {
            $_POST['wprss_limit'] = '';
        }

        // loop through fields and save the data
        foreach ( $meta_fields as $field ) {
            $old = get_post_meta( $post_id, $field[ 'id' ], true );
            $new = trim( $_POST[ $field[ 'id' ] ] );
            if ( $new !== $old || empty($old) ) {
                update_post_meta( $post_id, $field[ 'id' ], $new );
            } elseif ( empty($new) && !empty($old) ) {
                delete_post_meta( $post_id, $field[ 'id' ], $old );
            }
        } // end foreach

        $force_feed = ( isset( $_POST['wprss_force_feed'] ) )? $_POST['wprss_force_feed'] : 'false';
        $state = ( isset( $_POST['wprss_state'] ) )? $_POST['wprss_state'] : 'active';
        $activate = ( isset( $_POST['wprss_activate_feed'] ) )? stripslashes( $_POST['wprss_activate_feed'] ) : '';
        $pause = ( isset( $_POST['wprss_pause_feed'] ) )? stripslashes( $_POST['wprss_pause_feed'] ) : '';
        $age_limit = ( isset( $_POST['wprss_age_limit'] ) )? stripslashes( $_POST['wprss_age_limit'] ) : '';
        $age_unit = ( isset( $_POST['wprss_age_unit'] ) )? stripslashes( $_POST['wprss_age_unit'] ) : '';
        $update_interval = ( isset( $_POST['wprss_update_interval'] ) )? stripslashes( $_POST['wprss_update_interval'] ) : wprss_get_default_feed_source_update_interval();
        $old_update_interval = get_post_meta( $post_id, 'wprss_update_interval', TRUE );

        // Update the feed source meta
        update_post_meta( $post_id, 'wprss_force_feed', $force_feed );
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

		$help = WPRSS_Help::get_instance();
		/* @var $help WPRSS_Help */

        echo '<div id="feed-preview-container">';

        if ( ! empty( $feed_url ) ) {
            $feed = wprss_fetch_feed( $feed_url, $post->ID );
            if ( ! is_wp_error( $feed ) ) {
                ob_start();
                // Figure out how many total items there are
                $total = @$feed->get_item_quantity();
                // Get the number of items again, but limit it to 5.
                $maxitems = $feed->get_item_quantity(5);

                // Build an array of all the items, starting with element 0 (first element).
                $items = $feed->get_items( 0, $maxitems );
                ob_clean();
                ?>
				<h4><?php echo sprintf( __( 'Latest %1$s feed items out of %2$s available from %3$s' ), $maxitems, $total, get_the_title() ) ?></h4>
                <ul>
				<?php
                foreach ( $items as $item ) {
                    $date = $item->get_date( 'U' );
                    $has_date = $date ? true : false;

                    // Get human date
                    if ( $has_date ) {
                        $item_date = human_time_diff( $date, current_time('timestamp')).' '.__( 'ago', 'wprss' );
                    } else {
                        $item_date = '<em>[' . __( 'No Date', WPRSS_TEXT_DOMAIN ) . ']</em>';
                    }

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
                ?>
				</ul>
				<?php
            }
            else {
                ?>
                <span class="invalid-feed-url">
                    <?php _e( '<strong>Invalid feed URL</strong> - Double check the feed source URL setting above.', WPRSS_TEXT_DOMAIN ) ?>
                    <?php wprss_log_obj( 'Failed to preview feed.', $feed->get_error_message(), NULL, WPRSS_LOG_LEVEL_INFO ); ?>
                </span>
				<?php
				echo wpautop(
				        sprintf(
				                __('Not sure where to find the RSS feed on a website? <a target="_blank" href="%1$s">Click here</a> for a visual guide.', 'wprss'),
                                'https://kb.wprssaggregator.com/article/55-how-to-find-an-rss-feed'
                        )
                );
            }
            
        }
        else {
            echo '<p>' . __( 'No feed URL defined yet', WPRSS_TEXT_DOMAIN ) . '</p>';
        }
        echo '</div>';

        echo '<div id="force-feed-container">';
        wprss_render_force_feed_option( $post->ID, TRUE );
        echo '</div>';
    }


    /**
     * Renders the Force Feed option for the Feed Preview.
     *
     * @param int|string $feed_source_id (Optional) The ID of the feed source for the option will be rendered. If not given or
     *                                   its value is null, the option will not be checked.
     * @param bool       $echo           (Optional) If set to true, the function will immediately echo the option,
     *                                   rather than return a string of the option's markup. Default: False.
     * @return string|null               A string containing the HTML for the rendered option if $echo is set to false,
     *                                   or null if $echo is set to true.
     * @since 4.6.12
     */
    function wprss_render_force_feed_option( $feed_source_id = NULL, $echo = FALSE ) {
        if ( ! $echo ) ob_start();
        $force_feed = $feed_source_id === NULL ? '' : get_post_meta( $feed_source_id, 'wprss_force_feed', TRUE ); ?>
        <p>
            <label for="wprss-force-feed"><?php _e('Force the feed') ?></label>
            <input type="hidden" name="wprss_force_feed" value="false" />
            <input type="checkbox" name="wprss_force_feed" id="wprss-force-feed" value="true" <?php echo checked( $force_feed, 'true' ); ?> />
            <?php echo WPRSS_Help::get_instance()->tooltip( 'field_wprss_force_feed' ) ?>
        </p>
        <?php
        if ( ! $echo ) return ob_get_clean();
        return NULL;
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
        $update_time = get_post_meta( $post->ID, 'wprss_update_time', TRUE );

        $age_limit = get_post_meta( $post->ID, 'wprss_age_limit', true );
        $age_unit = get_post_meta( $post->ID, 'wprss_age_unit', true );

        // Set default strings for activate and pause times
        $default_activate = 'immediately';
        $default_pause = 'never';

        // Prepare the states
        $states = array(
            'active'    =>  __( 'Active', WPRSS_TEXT_DOMAIN ),
            'paused'    =>  __( 'Paused', WPRSS_TEXT_DOMAIN ),
        );

        // Prepare the schedules
        $default_interval = __( 'Default', WPRSS_TEXT_DOMAIN );
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

		// Inline help
		$help = WPRSS_Help::get_instance();
		$help_options = array('tooltip_handle_class_extra' => $help->get_options('tooltip_handle_class_extra') . ' ' . $help->get_options('tooltip_handle_class') . '-side');

        ?>

        <div class="wprss-meta-side-setting">
            <label for="wprss_state">Feed state:</label>
            <select id="wprss_state" name="wprss_state">
                <?php foreach( $states as $value => $label ) : ?>
                    <option value="<?php echo $value; ?>" <?php selected( $state, $value ) ?> ><?php echo $label; ?></option>
                <?php endforeach; ?>
            </select>
			<?php echo $help->tooltip( 'field_wprss_state', null, $help_options ) ?>
        </div>

        <div class="wprss-meta-side-setting">
            <p>
                <label for="">Activate feed: </label>
                <strong id="wprss-activate-feed-viewer"><?php echo ( ( $activate !== '' )? $activate : $default_activate ); ?></strong>
                <a href="#">Edit</a>
				<?php echo $help->tooltip( 'field_wprss_activate_feed', null, $help_options ) ?>
            </p>
            <div class="wprss-meta-slider" data-collapse-viewer="wprss-activate-feed-viewer" data-default-value="<?php echo $default_activate; ?>">
                <input id="wprss_activate_feed" class="wprss-datetimepicker-from-today" name="wprss_activate_feed" value="<?php echo $activate; ?>" />
                <span class="description">
                    Current UTC time is:<br/><code><?php echo date( 'd/m/Y H:i:s', current_time('timestamp',1) ); ?></code>
                </span>
            </div>
        </div>

        <div class="wprss-meta-side-setting">
            <p>
                <label for="">Pause feed: </label>
                <strong id="wprss-pause-feed-viewer"><?php echo ( ( $pause !== '' )? $pause : $default_pause ); ?></strong>
                <a href="#">Edit</a>
				<?php echo $help->tooltip( 'field_wprss_pause_feed', null, $help_options ) ?>
            </p>
            <div class="wprss-meta-slider" data-collapse-viewer="wprss-pause-feed-viewer" data-default-value="<?php echo $default_pause; ?>">
                <input id="wprss_pause_feed" class="wprss-datetimepicker-from-today" name="wprss_pause_feed" value="<?php echo $pause; ?>" />
                <span class="description">
                    Current UTC time is:<br/><code><?php echo date( 'd/m/Y H:i:s', current_time('timestamp',1) ); ?></code>
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
				<?php echo $help->tooltip( 'field_wprss_update_interval', null, $help_options ) ?>
            </p>
            <div class="wprss-meta-slider" data-collapse-viewer="wprss-feed-update-interval-viewer" data-default-value="<?php echo $default_interval; ?>">
                <select id="feed-update-interval" name="wprss_update_interval">
                <?php foreach ( $schedules as $value => $schedule ) : ?>
                    <?php $text = ( $value === wprss_get_default_feed_source_update_interval() )? $default_interval : wprss_interval( $schedule['interval'] ); ?>
                    <option value="<?php echo $value; ?>" <?php selected( $update_interval, $value ); ?> ><?php echo $text; ?></option>
                <?php endforeach; ?>
                </select>
                <label>
                    <input type="time" name="wpra_feed[update_time]" value="<?php echo esc_attr($update_time); ?>">
                </label>
            </div>
        </div>


        <div class="wprss-meta-side-setting">
            <p>
                <label id="wprss-age-limit-feed-label" for="" data-when-empty="Limit items by age:">
                    <?php _e( 'Limit items by age:', 'wprss' ); ?>
                </label>
                <strong id="wprss-age-limit-feed-viewer">
                    <?php _e( 'Default', WPRSS_TEXT_DOMAIN ); ?>
                </strong>
                <a href="#">Edit</a>
				<?php echo $help->tooltip( 'field_wprss_age_limit', null, $help_options ) ?>
            </p>
            <div class="wprss-meta-slider" data-collapse-viewer="wprss-age-limit-feed-viewer" data-label="#wprss-age-limit-feed-label" data-default-value="" data-empty-controller="#limit-feed-items-age" data-hybrid="#limit-feed-items-age, #limit-feed-items-age-unit">
                <input id="limit-feed-items-age" name="wprss_age_limit" type="number" min="0" class="wprss-number-roller" placeholder="No limit" value="<?php echo $age_limit; ?>" />

                <select id="limit-feed-items-age-unit" name="wprss_age_unit">
                <?php foreach ( wprss_age_limit_units() as $unit ) : ?>
                    <option value="<?php echo $unit; ?>" <?php selected( $age_unit, $unit ); ?> ><?php echo $unit; ?></option>
                <?php endforeach; ?>
                </select>
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
       echo '<p><a href="https://www.wprssaggregator.com/documentation/">View the documentation</p>';
       echo '<p><strong>';
       _e( 'Need help?', WPRSS_TEXT_DOMAIN );
       echo '</strong> <a target="_blank" href="https://wordpress.org/support/plugin/wp-rss-aggregator">';
       _e( 'Check out the support forum', WPRSS_TEXT_DOMAIN );
       echo '</a></p>';
       echo '</strong> <a target="_blank" href="https://www.wprssaggregator.com/feature-requests/">';
       _e( 'Suggest a new feature', WPRSS_TEXT_DOMAIN );
       echo '</a></p>';
    }

    /**
     * Generate Like this plugin meta box
     *
     * @since 2.0
     *
     */
    function wprss_like_meta_box_callback()
    {
        ?>
        <ul>
            <li><a href="https://wordpress.org/support/view/plugin-reviews/wp-rss-aggregator?rate=5#postform" target="_blank"><?php _e( 'Give it a 5 star rating on WordPress.org', WPRSS_TEXT_DOMAIN ) ?></a></li>
        </ul>
        <?php
        do_action('wpra_share_the_love_metabox');
    }


    /**
     * Generate Follow us plugin meta box
     *
     * @since 2.0
     *
     */
    function wprss_follow_meta_box_callback() {
        ?>
        <ul>
            <li class="twitter"><a href="https://twitter.com/wpmayor"><?php _e( 'Follow WP Mayor on Twitter.', WPRSS_TEXT_DOMAIN ) ?></a></li>
            <li class="facebook"><a href="https://www.facebook.com/wpmayor"><?php _e( 'Like WP Mayor on Facebook.', WPRSS_TEXT_DOMAIN ) ?></a></li>
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
        //remove_meta_box( 'wpseo_meta', 'wprss_feed' ,'normal' );
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
