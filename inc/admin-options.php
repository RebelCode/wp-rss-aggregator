<?php  
    /**
     * Plugin administration page
     * 
     * Note: Wording of options and settings is confusing, due to the plugin originally only having 
     * an 'options' page to enter feed sources, and now needing two screens, one for feed sources and one for 
     * general settings. Might implement something cleaner in the future.
     */ 
    

    /**
     * Custom Post Type Icon for Admin Menu & Post Screen
     * @since  1.2
     */
    add_action( 'admin_head', 'custom_post_type_icon' );

    function custom_post_type_icon() {
        ?>
        <style>
            /* Post Screen - 32px */
            .icon32-posts-wprss_feed {
                background: transparent url( <?php echo WPRSS_IMG . 'icon-adminpage32.png'; ?> ) no-repeat left top !important;
            } 
            /* Post Screen - 32px */
            .icon32-posts-wprss_feed_item {
                background: transparent url( <?php echo WPRSS_IMG . 'icon-adminpage32.png'; ?> ) no-repeat left top !important;
            }   
        </style>
    <?php } 
     

    /**
     * Register menu and submenus
     * @since 1.2
     */ 
    
    // Add the admin options pages as submenus to the Feed CPT   
    add_action( 'admin_menu', 'wprss_register_menu_pages' );
    
    function wprss_register_menu_pages() {        
          
        //create submenu items        
        add_submenu_page( 'edit.php?post_type=wprss_feed', 'WP RSS Aggregator Settings', 'Settings', 'manage_options', 'wprss-aggregator-settings', wprss_settings_page );            
        add_submenu_page( 'edit.php?post_type=wprss_feed', 'Import/Export Feeds', 'Import/Export', 'manage_options', 'wprss-aggregator-import-export', wprss_import_export_page );
        add_submenu_page( 'edit.php?post_type=wprss_feed', 'Uninstall', 'Uninstall', 'manage_options', 'wprss-aggregaor-uninstall', wprss_uninstall_page );
        add_submenu_page( 'edit.php?post_type=wprss_feed', 'Help', 'Help', 'manage_options', 'wprss-aggregator-help', wprss_help_page );  
    }



    /**
     * Register and define options and settings
     * @since  1.2
     */ 
    
    add_action( 'admin_init', 'wprss_admin_init' );
    
    function wprss_admin_init() {
        register_setting( 'wprss_options', 'wprss_options' );    
        add_settings_section( 'wprss_main', '', 'wprss_section_text', 'wprss' );       

        register_setting( 'wprss_settings', 'wprss_settings' );
        

        add_settings_section( 'wprss-settings-main', '', 'wprss_settings_section_text', 'wprss-aggregator-settings' );   
        
        add_settings_field( 'wprss-settings-open-dd', 'Open links behaviour', 
                            'wprss_setting_open_dd', 'wprss-aggregator-settings', 'wprss-settings-main');

        add_settings_field( 'wprss-settings-follow-dd', 'Set links as', 
                            'wprss_setting_follow_dd', 'wprss-aggregator-settings', 'wprss-settings-main');        
    }  

    
    /**
     * DEPRECATED
     * Draw options page, used for adding feeds 
     */ 
    
 /*   function wprss_options_page() {
        ?>
        <div class="wrap">
            <?php screen_icon( 'wprss-aggregator' ); ?>
        
         
            <h2>WP RSS Aggregator Feed Sources</h2>
            <div id="options">
                <form action="options.php" method="post">            
                    <?php
                    
                    settings_fields( 'wprss_options' );
                    do_settings_sections( 'wprss' );
                    $options = get_option( 'wprss_options' );        
                    if ( !empty($options) ) {
                        $size = count($options);
                        for ( $i = 1; $i <= $size; $i++ ) {            
                            if( $i % 2 == 0 ) continue;
                            echo "<div class='wprss-input'>";
                            
                            $key = key( $options );
                            
                            echo "<p><label class='textinput' for='$key'>" . wprss_convert_key( $key ) . "</label>
                            <input id='$key' class='wprss-input' size='100' name='wprss_options[$key]' type='text' value='$options[$key]' /></p>";
                            
                            next( $options );
                            
                            $key = key( $options );
                            
                            echo "<p><label class='textinput' for='$key'>" . wprss_convert_key( $key ) . "</label>
                            <input id='$key' class='wprss-input' size='100' name='wprss_options[$key]' type='text' value='$options[$key]' /></p>";
                            
                            next( $options );
                            echo "</div>"; 
                            
                        }
                    }
                                        
                    ?>
                    <div id="buttons"><a href="#" id="add"><img src="<?php echo WPRSS_IMG; ?>add.png"></a>  
                    <a href="#" id="remove"><img src="<?php echo WPRSS_IMG; ?>remove.png"></a></div>  
                    <p class="submit"><input type="submit" value="Save Settings" name="submit" class="button-primary"></p>
                
                </form>
            </div> <!-- end options -->
        </div> <!-- end wrap -->
        <?php 
    }
    
    
  


    /** 
     * DEPRECATED
     * Explain the options section
     */ 
    
   /* function wprss_section_text() {
        echo '<p>Enter a name and URL for each of your feeds. The name is used just for your reference.</p>';
    }



    /**
     * Build the plugin settings page, used to save general settings like whether a link should be follow or no follow
     * @since 1.1
     */ 

    function wprss_settings_page() {
        ?>
        <div class="wrap">
            <?php screen_icon( 'wprss-aggregator' ); ?>
        
            <h2>WP RSS Aggregator Settings</h2>
            
            <form action="options.php" method="post">            
                <?php settings_fields( 'wprss_settings' ) ?>
                <?php do_settings_sections( 'wprss-aggregator-settings' ); ?>
                <p class="submit"><input type="submit" value="Save Settings" name="submit" class="button-primary"></p>
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
        $items = array( "No follow", "Follow" );
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
        $items = array( "Lightbox", "New window", "None" );
        echo "<select id='open-dd' name='wprss_settings[open_dd]'>";
        foreach( $items as $item ) {
            $selected = ( $options['open_dd'] == $item) ? 'selected="selected"' : '';
            echo "<option value='$item' $selected>$item</option>";
        }
        echo "</select>";   
    }






// Body class for admin
// http://www.kevinleary.net/customizing-wordpress-admin-css-javascript/
function base_admin_body_class( $classes )
{
    // Current action
    if ( is_admin() && isset($_GET['action']) ) {
        $classes .= 'action-'.$_GET['action'];
    }
    // Current post ID
    if ( is_admin() && isset($_GET['post']) ) {
        $classes .= ' ';
        $classes .= 'post-'.$_GET['post'];
    }
    // New post type & listing page
    if ( isset($_GET['post_type']) ) $post_type = $_GET['post_type'];
    if ( isset($post_type) ) {
        $classes .= ' ';
        $classes .= 'post-type-'.$post_type;
    }
    // Editting a post type
    $post_query = $_GET['post'];
    if ( isset($post_query) ) {
        $current_post_edit = get_post($post_query);
        $current_post_type = $current_post_edit->post_type;
        if ( !empty($current_post_type) ) {
            $classes .= ' ';
            $classes .= 'post-type-'.$current_post_type;
        }
    }
    // Return the $classes array
    return $classes;
}
add_filter('admin_body_class', 'base_admin_body_class');








?>