<?php
/*
    Plugin Name: WP RSS Aggregator
    Plugin URI: http://www.jeangalea.com
    Description: Imports and merges multiple RSS Feeds using SimplePie
    Version: 1.0
    Author: Jean Galea
    Author URI: http://www.jeangalea.com
    License: GPLv2
*/

/*  Copyright 2011 Jean Galea (email : jean@jpgalea.com)
    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.
    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
    GNU General Public License for more details.
    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA
*/


    /**
     * Plugin activation procedure
     */      
    
    register_activation_hook( __FILE__, 'wprss_install' );    
    
    function wprss_install() {
        // Activates the plugin and checks for compatible version of WordPress 
        if ( version_compare( get_bloginfo( 'version' ), '2.9', '<' ) ) {
            deactivate_plugins ( basename( __FILE__ ));     // Deactivate plugin
            wp_die( "This plugin requires WordPress version 2.9 or higher." );
        }
          
        if ( !wp_next_scheduled( 'wprss_generate_hook' ) ) {            
            // Schedule to run hourly
            wp_schedule_event( time(), 'hourly', 'wprss_generate_hook' );
        }
        
        add_action( 'wprss_generate_hook', 'wp_rss_aggregator' );                
    }
    
    
    /**
     * Plugin deactivation procedure
     */    
    
    register_deactivation_hook( __FILE__, 'wprss_deactivate' );
    
    function wprss_deactivate() {
        // on deactivation remove the cron job 
        if ( wp_next_scheduled( 'wprss_generate_hook' ) ) 
        wp_clear_scheduled_hook( 'wprss_generate_hook' );
    }
     
    
    /**
     * Actions for plugin's options page
     */
    
    // Only load scripts and CSS if we are on this plugin's options page (admin)
    if ( isset( $_GET['page'] ) && $_GET['page'] == 'wprss_aggregator' ) {
        add_action( 'admin_print_scripts', 'wprss_register_scripts' );
        add_action( 'admin_print_styles', 'wprss_header' );
    }    
  

    /**
     * Include scripts in plugin page header (admin)
     */     
    
    function wprss_register_scripts() {
         wp_enqueue_script( 'jquery' );
         wp_enqueue_script( 'add-remove', plugins_url( 'scripts/add-remove.js', __FILE__) );
    }
    
    
    /**
     * Include Colorbox-related script and CSS in WordPress head on frontend
     */      
    
    add_action( 'wp_enqueue_scripts', 'wprss_frontend_scripts' );
    
    function wprss_frontend_scripts() {
         wp_enqueue_style( 'styles', plugins_url( 'css/colorbox.css', __FILE__) );
         wp_enqueue_script( 'jquery.colorbox-min', plugins_url( 'scripts/jquery.colorbox-min.js', __FILE__) );         
    }    
    
    
    /**
     * Output JQuery command to trigger Colorbox for links in the <head>
     */  
    
    add_action ( 'wp_head', 'wprss_head_output' );
    
    function wprss_head_output() {
        echo "<script type='text/javascript'>jQuery(document).ready(function(){ jQuery('a.colorbox').colorbox({iframe:true, width:'80%', height:'80%'});});</script>";
    }


    /**
     * Include CSS in plugin page header
     */ 
    
    function wprss_header() {        
        wp_enqueue_style( 'styles', plugins_url( 'css/styles.css', __FILE__) );
    }  
    
    
    /**
     * Plugin administration page
     */ 
    
    // Add the admin options page    
    add_action( 'admin_menu', 'wprss_add_page' );
    
    function wprss_add_page() {
        add_options_page( 'RSS Aggregator', 'RSS Aggregator', 'manage_options', 'wprss_aggregator', 'wprss_options_page' );
    }    
    
    /**
     * Draw options page
     */ 
    
    function wprss_options_page() {
        ?>
        <div class="wrap">
        <?php screen_icon(); ?>
        
         
        <h2>WordPress RSS Aggregator</h2>
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
        
        $images_url = plugins_url( 'images', __FILE__); 
        ?>
        <div id="buttons"><a href="#" id="add"><img src="<?php echo $images_url; ?>/add.png"></a>  
        <a href="#" id="remove"><img src="<?php echo $images_url; ?>/remove.png"></a></div>  
        <p class="submit"><input type="submit" value="Save Settings" name="submit" class="button-primary"></p>
        
        </form>
        </div>
        </div>
        <?php 
    }
    
    
    /**
     * Register and define settings
     */ 
    
    add_action( 'admin_init', 'wprss_admin_init' );
    
    function wprss_admin_init() {
        register_setting( 'wprss_options', 'wprss_options' );
        add_settings_section( 'wprss_main', 'WP RSS Aggregator settings', 'wprss_section_text', 'wprss' );       
    }    


    /**
     * Explain the options section
     */ 
    
    function wprss_section_text() {
        echo '<p>Enter a name and URL for each of your feeds. The name is used just for your reference.</p>';
    }
    
    
    /**
     * Convert from field name to user-friendly name
     */ 
    
    function wprss_convert_key( $key ) { 
        if ( strpos( $key, 'feed_name_' ) === 0 ) { 
            $label = str_replace( 'feed_name_', 'Feed name ', $key );
        }
        
        else if ( strpos( $key, 'feed_url_' ) === 0 ) { 
            $label = str_replace( 'feed_url_', 'Feed URL ', $key );
        }
        return $label;
    }
    
    
    /**
     * Set up shortcodes and call the main function for output
     */     
    
    // Register a new shortcode
    add_shortcode( 'wp_rss_aggregator', 'wprss_shortcode');
    
    function wprss_shortcode( $atts ) {    
        if ( !empty ($atts) ) {
            foreach ( $atts as $key => &$val ) {
                $val = html_entity_decode($val);
            }
        }
        wp_rss_aggregator( $atts );       
    }


    /**
     * Get feeds and output the aggregation
     */     
        
    function wp_rss_aggregator( $args = array() ) {
        
        $defaults = array(
                          'date_before' => '<h3>',
                          'date_after' => '</h3>',
                          'links_before' => '<ul>',
                          'links_after' => '</ul>',
                          'link_before' => '<li>',
                          'link_after' => '</li>'                          
                    );
        
        // Parse incoming $args into an array and merge it with $defaults        	
	    $args = wp_parse_args( $args, $defaults );
        // Declare each item in $args as its own variable
        extract( $args, EXTR_SKIP );        
        
        $wprss_options = get_option( 'wprss_options', 'option not found' );   
    
        foreach ( $wprss_options as $key => $value ) {            
            if ( strpos( $key, 'feed_url_' ) === 0 ) {                 
                $feed_uris[] = $value;
            } 
        }        
        
        if ( !empty( $feed_uris ) ) {           
            // update feeds every hour else serve from cache
            function wprss_hourly_feed() { return 3600; }
            add_filter( 'wp_feed_cache_transient_lifetime', 'wprss_hourly_feed' );
            $feed = fetch_feed( $feed_uris );    
        }
        else echo 'No feed defined';
        remove_filter( 'wp_feed_cache_transient_lifetime', 'wprss_hourly_feed' );
        
        $items = $feed->get_items();        
        $items_today = array();
        $items_yesterday = array();
        $items_two_days_ago = array();
        $items_older = array();
        
        
        foreach ( $items as $item ):        
            $item_date = $item->get_date('l jS F (Y-m-d)');
            if ( $item_date == date('l jS F (Y-m-d)', strtotime('today') ) ) {
                $items_today[] = $item;
            }
            else if ( $item_date == date('l jS F (Y-m-d)', strtotime('yesterday') ) ) {
                $items_yesterday[] = $item; 
            }
            else if ( $item_date == date('l jS F (Y-m-d)', strtotime('-2 days') ) ) {
                $items_two_days_ago[] = $item;
            }
            else {
                $items_older[] = $item;
            }                   
        endforeach;
        
        if ( !empty( $items_today ) ) { 
            echo $date_before . 'Today' . $date_after;
            echo $links_before;
            foreach ( $items_today as $item ) {
                
                echo $link_before . '<a class="colorbox" href="' . $item->get_permalink() .'">'. $item->get_title(). ' '. '</a>'; 
                echo '<br><span class="feed-source">Source: '.$item->get_feed()->get_title()/* . ' | ' . $item->get_date('l jS F').''*/ . '</span>';
                echo $link_after;
                
            }
            echo $links_after;
        }
        
        if ( !empty( $items_yesterday ) ) { 
            echo $date_before . 'Yesterday' . $date_after;
            echo $links_before;
            foreach ( $items_yesterday as $item ) {
                echo '<li><a class="colorbox" href="' . $item->get_permalink() .'">'. $item->get_title(). ' '. '</a>'; 
                echo '<br><span class="feed-source">Source: '.$item->get_feed()->get_title()/* . ' | ' . $item->get_date('l jS F').''*/ . '</span>';
                echo $link_after;
            }
            echo $links_after;
        }
        
        if ( !empty( $items_two_days_ago ) ) { 
            echo $date_before . '2 days ago' . $date_after;
            echo $links_before;
            foreach ( $items_two_days_ago as $item ) {
                echo '<li><a class="colorbox" href="' . $item->get_permalink() .'">'. $item->get_title(). ' '. '</a>'; 
                echo '<br><span class="feed-source">Source: '.$item->get_feed()->get_title()/* . ' | ' . $item->get_date('l jS F').''*/ . '</span>';
                echo $link_after;
            }
            echo $links_after;
        }
        if ( !empty( $items_older ) ) { 
            echo $date_before . 'More than 2 days ago' . $date_after;
            echo $links_before;
            foreach ( $items_older as $item ) {
                echo '<li><a class="colorbox" href="' . $item->get_permalink() .'">'. $item->get_title(). ' '. '</a>'; 
                echo '<br><span class="feed-source">Source: '.$item->get_feed()->get_title() . ' | ' . $item->get_date('l jS F').'</span>';
                echo $link_after;
            }           
            echo $links_after;
        }
    }
    
    // use just for testing - runs on each wp load
    //add_action( 'wp_loaded', 'wp_rss_aggregator' );
        
?>