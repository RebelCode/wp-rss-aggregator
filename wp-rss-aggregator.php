<?php
    /**
     * Plugin Name: WP RSS Aggregator
     * Plugin URI: https://www.wprssaggregator.com/#utm_source=wpadmin&utm_medium=plugin&utm_campaign=wpraplugin
     * Description: Imports and aggregates multiple RSS Feeds.
     * Version: 4.10
     * Author: RebelCode
     * Author URI: https://www.wprssaggregator.com
     * Text Domain: wprss
     * Domain Path: /languages/
     * License: GPLv3
     */

    /**
     * Copyright (C) 2012-2016 RebelCode Ltd.
     *
     * This program is free software: you can redistribute it and/or modify
     * it under the terms of the GNU General Public License as published by
     * the Free Software Foundation, either version 3 of the License, or
     * (at your option) any later version.
     *
     * This program is distributed in the hope that it will be useful,
     * but WITHOUT ANY WARRANTY; without even the implied warranty of
     * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
     * GNU General Public License for more details.
     *
     * You should have received a copy of the GNU General Public License
     * along with this program.  If not, see <http://www.gnu.org/licenses/>.
     */

    /**
     * @package     WPRSSAggregator
     * @version     4.10
     * @since       1.0
     * @author      RebelCode
     * @copyright   Copyright (c) 2012-2016, RebelCode Ltd.
     * @link        https://www.wprssaggregator.com/
     * @license     http://www.gnu.org/licenses/gpl.html
     */

    /**
     * Define constants used by the plugin.
     */

    // Set the version number of the plugin.
    if( !defined( 'WPRSS_VERSION' ) )
        define( 'WPRSS_VERSION', '4.10', true );

    if( !defined( 'WPRSS_WP_MIN_VERSION' ) )
        define( 'WPRSS_WP_MIN_VERSION', '4.0', true );

    // Set the database version number of the plugin.
    if( !defined( 'WPRSS_DB_VERSION' ) )
        define( 'WPRSS_DB_VERSION', 15 );

    // Set the plugin prefix
    if( !defined( 'WPRSS_PREFIX' ) )
        define( 'WPRSS_PREFIX', 'wprss', true );

    // Set the plugin prefix
    if( !defined( 'WPRSS_FILE_CONSTANT' ) )
        define( 'WPRSS_FILE_CONSTANT', __FILE__, true );

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

    if( !defined( 'WPRSS_LANG' ) )
        define( 'WPRSS_LANG', WPRSS_DIR . trailingslashit( 'languages' ), true );

    // Set the constant path to the plugin's log file.
    if( !defined( 'WPRSS_LOG_FILE' ) )
        define( 'WPRSS_LOG_FILE', WP_CONTENT_DIR . '/log/wprss/log', true );

    if( !defined( 'WPRSS_LOG_FILE_EXT' ) )
        define( 'WPRSS_LOG_FILE_EXT', '.txt', true );

	if ( !defined('WPRSS_SL_STORE_URL') ) {
		define( 'WPRSS_SL_STORE_URL', 'http://www.wprssaggregator.com', TRUE );
	}

	if ( !defined( 'WPRSS_TEXT_DOMAIN' ) ) {
		define( 'WPRSS_TEXT_DOMAIN', 'wprss' );
    }

    // Maximum time for the feed source to be fetched
    if ( !defined( 'WPRSS_FEED_FETCH_TIME_LIMIT' ) ) {
        define( 'WPRSS_FEED_FETCH_TIME_LIMIT', 30, TRUE );
    }
    // Maximum time for a single feed item to import
    if ( !defined( 'WPRSS_ITEM_IMPORT_TIME_LIMIT' ) ) {
        define( 'WPRSS_ITEM_IMPORT_TIME_LIMIT', 15, TRUE );
    }
    // Where to take the diagnostic tests from
    if ( !defined( 'WPRACORE_DIAG_TESTS_DIR' ) ) {
        define( 'WPRACORE_DIAG_TESTS_DIR', WPRSS_DIR . 'test/diag' );
    }

    define( 'WPRSS_CORE_PLUGIN_NAME', 'WP RSS Aggregator' );

    /**
     * Load required files.
     */

    /* Autoloader for this plugin */
    require_once ( WPRSS_INC . 'autoload.php' );
    // Adding autoload paths
    wprss_autoloader()->add('Aventura\\Wprss\\Core', WPRSS_INC);
    wprss_autoloader()->add('Aventura\\Wprss\\Core\\DiagTest', WPRACORE_DIAG_TESTS_DIR);
    // Add tests
    add_filter('wprss_diag_tester_sources', function ($event) {
        $sources = $event->getData('sources');

        $locator = new \Dhii\SimpleTest\Locator\FilePathLocator();
        $locator->addPath(new \RecursiveDirectoryIterator(WPRACORE_DIAG_TESTS_DIR, 1));
        $testSource = new \RebelCode\Wprss\Debug\DiagTest\Model\TestSource($locator->locate(), wprss()->getCode(), WPRSS_CORE_PLUGIN_NAME);

        $sources[$testSource->getCode()] = $testSource;
        $event->setData('sources', $sources);

        return $event;
    });

    /* Only function definitions, no effect! */
    require_once(WPRSS_INC . 'functions.php');

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

	/* Load the blacklist functions file */
    require_once ( WPRSS_INC . 'feed-blacklist.php' );

    /* Load the feed importing functions file */
    require_once ( WPRSS_INC . 'feed-importing.php' );

    /* Load the feed states functions file */
    require_once ( WPRSS_INC . 'feed-states.php' );

    /* Load the feed display functions file */
    require_once ( WPRSS_INC . 'feed-display.php' );

    /* Load the custom feed file */
    require_once ( WPRSS_INC . 'custom-feed.php' );

    /* Load the custom post type feeds file */
    require_once ( WPRSS_INC . 'cpt-feeds.php' );

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
    require_once ( WPRSS_INC . 'OPML.php' );

    /* Load the OPML Importer file */
    require_once ( WPRSS_INC . 'opml-importer.php' );

    /* Load the admin debugging page file */
    require_once ( WPRSS_INC . 'admin-debugging.php' );

    /* Load the addons page file */
    require_once ( WPRSS_INC . 'admin-addons.php' );

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

    /* Load the security reset file */
    require_once ( WPRSS_INC . 'secure-reset.php' );

	/* Load the licensing file */
	require_once ( WPRSS_INC . 'licensing.php' );

    /* Load the admin editor file */
    require_once ( WPRSS_INC . 'admin-editor.php' );

    /* Load the admin heartbeat functions */
    require_once ( WPRSS_INC . 'admin-heartbeat.php' );

    // Load the statistics functions file
    require_once ( WPRSS_INC . 'admin-statistics.php' );

    // Load the logging functions file
    require_once ( WPRSS_INC . 'admin-log.php' );

	if ( !defined( 'WPRSS_LOG_LEVEL' ) )
		define( 'WPRSS_LOG_LEVEL', WPRSS_LOG_LEVEL_ERROR );

    /* Load the admin help file */
    require_once ( WPRSS_INC . 'admin-help.php' );

    /* Load the admin metaboxes help file */
    require_once ( WPRSS_INC . 'admin-help-metaboxes.php' );

    /* Load the admin settings help file */
    require_once ( WPRSS_INC . 'admin-help-settings.php' );

	/* SimplePie */
	require_once ( ABSPATH . WPINC . '/class-simplepie.php' );

	/* Access to feed */
	require_once ( WPRSS_INC . 'feed-access.php' );

    /* Load the fallbacks for mbstring */
    require_once ( WPRSS_INC . 'fallback-mbstring.php' );

    /* The "Leave a Review" notification module */
    require_once ( WPRSS_INC . 'leave-review-notification.php' );

    // Initializes licensing
    add_action( 'plugins_loaded', 'wprss_licensing' );

    register_activation_hook( __FILE__ , 'wprss_activate' );
    register_deactivation_hook( __FILE__ , 'wprss_deactivate' );


    /**
     * Returns the Core plugin singleton instance.
     *
     * @since 4.8.1
     * @return Aventura\Wprss\Core\Plugin
     */
    function wprss() {
        static $plugin = null;


        // One time initialization
        if (is_null($plugin)) {
            static $timesCalled = 0;
            if ($timesCalled) {
                throw new Exception( sprintf('%1$s has been initialized recursively', WPRSS_CORE_PLUGIN_NAME) );
            }
            $timesCalled++;

            /**
             * Basically, we could just do this here:
             * Factory::create();
             *
             * However, the actual setup allows for even further customization.
             * In fact, the factory can be substituted by some entirely different factory,
             * that creates and initializes a different plugin in a different way.
             */

            $factoryClassName = apply_filters('wprss_core_plugin_factory_class_name',
                'Aventura\\Wprss\\Core\\Factory');

            if (!class_exists($factoryClassName)) {
                throw new Aventura\Wprss\Exception(
                    sprintf('Could not initialize add-on: Factory class "%1$s" does not exist', $factoryClassName));
            }

            $plugin = call_user_func_array(array($factoryClassName, 'create'), array(array(
                'basename'      => __FILE__,
                'name'          => WPRSS_CORE_PLUGIN_NAME
            )));
        }

        return $plugin;
    }

    try {
        $instance = wprss();
    } catch (Exception $e) {
        if (WP_DEBUG && WP_DEBUG_DISPLAY) {
            throw $e;
        }
        wp_die( $e->getMessage() );
    }


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


    add_filter( 'wprss_admin_pointers', 'wprss_check_tracking_notice' );
    /**
     * Сhecks the tracking option and if not set, shows a pointer with opt in and out options.
     *
     * @since 3.6
     */
    function wprss_check_tracking_notice( $pointers ){
        $settings = get_option( 'wprss_settings_general', array( 'tracking' => '' ) );
        $wprss_tracking = ( isset( $settings['tracking'] ) )? $settings['tracking'] : '';

        if ( $wprss_tracking === '' ) {
            $tracking_pointer = array(
                'wprss_tracking_pointer'    =>  array(

                    'target'            =>  '#wpadminbar',
                    'options'           =>  array(
                        'content'           =>  '<h3>' . sprintf( __( 'Help improve %1$s', WPRSS_TEXT_DOMAIN ), WPRSS_CORE_PLUGIN_NAME ) . '</h3>' . '<p>' . sprintf( __( 'You\'ve just installed %1$s. Please helps us improve it by allowing us to gather anonymous usage stats so we know which configurations, plugins and themes to test with.', WPRSS_TEXT_DOMAIN ), WPRSS_CORE_PLUGIN_NAME ) . '</p>',
                        'position'          =>  array(
                            'edge'              =>  'top',
                            'align'             =>  'center',
                        ),
                        'active'            =>  TRUE,
                        'btns'              =>  array(
                            'wprss-tracking-opt-out'    =>  __( 'Do not allow tracking', WPRSS_TEXT_DOMAIN ),
                            'wprss-tracking-opt-in'    =>  __( 'Allow tracking', WPRSS_TEXT_DOMAIN ),
                        )
                    )
                )

            );
            return array_merge( $pointers, $tracking_pointer );
        }
        else return $pointers;
    }


    add_action( 'admin_enqueue_scripts', 'wprss_prepare_pointers', 1000 );
    /**
     * Prepare the admin pointers
     *
     * @since 3.6
     */
    function wprss_prepare_pointers() {
        // If the user is not an admin, do not show the pointer
        if ( !current_user_can( 'manage_options' ) )
            return;

        $screen = get_current_screen();
        $screen_id = $screen->id;

        // Get pointers
        $pointers = apply_filters( 'wprss_admin_pointers', array() );

        if ( ! $pointers || ! is_array( $pointers ) )
            return;

        $dismissed = explode( ',', (string) get_user_meta( get_current_user_id(), 'dismissed_wp_pointers', true ) );
        $valid_pointers = array();

        // Check pointers and remove dismissed ones.
        foreach ( $pointers as $pointer_id => $pointer ) {
            // Sanity check
            if ( in_array( $pointer_id, $dismissed ) || empty( $pointer )  || empty( $pointer_id ) || empty( $pointer['target'] ) || empty( $pointer['options'] ) )
                continue;
            $pointer['pointer_id'] = $pointer_id;
            // Add the pointer to $valid_pointers array
            $valid_pointers['pointers'][] =  $pointer;
        }

        // No valid pointers? Stop here.
        if ( empty( $valid_pointers ) )
            return;

        // Add pointers style to queue.
        wp_enqueue_style( 'wp-pointer' );

        // Add pointers script to queue. Add custom script.
        wp_enqueue_script( 'wprss-pointers', WPRSS_JS . 'pointers.js', array( 'wp-pointer' ) );

        // Add pointer options to script.
        wp_localize_script( 'wprss-pointers', 'wprssPointers', $valid_pointers );

        add_action( 'admin_print_footer_scripts', 'wprss_footer_pointer_scripts' );
    }


    /**
     * Print the scripts for the admin pointers
     *
     * @since 3.6
     */
    function wprss_footer_pointer_scripts() {
        ?>
        <script type="text/javascript">

            jQuery(document).ready( function($) {

                for( var i in wprssPointers.pointers ) {
                    var pointer = wprssPointers.pointers[i],
                        options = $.extend( pointer.options, {
                            content: pointer.options.content,
                            position: pointer.options.position,
                            close: function() {
                                $.post( ajaxurl, {
                                    pointer: pointer.pointer_id,
                                    action: 'dismiss-wp-pointer'
                                });
                            },
                            buttons: function( event, t ){
                                var btns = jQuery('<div></div>');
                                for( var i in pointer.options.btns ) {
                                    var btn = jQuery('<a>').attr('id', i).css('margin-left','5px').text( pointer.options.btns[i] );
                                    btn.bind('click.pointer', function () {
                                        t.element.pointer('close');
                                    });
                                    btns.append( btn );
                                }
                                return btns;
                            }
                        }
                    );

                    $(pointer.target).pointer( options ).pointer('open');
                }

                $('#wprss-tracking-opt-in').addClass('button-primary').click( function(){ wprssTrackingOptAJAX(1); } );
                $('#wprss-tracking-opt-out').addClass('button-secondary').click( function(){ wprssTrackingOptAJAX(0); } );

            });

        </script>

        <?php
    }


	function wprss_wp_min_version_satisfied() {
		return version_compare( get_bloginfo( 'version' ), WPRSS_WP_MIN_VERSION, '>=' );
	}


	add_action( 'init', 'wprss_add_wp_version_warning' );
	function wprss_add_wp_version_warning() {
		if ( wprss_wp_min_version_satisfied() )
			return;

		wprss_admin_notice_add(array(
			'id'			=> 'wp_version_warning',
			'content'		=> sprintf( __(
					'<p><strong>%2$s requires WordPress to be of version %1$s or higher.</strong></br>'
					. 'Older versions of WordPress are no longer supported by %2$s. Please upgrade your WordPress core to continue benefiting from %2$s support services.</p>',
				WPRSS_TEXT_DOMAIN ), WPRSS_WP_MIN_VERSION, WPRSS_CORE_PLUGIN_NAME ),
			'notice_type'	=> 'error'
		));

	}


	add_action( 'init', 'wprss_add_php_version_change_warning' );
	function wprss_add_php_version_change_warning() {
		$minVersion = '5.3';
		if ( version_compare(PHP_VERSION, $minVersion, '>=') )
			return;

		wprss_admin_notice_add(array(
			'id'			=> 'php_version_change_warning',
			'content'		=> sprintf( __(
					'<p><strong>%2$s is moving to PHP %1$s</strong></br>'
					. 'The next release of your favourite aggregator will not support PHP 5.2. <a href="http://www.wprssaggregator.com/wp-rss-aggregator-to-require-php-5-3/" target="_blank">Read why here</a></p>',
				WPRSS_TEXT_DOMAIN ), $minVersion, WPRSS_CORE_PLUGIN_NAME ),
			'notice_type'	=> 'error',
			'condition'		=> 'wprss_is_wprss_page'
		));
	}


    /**
     * Plugin activation procedure
     *
     * @since  1.0
     * @return void
     */
    function wprss_activate() {
        /* Prevents activation of plugin if compatible version of WordPress not found */
        if ( !wprss_wp_min_version_satisfied() ) {
            deactivate_plugins ( basename( __FILE__ ));     // Deactivate plugin
            wp_die( sprintf ( __( '%2$s requires WordPress version %1$s or higher.' ), WPRSS_WP_MIN_VERSION, WPRSS_CORE_PLUGIN_NAME ), WPRSS_CORE_PLUGIN_NAME, array( 'back_link' => true ) );
        }
        wprss_settings_initialize();
        flush_rewrite_rules();
        wprss_schedule_fetch_all_feeds_cron();

        // Get the previous welcome screen version
        $pwsv = get_option( 'wprss_pwsv', '0.0' );
        // If the aggregator version is higher than the previous version ...
        if ( version_compare( WPRSS_VERSION, $pwsv, '>' ) ) {
            // Sets a transient to trigger a redirect upon completion of activation procedure
            set_transient( '_wprss_activation_redirect', true, 30 );
        }

		include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		// Check if WordPress SEO is activate, if yes set its options for hiding the metaboxes on the wprss_feed and wprss_feed_item screens
		if ( is_plugin_active( 'wordpress-seo/wp-seo.php' ) ) {
			$wpseo_titles = get_option( 'wpseo_titles', array() );
			if ( isset( $wpseo_titles['hideeditbox-wprss_feed'] ) ) {
				$wpseo_titles['hideeditbox-wprss_feed'] = TRUE;
				$wpseo_titles['hideeditbox-wprss_feed_item'] = TRUE;
			}
			update_option( 'wpseo_titles', $wpseo_titles );
		}
    }


    /**
     * Plugin deactivation procedure
     *
     * @since 1.0
     */
    function wprss_deactivate() {
        // On deactivation remove the cron job
        wp_clear_scheduled_hook( 'wprss_fetch_all_feeds_hook' );
        wp_clear_scheduled_hook( 'wprss_truncate_posts_hook' );
        // Uschedule cron jobs for all feed sources
        $feed_sources = wprss_get_all_feed_sources();
        if( $feed_sources->have_posts() ) {
            // For each feed source
            while ( $feed_sources->have_posts() ) {
                // Stop its cron job
                $feed_sources->the_post();
                wprss_feed_source_update_stop_schedule( get_the_ID() );
            }
            wp_reset_postdata();
        }
        // Flush the rewrite rules
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
        load_plugin_textdomain( WPRSS_TEXT_DOMAIN, false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
    }


    /**
     * Utility filter function that returns TRUE;
     *
     * @since 3.8
     */
    function wprss_enable() {
        return TRUE;
    }


     /**
     * Utility filter function that returns FALSE;
     *
     * @since 3.8
     */
    function wprss_disable() {
        return FALSE;
    }

    /**
     * Gets the timezone string that corresponds to the timezone set for
     * this site. If the timezone is a UTC offset, or if it is not set, still
     * returns a valid timezone string.
     * However, if no actual zone exists in the configured offset, the result
     * may be rounded up, or failure.
     *
     * @see http://pl1.php.net/manual/en/function.timezone-name-from-abbr.php
     * @return string A valid timezone string, or false on failure.
     */
    function wprss_get_timezone_string() {
		$tzstring = get_option( 'timezone_string' );

		if ( empty($tzstring) ) {
            $offset = ( int )get_option( 'gmt_offset' );
            $tzstring = timezone_name_from_abbr( '', $offset * 60 * 60, 1 );
		}

		return $tzstring;
	}


    /**
     * @see http://wordpress.stackexchange.com/questions/94755/converting-timestamps-to-local-time-with-date-l18n#135049
     * @param string|null $format Format to use. Default: Wordpress date and time format.
     * @param int|null $timestamp The timestamp to localize. Default: time().
     * @return string The formatted datetime, localized and offset for local timezone.
     */
    function wprss_local_date_i18n( $timestamp = null, $format = null ) {
        $format = is_null( $format ) ? get_option( 'date_format' ) . ' ' . get_option( 'time_format' ) : $format;
        $timestamp = $timestamp ? $timestamp : time();

        $timezone_str = wprss_get_timezone_string() ? wprss_get_timezone_string() : 'UTC';
        $timezone = new DateTimeZone( $timezone_str );

        // The date in the local timezone.
		$date = new DateTime( null, $timezone );
		if ( version_compare(PHP_VERSION, '5.3', '>=') ) {
			$date->setTimestamp( $timestamp );
		} else {
			$datetime = getdate( intval($timestamp) );
			$date->setDate( $datetime['year'] , $datetime['mon'] , $datetime['mday'] );
			$date->setTime( $datetime['hours'] , $datetime['minutes'] , $datetime['seconds'] );
		}
        $date_str = $date->format( 'Y-m-d H:i:s' );

        // Pretend the local date is UTC to get the timestamp
        // to pass to date_i18n().
        $utc_timezone = new DateTimeZone( 'UTC' );
        $utc_date = new DateTime( $date_str, $utc_timezone );
        $timestamp = intval( $utc_date->format('U') );

        return date_i18n( $format, $timestamp, true );
    }


    /**
     * Gets an internationalized and localized datetime string, defaulting
     * to WP RSS format.
     *
     * @see wprss_local_date_i18n;
     * @param string|null $format Format to use. Default: Wordpress date and time format.
     * @param int|null $timestamp The timestamp to localize. Default: time().
     * @return string The formatted datetime, localized and offset for local timezone.
     */
    function wprss_date_i18n( $timestamp = null, $format = null ) {
        $format = is_null( $format ) ? wprss_get_general_setting( 'date_format' ) : $format;

        return wprss_local_date_i18n( $timestamp, $format );
    }


    /**
     * Checks whether or not the Script Debug mode is on.
     *
     * By default, this is the value of the SCRIPT_DEBUG WordPress constant.
     * However, this can be changed via the filter.
     * Also, in earlier versions of WordPress, this constant does not seem
     * to be initially declared. In this case it is assumed to be false,
     * as per {@link https://codex.wordpress.org/Debugging_in_WordPress#SCRIPT_DEBUG WordPress Codex} documentation.
     *
     * @since 4.7.4
     * @uses-filter wprss_is_script_debug To modify return value.
     * @return boolean True if script debugging is on; false otherwise.
     */
    function wprss_is_script_debug() {
        return apply_filters( 'wprss_is_script_debug', defined( 'SCRIPT_DEBUG' ) ? SCRIPT_DEBUG : false );
    }


    /**
     * Get the prefix for minified resources' extensions.
     *
     * @since 4.7.4
     * @see wprss_is_script_debug()
     * @uses-filter wprss_minified_extension_prefix To modify return value.
     * @return string The prefix that is to be applied to minified resources' file names, before the extension.
     */
    function wprss_get_minified_extension_prefix() {
        return apply_filters( 'wprss_minified_extension_prefix', '.min' );
    }


    /**
     * Get the absolute URL to a WP RSS Aggregator script.
     *
     * If Script Debugging is on, the extension will be prefixed appropriately.
     *
     * @since 4.7.4
     * @see wprss_get_minified_extension_prefix()
     * @param string $url The relative URL to the script resource, without the extension.
     * @param string $extension The extension of the script file name, including the period (.). Default: '.js'.
     * @return string The URL to the script local to WP RSS Aggregator, possibly minified.
     */
    function wprss_get_script_url( $url, $extension = null ) {
        if ( is_null( $extension ) )
            $extension = '.js';

        $script_url = WPRSS_JS . $url . (wprss_is_script_debug() ? wprss_get_minified_extension_prefix() : '') . $extension;
        return apply_filters( 'wprss_script_url',  $script_url, $url, $extension );
    }
