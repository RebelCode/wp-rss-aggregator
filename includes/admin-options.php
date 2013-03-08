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
        
        add_settings_section( 
            'wprss_settings_general_section',               // ID used to identify this section and with which to register options 
            __( 'General plugin settings', 'wprss' ),       // Section title that shows within <H3> tags
            'wprss_settings_general_callback',              // Callback function that echoes some explanations
            'wprss_settings_general'                        // Settings page on which to show this section
        );   

        add_settings_section( 
            'wprss_settings_styles_section',               
            __( 'Styles', 'wprss' ),     
            'wprss_settings_styles_callback',            
            'wprss_settings_general'                      
        );          

        add_settings_field( 
            'wprss-settings-open-dd',                       // ID used to identify the field 
            __( 'Open links behaviour', 'wprss' ),          // Text printed next to the field
            'wprss_setting_open_dd_callback',               // Callback function that echoes the field
            'wprss_settings_general',                       // Settings page on which to show this field
            'wprss_settings_general_section'                // Section of the settings page on which to show this field
        );

        add_settings_field( 
            'wprss-settings-follow-dd', 
            __( 'Set links as', 'wprss' ), 
            'wprss_setting_follow_dd_callback', 
            'wprss_settings_general',  
            'wprss_settings_general_section'
        );     

        add_settings_field( 
            'wprss-settings-feed-limit', 
            __( 'Feed limit', 'wprss' ), 
            'wprss_setting_feed_limit_callback', 
            'wprss_settings_general',  
            'wprss_settings_general_section'
        );  

        add_settings_field( 
            'wprss-settings-date-format', 
            __( 'Date format', 'wprss' ), 
            'wprss_setting_date_format_callback', 
            'wprss_settings_general',  
            'wprss_settings_general_section'
        );          

        add_settings_field( 
            'wprss-settings-limit-feed-items', 
            __( 'Limit feed items', 'wprss' ), 
            'wprss_setting_limit_feed_items_callback', 
            'wprss_settings_general',  
            'wprss_settings_general_section'
        );            

        add_settings_field( 
            'wprss-settings-cron-interval', 
            __( 'Cron interval', 'wprss' ), 
            'wprss_setting_cron_interval_callback', 
            'wprss_settings_general',  
            'wprss_settings_general_section'
        );               

        add_settings_field( 
            'wprss-settings-date-enable', 
            __( 'Show date', 'wprss' ), 
            'wprss_setting_date_enable_callback', 
            'wprss_settings_general',  
            'wprss_settings_general_section'
        );   

        add_settings_field( 
            'wprss-settings-source-enable', 
            __( 'Show source', 'wprss' ), 
            'wprss_setting_source_enable_callback', 
            'wprss_settings_general',  
            'wprss_settings_general_section'
        );                           

        add_settings_field( 
            'wprss-settings-styles-disable', 
            __( 'Disable Styles', 'wprss' ), 
            'wprss_setting_styles_disable_callback', 
            'wprss_settings_general',  
            'wprss_settings_styles_section'
        );          

        do_action( 'wprss_admin_init' );
    }  


   /* function wprss_settings_validate( $input ) {
    
        $options = get_option( 'wprss_settings_general' );

        if ( ! isset( $input['excerpt_enable'] ) || $input['excerpt_enable'] != '1' )
            $options['excerpt_enable'] = 0;
        else
            $options['excerpt_enable'] = 1;

        if ( ! isset( $input['thumbnail_enable'] ) || $input['thumbnail_enable'] != '1' )
            $options['thumbnail_enable'] = 0;
        else
            $options['thumbnail_enable'] = 1;
      //  do_action( 'wprss_settings_validate', $input, $options );

      /*  if ( $options['cron_interval'] != $input['cron_inteval'] ) {
            wp_clear_scheduled_hook( 'wprss_fetch_all_feeds_hook' ); 
            wp_schedule_event( time(), $input['cron_interval'], 'wprss_fetch_all_feeds_hook' );   
        }*/
     /*   return $options;
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
            // Might be a better idea to grey out the tabs when the addon is not activated, and use 
            // an action hook to insert the code relative to sections of addon.
            if ( is_plugin_active( 'wp-rss-excerpts-thumbnails/wp-rss-excerpts-thumbnails.php' ) ) { ?>
                <h2 class="nav-tab-wrapper">
                    <a href="?post_type=wprss_feed&page=wprss-aggregator-settings&tab=general_settings" 
                    class="nav-tab <?php echo $active_tab == 'general_settings' ? 'nav-tab-active' : ''; ?>">General</a>
                    <a href="?post_type=wprss_feed&page=wprss-aggregator-settings&tab=excerpts_settings" 
                    class="nav-tab <?php echo $active_tab == 'excerpts_settings' ? 'nav-tab-active' : ''; ?>">Excerpts</a>
                    <a href="?post_type=wprss_feed&page=wprss-aggregator-settings&tab=thumbnails_settings" 
                    class="nav-tab <?php echo $active_tab == 'thumbnails_settings' ? 'nav-tab-active' : ''; ?>">Thumbnails</a>          
                </h2>
            <?php } ?>

            <form action="options.php" method="post">   
            
                <?php 
                if ( is_plugin_active( 'wp-rss-excerpts-thumbnails/wp-rss-excerpts-thumbnails.php' ) ) { 
                    if( $active_tab == 'general_settings' ) {         
                        settings_fields( 'wprss_settings_general' ); 
                        do_settings_sections( 'wprss_settings_general' ); 
                    }
                    
                    else if( $active_tab == 'excerpts_settings' ) {         
                        settings_fields( 'wprss_settings_excerpts' );
                        do_settings_sections( 'wprss_settings_excerpts' ); 
                    }

                    else if( $active_tab == 'thumbnails_settings' ) {         
                        settings_fields( 'wprss_settings_thumbnails' );
                        do_settings_sections( 'wprss_settings_thumbnails' );   
                    }              
                
                    submit_button( __( 'Save Settings', 'wprss' ) ); 
                }
                else {
                    settings_fields( 'wprss_settings_general' ); 
                    do_settings_sections( 'wprss_settings_general' );  
                    submit_button( __( 'Save Settings', 'wprss' ) ); 
                }
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
     * General settings section header
     * @since 3.0
     */
    function wprss_settings_styles_callback() {
        echo '<p>' . __( 'If you would like to disable all styles used in this plugin, tick the checkbox.', 'wprss' ) . '</p>';
    }


    /** 
     * Follow or No Follow dropdown
     * @since 1.1
     */
    function wprss_setting_follow_dd_callback() {
        $options = get_option( 'wprss_settings_general' );
        $items = array( 
                    __( 'No follow', 'wprss' ), 
                    __( 'Follow', 'wprss' ) 
        );        
        echo "<select id='follow-dd' name='wprss_settings_general[follow_dd]'>";
        foreach( $items as $item ) {
            $selected = ( $options['follow_dd'] == $item) ? 'selected="selected"' : '';
            echo "<option value='$item' $selected>$item</option>";
        }
        echo "</select>";
    }


    /** 
     * Link open setting dropdown
     * @since 1.1
     */
    function wprss_setting_open_dd_callback() {
        $options = get_option( 'wprss_settings_general' );
        $items = array( 
            __( 'Lightbox', 'wprss' ), 
            __( 'New window', 'wprss' ), 
            __( 'None', 'wprss' )
        );
        echo "<select id='open-dd' name='wprss_settings_general[open_dd]'>";
        foreach( $items as $item ) {
            $selected = ( $options['open_dd'] == $item ) ? 'selected="selected"' : '';
            echo "<option value='$item' $selected>$item</option>";
        }
        echo "</select>";   
    }


    /** 
     * Set limit for feeds on frontend
     * @since 2.0
     */
    function wprss_setting_feed_limit_callback() {
        $options = get_option( 'wprss_settings_general' );                    
        echo "<input id='feed-limit' name='wprss_settings_general[feed_limit]' type='text' value='{$options['feed_limit']}' />";   
        echo "<label for='feed-limit'>Enter the number of feeds to display on the front end</label>";
    }


    /** 
     * Set date format 
     * @since 3.0
     */
    function wprss_setting_date_format_callback() {
        $options = get_option( 'wprss_settings_general' );                    
        echo "<input id='date-format' name='wprss_settings_general[date_format]' type='text' value='{$options['date_format']}' />";   
        echo "<label for='date-format'>Date formatting, using the <a href='http://codex.wordpress.org/Formatting_Date_and_Time'>PHP date formats</a></label>";
    }


    /** 
     * Limit number of feed items stored
     * @since 3.0
     */
    function wprss_setting_limit_feed_items_callback() {
        $options = get_option( 'wprss_settings_general' );                    
        echo "<input id='limit-feed-items' name='wprss_settings_general[limit_feed_items]' type='text' value='{$options['limit_feed_items']}' />";   
        echo "<label for='limit-feed-items'>Enter the maximum number of feeds to store in the database; enter 0 for unlimited feed items</label>";
    }


    /** 
     * Cron interval dropdown callback
     * @since 3.0
     */
    function wprss_setting_cron_interval_callback() {
        $options = get_option( 'wprss_settings_general' );  
        $current = $options['cron_interval'];

        $schedules = wp_get_schedules();     
        //var_dump($schedules);
        echo "<select id='cron-interval' name='wprss_settings_general[cron_interval]'>";
        foreach( $schedules as $schedule_name=>$schedule_data ) { ?>
            <option value="<?php echo $schedule_name; ?>" <?php selected( $current, $schedule_name ); ?> >
                <?php echo $schedule_data['display']; ?> (<?php echo wprss_interval( $schedule_data['interval'] ); ?>)
            </option>
        <?php } ?>
        </select><?php
    }


    /** 
     * Enable source
     * @since 3.0
     */
    function wprss_setting_source_enable_callback( $args ) {
        $options = get_option( 'wprss_settings_general' );                    
        echo "<input id='source-enable' name='wprss_settings_excerpts[source_enable]' type='checkbox' value='1' " . checked( 1, $options['source_enable'], false ) . " />";   
        echo "<label for='source-enable'>Check this box to enable feed source display</label>";   
    }


    /** 
     * Enable date
     * @since 3.0
     */
    function wprss_setting_date_enable_callback( $args ) {
        $options = get_option( 'wprss_settings_general' );                    
        echo "<input id='date-enable' name='wprss_settings_excerpts[date_enable]' type='checkbox' value='1' " . checked( 1, $options['date_enable'], false ) . " />";   
        echo "<label for='date-enable'>Check this box to enable display of date published</label>";   
    }    


    /** 
     * Disable styles
     * @since 3.0
     */
    function wprss_setting_styles_disable_callback( $args ) {
        $options = get_option( 'wprss_settings_general' );                    
        echo "<input id='styles-disable' name='wprss_settings_excerpts[styles_disable]' type='checkbox' value='1' " . checked( 1, $options['styles_disable'], false ) . " />";   
        echo "<label for='styles-disable'>Check this box to disable all plugin styles</label>";   
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

        if ( ! isset( $input['source_enable'] ) || $input['source_enable'] != '1' ) {
            $output['source_enable'] = 0; wp_die(); }
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
        
        if ( $input['cron_interval'] != $current_cron_interval ) {
            wp_clear_scheduled_hook( 'wprss_fetch_all_feeds_hook' );    
            wp_schedule_event( time(), $input['cron_interval'], 'wprss_fetch_all_feeds_hook' );
        }



        // Return the array processing any additional functions filtered by this action
        return apply_filters( 'wprss_settings_general_validate', $output, $input );
    }