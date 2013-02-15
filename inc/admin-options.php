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
     * @since  2.0
     * @todo  add option for cron frequency
     */ 
    function wprss_admin_init() {
        register_setting( 'wprss_options', 'wprss_options' );    
        add_settings_section( 'wprss_main', '', 'wprss_section_text', 'wprss' );       

        register_setting( 'wprss_settings', 'wprss_settings' );
        
        add_settings_section( 'wprss-settings-main', '', 'wprss_settings_section_text', 'wprss-aggregator-settings' );   
        
        add_settings_field( 'wprss-settings-open-dd', __( 'Open links behaviour', 'wprss' ), 
                            'wprss_setting_open_dd', 'wprss-aggregator-settings', 'wprss-settings-main');

        add_settings_field( 'wprss-settings-follow-dd', __( 'Set links as', 'wprss' ), 
                            'wprss_setting_follow_dd', 'wprss-aggregator-settings', 'wprss-settings-main');     

        add_settings_field( 'wprss-settings-feed-limit', __( 'Feed limit', 'wprss' ), 
                            'wprss_setting_feed_limit', 'wprss-aggregator-settings', 'wprss-settings-main');  

        add_settings_field( 'wprss-settings-default-thumbnail', __( 'Default thumbnail image', 'wprss' ), 
                            'wprss_setting_default_thumbnail', 'wprss-aggregator-settings', 'wprss-settings-main');  

        add_settings_field( 'wprss-settings-default-thumbnail-width', __( 'Default thumbnail image width', 'wprss' ), 
                            'wprss_setting_default_thumbnail_width', 'wprss-aggregator-settings', 'wprss-settings-main');  

        add_settings_field( 'wprss-settings-default-thumbnail-height', __( 'Default thumbnail image height', 'wprss' ), 
                            'wprss_setting_default_thumbnail_height', 'wprss-aggregator-settings', 'wprss-settings-main');  

        add_settings_field( 'wprss-settings-default-thumbnail-preview', __( 'Default thumbnail image preview', 'wprss' ), 
                            'wprss_setting_default_thumbnail_preview', 'wprss-aggregator-settings', 'wprss-settings-main');  

    }  


    /**
     * Build the plugin settings page, used to save general settings like whether a link should be follow or no follow
     * @since 1.1
     */ 
    function wprss_settings_page() {
        ?>
        <div class="wrap">
            <?php screen_icon( 'wprss-aggregator' ); ?>
        
            <h2><?php _e( 'WP RSS Aggregator Settings' ); ?></h2>
            
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
   //     echo '<p>Enter your settings here.</p>';
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
        // echo the field
       
        echo "<input id='feed-limit' name='wprss_settings[feed_limit]' type='text' value='$options[feed_limit]' />";   
    }


    /** 
     * Set default thumbnail image
     */
    function wprss_setting_default_thumbnail() {
        $options = get_option( 'wprss_settings' );                    
        // echo the field
       
        echo "<input id='default-thumbnail' name='wprss_settings[default_thumbnail]' type='text' value='$options[default_thumbnail]' />";   
        echo "<input id='default-thumbnail-button' type='button' class='button' value='Choose image' />";   

    
    }    


    /** 
     * Set default thumbnail image width
     */
    function wprss_setting_default_thumbnail_width() {
        $options = get_option( 'wprss_settings' );                    
        // echo the field
       
        echo "<input id='default-thumbnail-width' name='wprss_settings[default_thumbnail_width]' type='text' value='$options[default_thumbnail_width]' />";   
    }

    /** 
     * Set default thumbnail image width
     */
    function wprss_setting_default_thumbnail_height() {
        $options = get_option( 'wprss_settings' );                    
        // echo the field
       
        echo "<input id='default-thumbnail-height' name='wprss_settings[default_thumbnail_height]' type='text' value='$options[default_thumbnail_height]' />";   
    }

    /** 
     * Default thumbnail image preview
     * http://wp.tutsplus.com/tutorials/creative-coding/how-to-integrate-the-wordpress-media-uploader-in-theme-and-plugin-options/
     */
    function wprss_setting_default_thumbnail_preview() {
        $options = get_option( 'wprss_settings' ); ?>
        <div id="default-thumbnail-preview" style="min-height: 100px;">
            <img style="max-width:100%;" src="<?php echo esc_url( $options['default_thumbnail'] ); ?>" />
        </div>
        <?php
    }



