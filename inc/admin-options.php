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
     */ 
    function wprss_admin_init() {
        //register_setting( 'wprss_options', 'wprss_options' );    
              
        register_setting( 'wprss_settings', 'wprss_settings' );
        
        add_settings_section( 'wprss-settings-main', '', 'wprss_settings_section_text', 'wprss-aggregator-settings' );   
        
        add_settings_field( 'wprss-settings-open-dd', __( 'Open links behaviour', 'wprss' ), 
                            'wprss_setting_open_dd', 'wprss-aggregator-settings', 'wprss-settings-main');

        add_settings_field( 'wprss-settings-follow-dd', __( 'Set links as', 'wprss' ), 
                            'wprss_setting_follow_dd', 'wprss-aggregator-settings', 'wprss-settings-main');     

        add_settings_field( 'wprss-settings-feed-limit', __( 'Feed limit', 'wprss' ), 
                            'wprss_setting_feed_limit', 'wprss-aggregator-settings', 'wprss-settings-main');  

        do_action( 'wprss_admin_init' );
    }  


    /**
     * Build the plugin settings page, used to save general settings like whether a link should be follow or no follow
     * @since 1.1
     */ 
    function wprss_settings_page() {
        ?>
        <div class="wrap">
            <?php screen_icon( 'wprss-aggregator' ); ?>
        
            <h2><?php _e( 'WP RSS Aggregator Settings', 'wprss' ); ?></h2>
            
            <form action="options.php" method="post">            
                <?php settings_fields( 'wprss_settings' ) ?>
                <?php do_settings_sections( 'wprss-aggregator-settings' ); ?>
                <p class="submit"><input type="submit" value="<?php _e( 'Save Settings', 'wprss' ); ?>" name="submit" class="button-primary"></p>
            </form>
        </div>
        <?php
    }


    // Draw the section header
    function wprss_settings_section_text() {
        echo '<h3>General Plugin Settings</h3>';
    }


    /** 
     * Follow or No Follow dropdown
     * @since 1.1
     */
    function wprss_setting_follow_dd() {
        $options = get_option( 'wprss_settings' );
        $items = array( 
                    __( 'No follow', 'wprss' ), 
                    __( 'Follow', 'wprss' ) 
        );        
        echo "<select id='follow-dd' name='wprss_settings[follow_dd]'>";
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
    function wprss_setting_open_dd() {
        $options = get_option( 'wprss_settings' );
        $items = array( 
            __( 'Lightbox', 'wprss' ), 
            __( 'New window', 'wprss' ), 
            __( 'None', 'wprss' )
        );
        echo "<select id='open-dd' name='wprss_settings[open_dd]'>";
        foreach( $items as $item ) {
            $selected = ( $options['open_dd'] == $item) ? 'selected="selected"' : '';
            echo "<option value='$item' $selected>$item</option>";
        }
        echo "</select>";   
    }


    /** 
     * Set limit for feeds on frontend
     * @since 2.0
     */
    function wprss_setting_feed_limit() {
        $options = get_option( 'wprss_settings' );                    
        echo "<input id='feed-limit' name='wprss_settings[feed_limit]' type='text' value='$options[feed_limit]' />";   
    }





