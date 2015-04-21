<?php

if ( !defined( 'WPRSS_TRACKING_SERVER_URL' ) )
	define( 'WPRSS_TRACKING_SERVER_URL', 'http://www.wprssaggregator.com/', TRUE );

if ( !defined( 'WPRSS_TRACKING_INTEVAL' ) )
	define( 'WPRSS_TRACKING_INTEVAL', 'daily', TRUE );


// add_action( 'admin_init', 'wprss_send_tracking_data' );
function wprss_send_tracking_data() {

	// Check the tracking option - if turned off, exit out of function
	$tracking_option = wprss_get_general_setting('tracking');
	if ( $tracking_option == 0 || $tracking_option == FALSE ) return;

	// Get the tracking transient.
	$transient = get_transient( 'wprss_tracking_transient' );
	// If the transient did not expire, exit out of function
	if ( $transient !== FALSE && !isset( $_GET['wprss_send_report'] ) ) return;
	// If the GET parameter is set, show an admin notice
	if ( isset( $_GET['wprss_send_report'] ) ) {
		add_action( 'admin_notices', 'wprss_tracking_notice' );
	}

	// Check if running on localhost
	$site_url = site_url();
	$running_on_local = preg_match_all( "/(localhost|127\.0\.0\.1)/", $site_url, $matches ) > 0;
	if( $running_on_local ) {
		return;
	}

	// Get data about the plugin
	$plugin_data = get_plugin_data( WPRSS_FILE_CONSTANT );

	// Get the theme name
	if ( function_exists( 'wp_get_theme' ) ) {
		$theme_data = wp_get_theme();
		$theme_name = $theme_data->Name;
	} else {
		$theme_data = get_theme_data( get_stylesheet_directory() . '/style.css' );
		$theme_name = $theme_data['Name'];
	}

	// Get plugins
	$plugins = get_plugins();
	$active_plugins_option = get_option( 'active_plugins', array() );
	// Prepare plugin arrays
	$active_plugins = array();
	$inactive_plugins = array();
	// Loop through plugins
	foreach ( $plugins as $plugins_path => $plugin_info ) {
		// If plugin found in active plugins list, then add to the active_plugins array
		if ( in_array( $plugins_path, $active_plugins_option ) ) {
			$add_to = &$active_plugins;
		}
		// Otherwise add to inactive_plugins array
		else {
			$add_to = &$inactive_plugins;
		}
		// Add the plugin info to the chosen array
		$add_to[] = $plugin_info['Name'] . ' v' . $plugin_info['Version'];
	}

	// If multisite
	if ( is_multisite() ) {
		// Get network plugins
		$network_plugins = wp_get_active_network_plugins();
		$network_active_plugins_option = get_site_option( 'active_sitewide_plugins', array() );
		// Prepare plugin array
		$network_active_plugins = array();
		// Loop through plugins
		foreach ( $network_plugins as $plugin_path ) {
			// Get plugin basename
			$plugin_base = plugin_basename( $plugin_path );
			// If the plugin basename is found in the active network plugin list
			if ( array_key_exists( $plugin_base, $network_active_plugins_option ) ) {
				// Get the plugin info and add it to the plugin list
				$plugin_info = get_plugin_data( $plugin_path );
				$network_active_plugins[] = $plugin_info['Name'] . ' v' . $plugin_info['Version'];
			}
		}
	} else {
		// Otherwise, indicate that the site is not a multisite installation
		$network_active_plugins = 'Not multisite';
	}


	// Detect add-ons
	$addons = array();
	if ( defined( 'WPRSS_C_VERSION' ) ) {
		$addons[] = 'Categories';
	}
	if ( defined( 'WPRSS_ET_VERSION' ) ) {
		$addons[] = 'Excerpts & Thumbnails';
	}
	if ( defined( 'WPRSS_KF_VERSION' ) ) {
		$addons[] = 'Keyword Filtering';
	}
	if ( defined( 'WPRSS_FTP_VERSION' ) ) {
		$addons[] = 'Feed to Post';
	}

	// Compile the data
	$data = array(
		'Site URL'					=>	base64_encode( $site_url ),
		'Plugin Version'			=>	$plugin_data['Version'],
		'Active Add-ons'			=>	$addons,
		'Theme Name'				=>	$theme_name,
		'Site Name'					=>	str_replace( ' ', '', get_bloginfo( 'name' ) ),
		'Plugin Count'				=>	count( get_option( 'active_plugins' ) ),
		'Active Plugins'			=>	$active_plugins,
		'Network Active Plugins'	=>	$network_active_plugins,
		'Inactive Plugins'			=>	$inactive_plugins,
		'WordPress Version'			=>	get_bloginfo( 'version' ),
	);

	// Send the data
	wp_remote_post(
		WPRSS_TRACKING_SERVER_URL,
		array(
			'method' => 'POST',
			'timeout' => 45,
			'redirection' => 5,
			'httpversion' => '1.0',
			'blocking' => true,
			'headers' => array(),
			'body' => array(
				'wprss_tracking_data'	=>	$data,
			),
			'cookies' => array()
		)
	);

	// Set a transient that expires in 1 week. When it expires, this function will run again
	// Expiration: 60secs * 60mins * 24hrs * 7days = 1 week
	set_transient( 'wprss_tracking_transient', '-', 60 * 60 * 24 * 7 );
}


/**
 * Shows a notice that notifies the user that the data report has been sent.
 * 
 * @since 1.0
 */
function wprss_tracking_notice() {
	?>
	<div class="updated">
		<?php echo wpautop( __( '<b>WP RSS Aggregator:</b> Data report sent!', WPRSS_TEXT_DOMAIN ) ) ?>
	</div>
	<?php
}