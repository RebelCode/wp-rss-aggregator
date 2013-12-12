<?php
    /**
     * System information
     *
     * @package    WPRSSAggregator
     * @subpackage Includes
     * @since      3.1
     * @author     Jean Galea <info@jeangalea.com>
     * @copyright  Copyright(c) 2012-2013, Jean Galea. Adapted from Easy Digital Downloads by Pippin Williamson
     * @link       http://www.wpmayor.com
     * @license    http://www.gnu.org/licenses/gpl.html
     */

    /**
     * Generate the system information
     * 
     * @since 3.1
     */ 
	function wprss_system_info() {
		global $wpdb;

		if ( ! class_exists( 'Browser' ) )
			require_once WPRSS_DIR . 'includes/libraries/browser.php';

		$browser =  new Browser();
	?>
			<h3><?php _e( 'System Information', 'wprss' ) ?></h3>
			<form action="<?php echo esc_url( admin_url( 'edit.php?post_type=wprss_feed&page=wprss-debugging' ) ); ?>" method="post">
				<textarea readonly="readonly" onclick="this.focus();this.select()" id="system-info-textarea" name="wprss-sysinfo" title="<?php _e( 'To copy the system info, click below then press Ctrl + C (PC) or Cmd + C (Mac).', 'wprss' ); ?>">
### Begin System Info ###

## Please include this information when posting support requests ##

Multi-site:               <?php echo is_multisite() ? 'Yes' . "\n" : 'No' . "\n" ?>

SITE_URL:                 <?php echo site_url() . "\n"; ?>
HOME_URL:                 <?php echo home_url() . "\n"; ?>

Plugin Version:           <?php echo WPRSS_VERSION . "\n"; ?>
WordPress Version:        <?php echo get_bloginfo( 'version' ) . "\n"; ?>

<?php echo $browser ; ?>

PHP Version:              <?php echo PHP_VERSION . "\n"; ?>
MySQL Version:            <?php echo mysql_get_server_info() . "\n"; ?>
Web Server Info:          <?php echo $_SERVER['SERVER_SOFTWARE'] . "\n"; ?>

PHP Safe Mode:            <?php echo ini_get( 'safe_mode' ) ? "Yes" : "No\n"; ?>
PHP Memory Limit:         <?php echo ini_get( 'memory_limit' ) . "\n"; ?>
PHP Post Max Size:        <?php echo ini_get( 'post_max_size' ) . "\n"; ?>
PHP Time Limit:           <?php echo ini_get( 'max_execution_time' ) . "\n"; ?>

WP_DEBUG:                 <?php echo defined( 'WP_DEBUG' ) ? WP_DEBUG ? 'Enabled' . "\n" : 'Disabled' . "\n" : 'Not set' . "\n" ?>

WP Table Prefix:          <?php echo "Length: ". strlen( $wpdb->prefix ); echo " Status:"; if ( strlen( $wpdb->prefix )>16 ) {echo " ERROR: Too Long";} else {echo " Acceptable";} echo "\n"; ?>

Show On Front:            <?php echo get_option( 'show_on_front' ) . "\n" ?>
Page On Front:            <?php $id = get_option( 'page_on_front' ); echo get_the_title( $id ) . ' #' . $id . "\n" ?>
Page For Posts:           <?php $id = get_option( 'page_on_front' ); echo get_the_title( $id ) . ' #' . $id . "\n" ?>

Session:                  <?php echo isset( $_SESSION ) ? 'Enabled' : 'Disabled'; ?><?php echo "\n"; ?>
Session Name:             <?php echo esc_html( ini_get( 'session.name' ) ); ?><?php echo "\n"; ?>
Cookie Path:              <?php echo esc_html( ini_get( 'session.cookie_path' ) ); ?><?php echo "\n"; ?>
Save Path:                <?php echo esc_html( ini_get( 'session.save_path' ) ); ?><?php echo "\n"; ?>
Use Cookies:              <?php echo ini_get( 'session.use_cookies' ) ? 'On' : 'Off'; ?><?php echo "\n"; ?>
Use Only Cookies:         <?php echo ini_get( 'session.use_only_cookies' ) ? 'On' : 'Off'; ?><?php echo "\n"; ?>

UPLOAD_MAX_FILESIZE:      <?php if ( function_exists( 'phpversion' ) ) echo ( wprss_let_to_num( ini_get( 'upload_max_filesize' ) )/( 1024*1024 ) )."MB"; ?><?php echo "\n"; ?>
POST_MAX_SIZE:            <?php if ( function_exists( 'phpversion' ) ) echo ( wprss_let_to_num( ini_get( 'post_max_size' ) )/( 1024*1024 ) )."MB"; ?><?php echo "\n"; ?>
WordPress Memory Limit:   <?php echo ( wprss_let_to_num( WP_MEMORY_LIMIT )/( 1024*1024 ) )."MB"; ?><?php echo "\n"; ?>
DISPLAY ERRORS:           <?php echo ( ini_get( 'display_errors' ) ) ? 'On (' . ini_get( 'display_errors' ) . ')' : 'N/A'; ?><?php echo "\n"; ?>
FSOCKOPEN:                <?php echo ( function_exists( 'fsockopen' ) ) ? __( 'Your server supports fsockopen.', 'wprss' ) : __( 'Your server does not support fsockopen.', 'wprss' ); ?><?php echo "\n"; ?>

ACTIVE PLUGINS:

<?php
$plugins = get_plugins();
$active_plugins = get_option( 'active_plugins', array() );

foreach ( $plugins as $plugin_path => $plugin ):
	// If the plugin isn't active, don't show it.
	if ( ! in_array( $plugin_path, $active_plugins ) ) {
		$inactive_plugins[] = $plugin;
		continue;
	}

echo $plugin['Name']; ?>: <?php echo $plugin['Version'] ."\n";

endforeach; 

if ( is_multisite() ) :
?>

NETWORK ACTIVE PLUGINS:

<?php
$plugins = wp_get_active_network_plugins();
$active_plugins = get_site_option( 'active_sitewide_plugins', array() );

foreach ( $plugins as $plugin_path ) {
	$plugin_base = plugin_basename( $plugin_path );

	// If the plugin isn't active, don't show it.
	if ( ! array_key_exists( $plugin_base, $active_plugins ) )
		continue;

	$plugin = get_plugin_data( $plugin_path );

	echo $plugin['Name'] . ': ' . $plugin['Version'] ."\n";
}

endif; 

if ( ! is_multisite() ) : ?>

DEACTIVATED PLUGINS:

<?php

foreach ( $inactive_plugins as $inactive_plugin ):

echo $inactive_plugin['Name']; ?>: <?php echo $inactive_plugin['Version'] ."\n";

endforeach; 

endif; ?>

CURRENT THEME:

<?php
if ( get_bloginfo( 'version' ) < '3.4' ) {
	$theme_data = get_theme_data( get_stylesheet_directory() . '/style.css' );
	echo $theme_data['Name'] . ': ' . $theme_data['Version'];
} else {
	$theme_data = wp_get_theme();
	echo $theme_data->Name . ': ' . $theme_data->Version;
}
?>


### End System Info ###
				</textarea>
				<p class="submit">
					<input type="hidden" name="wprss-action" value="download_sysinfo" />
					<?php submit_button( __( 'Download System Info File', 'wprss' ), 'primary', 'wprss-download-sysinfo', false ); ?>
				</p>
			</form>
		
	<?php
	}

	/**
	 * Generates the System Info Download File
	 *
	 * @since 3.1
	 * @return void
	 */
	function wprss_generate_sysinfo_download() {
		nocache_headers();

		header( "Content-type: text/plain" );
		header( 'Content-Disposition: attachment; filename="wprss-system-info.txt"' );

		echo wp_strip_all_tags( $_POST['wprss-sysinfo'] );
		exit;
	}
	add_action( 'wprss_download_sysinfo', 'wprss_generate_sysinfo_download' );