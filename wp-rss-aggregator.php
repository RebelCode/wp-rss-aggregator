<?php
    /*
    Plugin Name: WP RSS Aggregator
    Plugin URI: http://www.wprssaggregator.com
    Description: Imports and aggregates multiple RSS Feeds using SimplePie
    Version: 3.4.6
    Author: Jean Galea
    Author URI: http://www.wprssaggregator.com
    License: GPLv3
    License URI: http://www.gnu.org/licenses/gpl-2.0.html
    */

    /*  
    Copyright 2012-2013 Jean Galea (email : info@jeangalea.com)
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
     * @package   WPRSSAggregator
     * @version   3.4.6
     * @since     1.0
     * @author    Jean Galea <info@jeangalea.com>
     * @copyright Copyright (c) 2012-2013, Jean Galea
     * @link      http://www.wpmayor.com/
     * @license   http://www.gnu.org/licenses/gpl.html
     */

    /**
     * Define constants used by the plugin.
     */

    // Set the version number of the plugin. 
    if( !defined( 'WPRSS_VERSION' ) )
        define( 'WPRSS_VERSION', '3.4.6', true );

    // Set the database version number of the plugin. 
    if( !defined( 'WPRSS_DB_VERSION' ) )
        define( 'WPRSS_DB_VERSION', 8 );

    // Set the plugin prefix 
    if( !defined( 'WPRSS_PREFIX' ) )
        define( 'WPRSS_PREFIX', 'wprss', true );            

    // Set constant path to the plugin directory. 
    if( !defined( 'WPRSS_DIR' ) )
        define( 'WPRSS_DIR', plugin_dir_path( __FILE__ ) );        

    // Set constant URI to the plugin URL. 
    if( !defined( 'WPRSS_URI' ) )
        define( 'WPRSS_URI', plugin_dir_url( __FILE__ ) );        

    // Set the constant path to the plugin's javascript directory. 
    if( !defined( 'WPRSS_JS' ) )
        define( 'WPRSS_JS', WPRSS_URI . trailingslashit( 'js' ), true );

    // Set the constant path to the plugin's CSS directory. 
    if( !defined( 'WPRSS_CSS' ) )
        define( 'WPRSS_CSS', WPRSS_URI . trailingslashit( 'css' ), true );

    // Set the constant path to the plugin's images directory. 
    if( !defined( 'WPRSS_IMG' ) )
        define( 'WPRSS_IMG', WPRSS_URI . trailingslashit( 'images' ), true );

    // Set the constant path to the plugin's includes directory. 
    if( !defined( 'WPRSS_INC' ) )
        define( 'WPRSS_INC', WPRSS_DIR . trailingslashit( 'includes' ), true );
    

    /**
     * Load required files.
     */

    /* Load install, upgrade and migration code. */
    require_once ( WPRSS_INC . 'update.php' );           
    
    /* Load the shortcodes functions file. */
    require_once ( WPRSS_INC . 'shortcodes.php' );

    /* Load the custom post types and taxonomies. */
    require_once ( WPRSS_INC . 'custom-post-types.php' );  

    /* Load the file for setting capabilities of our post types */
    require_once ( WPRSS_INC . 'roles-capabilities.php' ); 

    /* Load the feed processing functions file */
    require_once ( WPRSS_INC . 'feed-processing.php' );   

    /* Load the feed display functions file */
    require_once ( WPRSS_INC . 'feed-display.php' );            

    /* Load the custom feed file */
    require_once ( WPRSS_INC . 'custom-feed.php' );            

    /* Load the cron job scheduling functions. */
    require_once ( WPRSS_INC . 'cron-jobs.php' ); 

    /* Load the admin functions file. */
    require_once ( WPRSS_INC . 'admin.php' );         

    /* Load the admin options functions file. */
    require_once ( WPRSS_INC . 'admin-options.php' );             

    /* Load the settings import/export file */
    require_once ( WPRSS_INC . 'admin-import-export.php' ); 

    /* Load the debugging file */
    require_once ( WPRSS_INC . 'system-info.php' ); 

    /* Load the miscellaneous functions file */
    require_once ( WPRSS_INC . 'misc-functions.php' ); 

    /* Load the OPML Class file */
    require_once ( WPRSS_INC . 'opml.php' );

    /* Load the OPML Importer file */
    require_once ( WPRSS_INC . 'opml-importer.php' );

    /* Load the system info file */
    require_once ( WPRSS_INC . 'admin-debugging.php' );     

    /* Load the admin display-related functions */
    require_once ( WPRSS_INC . 'admin-display.php' );     

    /* Load the admin metaboxes functions */
    require_once ( WPRSS_INC . 'admin-metaboxes.php' );     

    /* Load the scripts loading functions file */
    require_once ( WPRSS_INC . 'scripts.php' );   

    /* Load the Ajax notification file */
    require_once ( WPRSS_INC . 'admin-ajax-notice.php' ); 
    
    /* Load the dashboard welcome screen file */
    require_once ( WPRSS_INC . 'admin-dashboard.php' );  

    /* Load the logging class */
    require_once ( WPRSS_INC . 'roles-capabilities.php' );      

    /* Load the logging class */
    require_once ( WPRSS_INC . 'libraries/WP_Logging.php' );   
    require_once ( WPRSS_INC . 'admin-editor.php' );

    
    register_activation_hook( __FILE__ , 'wprss_activate' );
    register_deactivation_hook( __FILE__ , 'wprss_deactivate' );


    add_action( 'init', 'wprss_init' );     
    /**
     * Initialise the plugin
     *
     * @since  1.0
     * @return void
     */     
    function wprss_init() {                    
        do_action( 'wprss_init' );          
    }


    /**
     * Plugin activation procedure
     *
     * @since  1.0
     * @return void
     */  
    function wprss_activate() {
        /* Prevents activation of plugin if compatible version of WordPress not found */
        if ( version_compare( get_bloginfo( 'version' ), '3.3', '<' ) ) {
            deactivate_plugins ( basename( __FILE__ ));     // Deactivate plugin
            wp_die( __( 'This plugin requires WordPress version 3.3 or higher.' ), 'WP RSS Aggregator', array( 'back_link' => true ) );
        }  
        wprss_settings_initialize();
        flush_rewrite_rules();
        wprss_schedule_fetch_all_feeds_cron();   
        // Sets a transient to trigger a redirect upon completion of activation procedure
        set_transient( '_wprss_activation_redirect', true, 30 );
    }    


    /**
     * Plugin deactivation procedure
     *
     * @since 1.0
     */           
    function wprss_deactivate() {
        // on deactivation remove the cron job 
        if ( wp_next_scheduled( 'wprss_fetch_all_feeds_hook' ) ) {
            wp_clear_scheduled_hook( 'wprss_fetch_all_feeds_hook' );
        }
        flush_rewrite_rules();
    }


    add_action( 'plugins_loaded', 'wprss_load_textdomain' );
    /**
     * Loads the plugin's translated strings.
     * 
     * @since  2.1
     * @return void     
     */  
    function wprss_load_textdomain() { 
        load_plugin_textdomain( 'wprss', false, plugin_dir_path( __FILE__ ) . '/languages/' );
    }
    
    
    // PressTrends WordPress Action
    add_action( 'admin_init', 'wprss_presstrends_plugin' );  
    /**
     * Track plugin usage using PressTrends
     * 
     * @since  3.5
     * @return void     
     */  
    function wprss_presstrends_plugin() {
        // PressTrends Account API Key
        $api_key = 'znggu7vk7x2ddsiigkerzsca9q22xu1j53hp';
        $auth    = 'd8giw5yyux4noasmo8gua98n7fv2hrl11';
        // Start of Metrics
        global $wpdb;
        $data = get_transient( 'presstrends_cache_data' );
        if ( !$data || $data == '' ) {
            $api_base = 'http://api.presstrends.io/index.php/api/pluginsites/update?auth=';
            $url      = $api_base . $auth . '&api=' . $api_key . '';
            $count_posts    = wp_count_posts();
            $count_pages    = wp_count_posts( 'page' );
            $comments_count = wp_count_comments();
            if ( function_exists( 'wp_get_theme' ) ) {
                $theme_data = wp_get_theme();
                $theme_name = urlencode( $theme_data->Name );
            } else {
                $theme_data = get_theme_data( get_stylesheet_directory() . '/style.css' );
                $theme_name = $theme_data['Name'];
            }
            $plugin_name = '&';
            foreach ( get_plugins() as $plugin_info ) {
                $plugin_name .= $plugin_info['Name'] . '&';
            }
            // CHANGE __FILE__ PATH IF LOCATED OUTSIDE MAIN PLUGIN FILE
            $plugin_data         = get_plugin_data( __FILE__ );
            $posts_with_comments = $wpdb->get_var( "SELECT COUNT(*) FROM $wpdb->posts WHERE post_type='post' AND comment_count > 0" );
            $data                = array(
                'url'             => base64_encode(site_url()),
                'posts'           => $count_posts->publish,
                'pages'           => $count_pages->publish,
                'comments'        => $comments_count->total_comments,
                'approved'        => $comments_count->approved,
                'spam'            => $comments_count->spam,
                'pingbacks'       => $wpdb->get_var( "SELECT COUNT(comment_ID) FROM $wpdb->comments WHERE comment_type = 'pingback'" ),
                'post_conversion' => ( $count_posts->publish > 0 && $posts_with_comments > 0 ) ? number_format( ( $posts_with_comments / $count_posts->publish ) * 100, 0, '.', '' ) : 0,
                'theme_version'   => $plugin_data['Version'],
                'theme_name'      => $theme_name,
                'site_name'       => str_replace( ' ', '', get_bloginfo( 'name' ) ),
                'plugins'         => count( get_option( 'active_plugins' ) ),
                'plugin'          => urlencode( $plugin_name ),
                'wpversion'       => get_bloginfo( 'version' ),
            );
            foreach ( $data as $k => $v ) {
                $url .= '&' . $k . '=' . $v . '';
            }
            wp_remote_get( $url );
            set_transient( 'presstrends_cache_data', $data, 60 * 60 * 24 );
            }
        }  
