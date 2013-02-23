<?php  
    /**
     * Plugin settings related functions 
     * 
     * Note: Wording of options and settings is confusing, due to the plugin originally only having 
     * an 'options' page to enter feed sources, and now needing two screens, one for feed sources and one for 
     * general settings. Might implement something cleaner in the future.
     *
     * @package WPRSSAggregator
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
            'wprss_settings_general',                        // The name of an option to sanitize and save.
            'wprss_settings_general_validate'               // A callback function that sanitizes the option's value.
        );                 
        
        add_settings_section( 
            'wprss_settings_general_section',               // ID used to identify this section and with which to register options 
            __( 'General plugin settings', 'wprss' ),       // Section title that shows within <H3> tags
            'wprss_settings_general_callback',              // Callback function that echoes some explanations
            'wprss_settings_general'                        // Settings page on which to show this section
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

        do_action( 'wprss_admin_init' );
    }  


    function wprss_settings_validate( $input ) {
    
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

        return $options;
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

            <h2 class="nav-tab-wrapper">
                <a href="?post_type=wprss_feed&page=wprss-aggregator-settings&tab=general_settings" 
                class="nav-tab <?php echo $active_tab == 'general_settings' ? 'nav-tab-active' : ''; ?>">General</a>
                <a href="?post_type=wprss_feed&page=wprss-aggregator-settings&tab=excerpts_settings" 
                class="nav-tab <?php echo $active_tab == 'excerpts_settings' ? 'nav-tab-active' : ''; ?>">Excerpts</a>
                <a href="?post_type=wprss_feed&page=wprss-aggregator-settings&tab=thumbnails_settings" 
                class="nav-tab <?php echo $active_tab == 'thumbnails_settings' ? 'nav-tab-active' : ''; ?>">Thumbnails</a>
            </h2>

            <form action="options.php" method="post">   
            
                <?php 
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
                submit_button( __( 'Save Settings', 'wprss' )); 
                ?>                  
                <!--<p class="submit"><input type="submit" value="<?php _e( 'Save Settings', 'wprss' ); ?>" name="submit" class="button-primary"></p>-->
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
    }


    /** 
     * Validate inputs from the general settings page
     * @since 3.0
     */
    function wprss_settings_general_validate( $input ) {
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
        
        // Return the array processing any additional functions filtered by this action
        return apply_filters( 'wprss_settings_general_validate', $output, $input );
    }


