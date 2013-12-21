<?php  
    /**
     * Plugin settings related functions 
     * 
     * Note: Wording of options and settings is confusing, due to the plugin originally only having 
     * an 'options' page to enter feed sources, and now needing two screens, one for feed sources and one for 
     * general settings. Might implement something cleaner in the future.
     *
     * @package WP PRSS Aggregator
     */ 



    /**
     * Returns the given general setting option value form the database, or the default value if it is not found.
     * 
     * @param option_name The name of the option to get
     * @return mixed
     * @since 3.7.1
     */
    function wprss_get_general_setting( $option_name ) {
        $options = get_option( 'wprss_settings_general', array() );
        $defaults = wprss_get_default_settings_general();
        return ( ( isset( $options[ $option_name ] ) )? $options[$option_name] : $defaults[$option_name] );
    }


    add_action( 'admin_init', 'wprss_admin_init' );
    /**
     * Register and define options and settings
     * @since 2.0
     * @todo add option for cron frequency
     *
     * Note: In the future might change to 
     * the way EDD builds the settings pages, cleaner method. 
     */ 
    function wprss_admin_init() {

        register_setting( 
            'wprss_settings_general',                       // A settings group name.
            'wprss_settings_general',                       // The name of an option to sanitize and save.
            'wprss_settings_general_validate'               // A callback function that sanitizes the option's value.
        );       

        // Licensing of add-ons
        register_setting( 
            'wprss_settings_license_keys',                       
            'wprss_settings_license_keys',                        
            'wprss_settings_license_keys_validate' 
        );                   
        

        $sections = apply_filters(
            'wprss_settings_sections_array',
            array(
                'general'  =>  __( 'General plugin settings', 'wprss' ),
                'display'  =>  __( 'Display settings', 'wprss' ),
                'styles'   =>  __( 'Styles', 'wprss' ),
            )
        );

        // Define the settings per section
        $settings = apply_filters(
            'wprss_settings_array',
            array(
                'general'   =>  array(
                    'limit-feed-items-by-age' => array(
                        'label'     =>  __( 'Limit feed items by age', 'wprss' ),
                        'callback'  =>  'wprss_setting_limit_feed_items_age_callback'
                    ),
                    'limit-feed-items-db' => array(
                        'label'     => __( 'Limit feed items stored', 'wprss' ),
                        'callback'  => 'wprss_setting_limit_feed_items_callback'
                    ),
                    'limit-feed-items-imported' => array(
                        'label'     => __( 'Limit feed items per feed', 'wprss' ), 
                        'callback'  => 'wprss_setting_limit_feed_items_imported_callback'
                    ),
                    'cron-interval' => array(
                        'label'     =>  __( 'Feed processing interval', 'wprss' ),
                        'callback'  =>  'wprss_setting_cron_interval_callback'
                    ),
                    'custom-feed-url' => array(
                        'label'     =>  __( 'Custom feed URL', 'wprss' ),
                        'callback'  =>  'wprss_setings_custom_feed_url_callback'
                    ),
                    'custom-feed-limit' => array(
                        'label'     =>  __( 'Custom feed limit', 'wprss' ),
                        'callback'  =>  'wprss_setings_custom_feed_limit_callback'
                    ),
                    'tracking'  =>  array(
                        'label'     =>  __( 'Anonymous tracking', 'wprss' ),
                        'callback'  =>  'wprss_tracking_callback',
                    )
                ),

                'display'   =>  array(
                    'source-enable' => array(
                        'label'     =>  __( 'Show source', 'wprss' ),
                        'callback'  =>  'wprss_setting_source_enable_callback'
                    ),
                    'text-preceding-source' => array(
                        'label'     =>  __( 'Text preceding source', 'wprss' ),
                        'callback'  =>  'wprss_setting_text_preceding_source_callback'
                    ),                      
                    'source-link' => array(
                        'label'     =>  __( 'Link source', 'wprss' ),
                        'callback'  =>  'wprss_setting_source_link_callback'
                    ),
                    'open-dd' => array(
                        'label'     =>  __( 'Source link open behaviour', 'wprss' ),
                        'callback'  =>  'wprss_setting_open_dd_callback'
                    ),

                    'link-enable' => array(
                        'label'     =>  __( 'Link title', 'wprss' ),
                        'callback'  =>  'wprss_setting_title_link_callback'
                    ),      
                    'title-limit' => array(
                        'label'     =>  __( 'Title maximum length', 'wprss' ),
                        'callback'  =>  'wprss_setting_title_length_callback'
                    ),
                    'video-links' => array(
                        'label'     =>  __( 'For video feed items use', 'wprss' ),
                        'callback'  =>  'wprss_setting_video_links_callback'
                    ),                                  
                    'follow-dd' => array(
                        'label'     =>  __( 'Set links as nofollow', 'wprss' ),
                        'callback'  =>  'wprss_setting_follow_dd_callback'
                    ),
                    'date-enable' => array(
                        'label'     =>  __( 'Show date', 'wprss' ),
                        'callback'  =>  'wprss_setting_date_enable_callback'
                    ),
                    'date-format' => array(
                        'label'     =>  __( 'Date format', 'wprss' ),
                        'callback'  =>  'wprss_setting_date_format_callback'
                    ),
                    'text-preceding-date' => array(
                        'label'     =>  __( 'Text preceding date', 'wprss' ),
                        'callback'  =>  'wprss_setting_text_preceding_date_callback'
                    ),                    

                    'feed-limit' => array(
                        'label'     =>  __( 'Feed display limit', 'wprss' ),
                        'callback'  =>  'wprss_setting_feed_limit_callback'
                    ),
                ),

                'styles'    =>  array(
                    'styles-disable' => array(
                        'label'     =>  __( 'Disable Styles', 'wprss' ),
                        'callback'  =>  'wprss_setting_styles_disable_callback'
                    )
                )
            )
        );
        
        if ( apply_filters( 'wprss_use_fixed_feed_limit', FALSE ) === FALSE ) {
            unset( $settings['general']['limit-feed-items-db'] );
        }


        // Loop through each setting field and register it
        foreach( $settings as $section => $fields ) {
            if ( count( $fields ) > 0 ) {
                $section_desc = $sections[ $section ];
                add_settings_section( 
                    "wprss_settings_${section}_section",
                    $section_desc,
                    "wprss_settings_${section}_callback",
                    'wprss_settings_general'
                );

                foreach ( $fields as $id => $data ) {

                    add_settings_field(
                        'wprss-settings-' . $id,
                        $data['label'],
                        $data['callback'],
                        'wprss_settings_general',
                        "wprss_settings_${section}_section"
                    );

                }
            }
        }


        /*
        // SECURE RESET OPTION
        register_setting( 
            'wprss_secure_reset',                           // A settings group name.
            'wprss_secure_reset_code',                      // The name of an option to sanitize and save.
            ''                                              // A callback function that sanitizes the option's value.
        );
        add_settings_section( 
            'wprss_secure_reset_section',                   // ID of section
            __( 'Secure Reset', 'wprss' ),                  // Title of section
            'wprss_secure_reset_section_callback',          // Callback that renders the section header
            'wprss_settings_general'                        // The page on which to display the section
        );
        add_settings_field(
            'wprss-settings-secure-reset',                  // ID of setting
            __( 'Secure Reset', 'wprss' ),                  // The title of the setting
            'wprss_settings_secure_reset_code_callback',    // The callback that renders the setting
            'wprss_settings_general',                       // The page on which to display the setting
            "wprss_secure_reset_section"                    // The section in which to display the setting
        );
        */



        do_action( 'wprss_admin_init' );
    }  


    /**
     * Build the plugin settings page, used to save general settings like whether a link should be follow or no follow
     * @since 1.1
     */ 
    function wprss_settings_page_display() {
        ?>
        <div class="wrap">
            <?php screen_icon( 'wprss-aggregator' ); ?>            
        
            <h2><?php _e( 'WP RSS Aggregator Settings', 'wprss' ); ?></h2>   

            <?php settings_errors(); ?> 

            <?php $active_tab = isset( $_GET['tab'] ) ? $_GET['tab'] : 'general_settings'; ?>

            <?php

            $default_tabs = array(
				'general' => array( 
					'label' => __( 'General', 'wprss' ),
					'slug'  => 'general_settings',
				),
				'licenses' => array(
					'label' => __( 'Licenses', 'wprss' ),
					'slug'  => 'licenses_settings'
				)
            );

            $addon_tabs = apply_filters( 'wprss_options_tabs', array() );

            $tabs = array_merge( array( $default_tabs['general'] ), $addon_tabs , array( $default_tabs['licenses'] ) );

            $show_tabs = ( count( $addon_tabs ) > 0 ) || apply_filters( 'wprss_show_settings_tabs_condition', FALSE );

            if ( $show_tabs ) { ?>
            <h2 class="nav-tab-wrapper">
                <?php 
                foreach ( $tabs as $tab => $tab_property ) { ?>
                    <a href="?post_type=wprss_feed&page=wprss-aggregator-settings&tab=<?php echo esc_attr( $tab_property['slug'] ); ?>"
                        class="nav-tab <?php echo $active_tab == $tab_property['slug']  ? 'nav-tab-active' : ''; ?>"><?php echo esc_html( $tab_property['label'] ); ?></a>
                <?php } ?>
            <?php } ?>                
            </h2>            

            <form action="options.php" method="post">   
            
                <?php

                if ( $active_tab === 'general_settings' ) {
                    settings_fields( 'wprss_settings_general' ); 
                    //settings_fields( 'wprss_secure_reset' );
                    do_settings_sections( 'wprss_settings_general' );
                }
                elseif ( $show_tabs ) {

                    if ( $active_tab === 'licenses_settings' ) {
                        settings_fields( 'wprss_settings_license_keys' );
                        do_settings_sections( 'wprss_settings_license_keys' );
                    }
                    
                    do_action( 'wprss_add_settings_fields_sections', $active_tab );
                }

                submit_button( __( 'Save Settings', 'wprss' ) );

                ?>                                  
            </form>
        </div>
        <?php
    }


    /** 
     * General settings section header
     * @since 3.0
     */
    function wprss_settings_general_callback() {
        echo '<p>' . __( 'These are the general settings for WP RSS Aggregator.', 'wprss' ) . '</p>';
    }


    /** 
     * General settings display section header
     * @since 3.5
     */
    function wprss_settings_display_callback() {
        echo '<p>' . __( 'In this section you can find the options that control how the feed items are displayed.', 'wprss' ) . '</p>';
    }


    /** 
     * General settings styles section header
     * @since 3.0
     */
    function wprss_settings_styles_callback() {
        echo '<p>' . __( 'If you would like to disable all styles used in this plugin, tick the checkbox.', 'wprss' ) . '</p>';
    }


    /** 
     * General settings scure reset section header
     * @since 3.0
     */
    function wprss_secure_reset_section_callback() {
        echo '<p>' . __( 'Set your security reset code, in case of any errors.', 'wprss' ) . '</p>';
    }


    /** 
     * Tracking settings section header
     * @since 3.0
     */
    function wprss_tracking_section_callback() {
        echo '<p>' . __( 'Participate in helping us make the plugin better.', 'wprss' ) . '</p>';
    }


    /** 
     * Follow or No Follow dropdown
     * @since 1.1
     */
    function wprss_setting_follow_dd_callback() {
        $follow_dd = wprss_get_general_setting( 'follow_dd' );

        $checked = ( $follow_dd === 'no_follow' );
        $checked_attr = ( $checked )? 'checked="checked"' : '';

        echo "<input type='hidden' name='wprss_settings_general[follow_dd]' value='follow'>";
        echo "<input type='checkbox' id='follow-dd' name='wprss_settings_general[follow_dd]' value='no_follow' $checked_attr>";

        echo '<label class="description" id="follow-dd">';
        echo    '"Nofollow" provides a way for webmasters to tell search engines "Don\'t follow links on this page" or "Don\'t follow this specific link."';
        echo '</label>';
    }

	
	/**
	 * Use original video link, or embedded video links dropwdown
	 * @since 3.4
	 */
	function wprss_setting_video_links_callback() {
		$video_link = wprss_get_general_setting('video_link');
		$items = array(
			'false' => __( 'Original page link', 'wprss' ),
			'true' => __( 'Embedded video player link', 'wprss' )
		);
		echo "<select id='video-link' name='wprss_settings_general[video_link]'>";
		foreach ( $items as $boolean => $text ) {
			$selected = ( $video_link === $boolean )? 'selected="selected"' : '';
			echo "<option value='$boolean' $selected>$text</option>";
        }
		echo "</select>";
		echo "<label class='description' for='video-link'>This will not affect already imported feed items.</label>";
	}
	

    /** 
     * Link open setting dropdown
     * @since 1.1
     */
    function wprss_setting_open_dd_callback() {
        $open_dd = wprss_get_general_setting('open_dd');

        $items = array( 
            __( 'Lightbox', 'wprss' ), 
            __( 'New window', 'wprss' ), 
            __( 'Self', 'wprss' )
        );
        echo "<select id='open-dd' name='wprss_settings_general[open_dd]'>";
        foreach( $items as $item ) {
            $selected = ( $open_dd == $item ) ? 'selected="selected"' : '';
            echo "<option value='$item' $selected>$item</option>";
        }
        echo "</select>";   
    }


    /** 
     * Set limit for feeds on frontend
     * @since 2.0
     */
    function wprss_setting_feed_limit_callback() {
        $feed_limit = wprss_get_general_setting( 'feed_limit' );
        echo "<input id='feed-limit' name='wprss_settings_general[feed_limit]' type='text' value='$feed_limit' />";   
        echo "<label class='description' for='feed-limit'>Enter the number of feeds to display on the front end</label>";
    }


    /** 
     * Set date format 
     * @since 3.0
     */
    function wprss_setting_date_format_callback() {
        $date_format = wprss_get_general_setting( 'date_format' );
        echo "<input id='date-format' name='wprss_settings_general[date_format]' type='text' value='$date_format' />";   
        echo "<label class='description' for='date-format'>Date formatting, using the <a href='http://codex.wordpress.org/Formatting_Date_and_Time'>PHP date formats</a></label>";
    }



    /** 
     * Enable linked title
     * @since 3.0
     */
    function wprss_setting_title_link_callback( $args ) {
        $title_link = wprss_get_general_setting( 'title_link' );
        echo "<input id='title-link' name='wprss_settings_general[title_link]' type='checkbox' value='1' " . checked( 1, $title_link, false ) . " />";   
        echo "<label class='description' for='title-link'>Check this box to enable linked titles</label>";   
    }



    /** 
     * Set the title length limit
     * @since 3.0
     */
    function wprss_setting_title_length_callback( $args ) {
        $title_limit = wprss_get_general_setting( 'title_limit' );
        echo "<input id='title-limit' name='wprss_settings_general[title_limit]' type='number' class='wprss-number-roller' min='0' value='$title_limit' placeholder='No limit' />";   
        echo "<label class='description' for='title-limit'>Set the maximum number of characters for feed titles.</label>";   
    }


    /** 
     * Enable source
     * @since 3.0
     */
    function wprss_setting_source_enable_callback( $args ) {
        $source_enable = wprss_get_general_setting( 'source_enable' );
        echo "<input id='source-enable' name='wprss_settings_general[source_enable]' type='checkbox' value='1' " . checked( 1, $source_enable, false ) . " />";   
        echo "<label class='description' for='source-enable'>Check this box to enable feed source display</label>";   
    }

    /** 
     * Enable linked title
     * @since 3.0
     */
    function wprss_setting_source_link_callback( $args ) {
        $source_link = wprss_get_general_setting( 'source_link' );               
        echo "<input id='source-link' name='wprss_settings_general[source_link]' type='checkbox' value='1' " . checked( 1, $source_link, false ) . " />";   
        echo "<label class='description' for='source-link'>Check this box to enable linked sources</label>";   
    }


    /** 
     * Set text preceding source
     * @since 3.0
     */
    function wprss_setting_text_preceding_source_callback() {
        $text_preceding_source = wprss_get_general_setting( 'text_preceding_source' );
        echo "<input id='text-preceding-source' name='wprss_settings_general[text_preceding_source]' type='text' value='$text_preceding_source' />";   
        echo "<label class='description' for='text-preceding-source'>Enter the text you want shown before the feed item's source</label>";
    }
    /** 
     * Enable date
     * @since 3.0
     */
    function wprss_setting_date_enable_callback( $args ) {
        $date_enable = wprss_get_general_setting( 'date_enable' );
        echo "<input id='date-enable' name='wprss_settings_general[date_enable]' type='checkbox' value='1' " . checked( 1, $date_enable, false ) . " />";
        echo "<label class='description' for='date-enable'>Check this box to enable display of date published</label>";   
    }    

    /** 
     * Set text preceding date
     * @since 3.0
     */
    function wprss_setting_text_preceding_date_callback() {
        $text_preceding_date = wprss_get_general_setting( 'text_preceding_date' );
        echo "<input id='text-preceding-date' name='wprss_settings_general[text_preceding_date]' type='text' value='$text_preceding_date' />";
        echo "<label class='description' for='text-preceding-date'>Enter the text you want shown before the feed item's publish date</label>";
    }



    /** 
     * Limit number of feed items stored by their age
     * @since 3.0
     */
    function wprss_setting_limit_feed_items_age_callback() {
        $limit_feed_items_age = wprss_get_general_setting( 'limit_feed_items_age' );
        $limit_feed_items_age_unit = wprss_get_general_setting( 'limit_feed_items_age_unit' );
        $units = wprss_age_limit_units();
        ?>

        <input id="limit-feed-items-age" name="wprss_settings_general[limit_feed_items_age]" type="number" min="0"
            class="wprss-number-roller" placeholder="No limit" value="<?php echo $limit_feed_items_age; ?>" />

        <select id="limit-feed-items-age-unit" name="wprss_settings_general[limit_feed_items_age_unit]">
        <?php foreach ( $units as $unit ) : ?>
            <option value="<?php echo $unit; ?>" <?php selected( $limit_feed_items_age_unit, $unit ); ?> ><?php echo $unit; ?></option>
        <?php endforeach; ?>
        </select>
        
        <br/>
        <label class='description' for='limit-feed-items-age'>
            Enter the maximum age of feed items to be stored in the database. Feed items older than the specified age will be deleted everyday at midnight.
            <br/>
            Leave empty for no limit.
        </label>

        <?php
    }



    /** 
     * Limit number of feed items stored
     * @since 3.0
     */
    function wprss_setting_limit_feed_items_callback() {
        $limit_feed_items_db = wprss_get_general_setting( 'limit_feed_items_db' );
        echo "<input id='limit-feed-items-db' name='wprss_settings_general[limit_feed_items_db]' type='text' value='$limit_feed_items_db' />";
        echo "<label class='description' for='limit-feed-items-db'>Enter the maximum number of feeds to store in the database; enter 0 for unlimited feed items</label>";
    }


    /** 
     * Limit number of feed items imported per feed
     * @since 3.1
     */
    function wprss_setting_limit_feed_items_imported_callback() {
        $limit_feed_items_imported = wprss_get_general_setting( 'limit_feed_items_imported' );
        echo "<input id='limit-feed-items-imported' name='wprss_settings_general[limit_feed_items_imported]' type='text' value='$limit_feed_items_imported' />";   
        echo "<label class='description' for='limit-feed-items-imported'>Enter the maximum number of feeds to import per feed source; enter 0 for unlimited feed items</label>";
    }


    /**
     * Gets a sorted (according to interval) list of the cron schedules
     * @since 3.0
     */
    function wprss_get_schedules() {
        $schedules = wp_get_schedules();
        uasort( $schedules, create_function( '$a,$b', 'return $a["interval"]-$b["interval"];' ) );
        return $schedules;
    }


    /** 
     * Cron interval dropdown callback
     * @since 3.0
     */
    function wprss_setting_cron_interval_callback() {
        $options = get_option( 'wprss_settings_general' );  
        $current = $options['cron_interval'];

        $schedules = wprss_get_schedules();    
        // Set the allowed Cron schedules, we don't want any intervals that can lead to issues with server load 
        $wprss_schedules = apply_filters( 
                            'wprss_schedules',
                            array( 'fifteen_min', 'thirty_min', 'hourly', 'two_hours', 'twicedaily', 'daily' )
        );        
        echo "<select id='cron-interval' name='wprss_settings_general[cron_interval]'>";
        foreach( $schedules as $schedule_name => $schedule_data ) { 
            if ( in_array( $schedule_name, $wprss_schedules ) ) { ?>
                <option value="<?php echo $schedule_name; ?>" <?php selected( $current, $schedule_name ); ?> >
                    <?php echo $schedule_data['display']; ?> (<?php echo wprss_interval( $schedule_data['interval'] ); ?>)
                </option>
            <?php } ?>
        <?php } ?>
        </select><?php
    }

    /**
     * Sets the custom feed URL
     * @since 3.3
     */
    function wprss_setings_custom_feed_url_callback() {
        $custom_feed_url = wprss_get_general_setting( 'custom_feed_url' );
        echo "<input id='custom_feed_url' name='wprss_settings_general[custom_feed_url]' type='text' value='$custom_feed_url' />";
        echo "<label class='description' for='custom_feed_url'>" . __( 'Custom feed URL', 'wprss' ) . "</label>";
    }

    /**
     * Sets the custom feed limit
     * @since 3.3
     */
    function wprss_setings_custom_feed_limit_callback() {
        $custom_feed_limit = wprss_get_general_setting( 'custom_feed_limit' );
        echo "<input id='custom_feed_limit' name='wprss_settings_general[custom_feed_limit]' placeholder='Default' min='0' class='wprss-number-roller' type='number' value='$custom_feed_limit' />";
        echo "<label class='description' for='custom_feed_limit'>" . __( 'Number of items to show in the custom feed', 'wprss' ) . "</label>";
    }

    /** 
     * Disable styles
     * @since 3.0
     */
    function wprss_setting_styles_disable_callback( $args ) {
        $styles_disable = wprss_get_general_setting( 'styles_disable' );
        echo "<input id='styles-disable' name='wprss_settings_general[styles_disable]' type='checkbox' value='1' " . checked( 1, $styles_disable, false ) . " />";   
        echo "<label class='description' for='styles-disable'>Check this box to disable all plugin styles</label>"; 
        echo "<p class='description'>You will then be responsible for providing your own CSS styles.</p>";  
    }



    /**
     * Secure Reset section
     *
     * @since 3.7.1
     */
    function wprss_settings_secure_reset_code_callback( $args ) {
        $reset_code = get_option( 'wprss_secure_reset_code', '' );
        ?>
        <input id="wprss-secure-reset-code" name="wprss_secure_reset_code" type="input" value="<?php echo $reset_code; ?>" />
        <button type="button" role="button" id="wprss-secure-reset-generate">Generate Random Code</button>

        <br/>

        <label class="description" for="wprss-secure-reset-code">
            Enter the code to use to securely reset the plugin and deactivate it. Be sure to save this code somewhere safe.<br/>
        </label>

        <br/>

        <p>
            Leave this empty to disable the secure reset function.<br/>
            You use this code by adding any of the following to any URL on your site.
            <ol>
                <li>"?wprss_action=reset&wprss_security_code=&lt;your_code&gt;" - <b>Resets your WP RSS Aggregator settings</b></li>
                <li>"?wprss_action=deactivate&wprss_security_code=&lt;your_code&gt;" - <b>Deactivates WP RSS Aggregator</b></li>
                <li>"?wprss_action=reset_and_deactivate&wprss_security_code=&lt;your_code&gt;" - <b>Does both of the above</b></li>
            </ol>
        </p>
        <p class="description">
            Use the above actions only when absolutely necessary, or when instructed to by support staff.
        </p>
        <?php
    }


    /**
     * Tracking checkbox
     * @since 3.6
     */
    function wprss_tracking_callback( $args ) {
        $tracking = wprss_get_general_setting( 'tracking' );
        echo "<input type='checkbox' id='tracking' name='wprss_settings_general[tracking]' value='1' " . checked( 1, $tracking, false ) . " />";
        echo "<label class='description' for='tracking'>Please help us improve WP RSS Aggregator by allowing us to gather anonymous usage statistics.</label>";
        echo "<p class='description'>No sensitive data will be sent.</p>";
    }


    /**
     * Pretty-prints the difference in two times.
     *
     * @since 3.0
     * @param time $older_date
     * @param time $newer_date
     * @return string The pretty time_since value
     * @link http://wordpress.org/extend/plugins/wp-crontrol/
     */
    function wprss_time_since( $older_date, $newer_date ) {
        return wprss_interval( $newer_date - $older_date );
    }    

    /**
     * Calculates difference between times
     * 
     * Taken from the WP-Crontrol plugin 
     * @link http://wordpress.org/extend/plugins/wp-crontrol/
     * @since 3.0
     *
     */
    function wprss_interval( $since ) {
        if ( $since === wprss_get_default_feed_source_update_interval() ) {
            return __( 'Default', 'wprss' );
        }
        // array of time period chunks
        $chunks = array(
            array(60 * 60 * 24 * 365 , _n_noop('%s year', '%s years', 'crontrol')),
            array(60 * 60 * 24 * 30 , _n_noop('%s month', '%s months', 'crontrol')),
            array(60 * 60 * 24 * 7, _n_noop('%s week', '%s weeks', 'crontrol')),
            array(60 * 60 * 24 , _n_noop('%s day', '%s days', 'crontrol')),
            array(60 * 60 , _n_noop('%s hour', '%s hours', 'crontrol')),
            array(60 , _n_noop('%s minute', '%s minutes', 'crontrol')),
            array( 1 , _n_noop('%s second', '%s seconds', 'crontrol')),
        );


        if( $since <= 0 ) {
            return __( 'now', 'wprss' );
        }

        // we only want to output two chunks of time here, eg:
        // x years, xx months
        // x days, xx hours
        // so there's only two bits of calculation below:

        // step one: the first chunk
        for ($i = 0, $j = count($chunks); $i < $j; $i++)
            {
            $seconds = $chunks[$i][0];
            $name = $chunks[$i][1];

            // finding the biggest chunk (if the chunk fits, break)
            if (($count = floor($since / $seconds)) != 0)
                {
                break;
                }
            }

        // set output var
        $output = sprintf(_n($name[0], $name[1], $count, 'wprss'), $count);

        // step two: the second chunk
        if ($i + 1 < $j)
            {
            $seconds2 = $chunks[$i + 1][0];
            $name2 = $chunks[$i + 1][1];

            if (($count2 = floor(($since - ($seconds * $count)) / $seconds2)) != 0)
                {
                // add to output var
                $output .= ' '.sprintf(_n($name2[0], $name2[1], $count2, 'wprss'), $count2);
                }
            }

        return $output;
    }


    /** 
     * Validate inputs from the general settings page
     * @since 3.0
     */
    function wprss_settings_general_validate( $input ) {
        $options = get_option( 'wprss_settings_general' );  
        $current_cron_interval = $options['cron_interval'];

        // Create our array for storing the validated options
        $output = array();
        
        // Loop through each of the incoming options
        foreach( $input as $key => $value ) {
            
            // Check to see if the current option has a value. If so, process it.
            if( isset( $input[ $key ] ) ) {
            
                // Strip all HTML and PHP tags and properly handle quoted strings
                $output[ $key ] = strip_tags( stripslashes( $input[ $key ] ) );
                
            } // end if
            
        } // end foreach

        // If limit_feed_items_age_unit is not set or it set to zero, set it to empty
        if ( ! isset( $input['limit_feed_items_age'] ) || strval( $input['limit_feed_items_age'] ) == '0' ) {
            $output['limit_feed_items_age'] = '';
        }

        if (  ! isset( $input['title_link'] )  ||  $input['title_link'] != '1' ) 
            $output['title_link'] = 0; 
        else 
            $output['title_link'] = 1;  

        if (  ! isset( $input['source_enable'] )  ||  $input['source_enable'] != '1' ) 
            $output['source_enable'] = 0; 
        else 
            $output['source_enable'] = 1;        

        if ( ! isset( $input['date_enable'] ) || $input['date_enable'] != '1' )
            $output['date_enable'] = 0;
        else
            $output['date_enable'] = 1;      

        if ( ! isset( $input['styles_disable'] ) || $input['styles_disable'] != '1' )
            $output['styles_disable'] = 0;
        else
            $output['styles_disable'] = 1;     
        
		if ( ! isset( $input['video_link'] ) || strtolower( $input['video_link'] ) !== 'true' )
			$output['video_link'] = 'false';
		else
			$output['video_link'] = 'true';
		
        if ( $input['cron_interval'] != $current_cron_interval ) {
            wp_clear_scheduled_hook( 'wprss_fetch_all_feeds_hook' );    
            wp_schedule_event( time(), $input['cron_interval'], 'wprss_fetch_all_feeds_hook' );
        }

        // Return the array processing any additional functions filtered by this action
        return apply_filters( 'wprss_settings_general_validate', $output, $input );
    }


    /**
     * Validates the licenses settings
     * 
     * @since 3.8
     */
    function wprss_settings_license_keys_validate( $input ) {
        // Get the current licenses option
        $licenses = get_option( 'wprss_settings_license_keys' );
        // For each entry in the recieved input
        foreach ( $input as $addon => $license_code ) {
            // If the entry does not exist OR the code is different
            if ( !array_key_exists( $addon, $licenses ) || $license_code !== $licenses[ $addon ] ) {
                // Save it to the licenses option
                $licenses[ $addon ] = $license_code;
            }
        }
        wprss_check_license_statuses();
        // Return the new licenses
        return $licenses;
    }



    add_action( 'wprss_check_license_statuses', 'wprss_check_license_statuses' );
    /**
     * Checks the license statuses
     * 
     * @since 3.8.1
     */
    function wprss_check_license_statuses() {
        $license_statuses = get_option( 'wprss_settings_license_statuses', array() );

        if ( count( $license_statuses ) === 0 ) return;

        $found_inactive = FALSE;
        foreach ( $license_statuses as $addon => $status ) {
            if ( $status !== 'active' ) {
                $found_inactive = TRUE;
                break;
            }
        }

        if ( $found_inactive ) {
            set_transient( 'wprss_notify_inactive_licenses', 1, 0 );
        }
    }



    /**
     * Validates the wprss_secure_reset_code option
     * 
     * @since 3.7.1
     */
    function wprss_secure_reset_code_validate( $input ) {
        return $input;
    }



    /**
     * Validates the presstrends setting
     *
     * @since 3.6
     */
    function wprss_tracking_validate ( $input ) {
        $output = $input;
        if ( ! isset( $input['wprss_tracking'] ) ) {
            $output['wprss_tracking'] = 0;
        }
        return $output;
    }



    /**
     * Returns the units used for the limit by age option.
     * 
     * @since 3.8
     */
    function wprss_age_limit_units() {
        return apply_filters(
            'wprss_age_limit_units',
            array(
                'days',
                'weeks',
                'months',
                'years'
            )
        );
    }