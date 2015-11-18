<?php
	/**
	 * Adds a dashboard page for the admin
	 * It is triggered on plugin activation and upon
	 * update.
	 *
	 * @since 3.3
	 */


	// Exit if the page is accessed directly
	if ( ! defined( 'ABSPATH' ) ) exit;


	add_action( 'admin_menu', 'wprss_admin_menu' );
	/**
	 * Adds a dashboard page.
	 * Usde to add the Welcome Page to the dashboard.
	 *
	 * @since 3.3
	 */
	function wprss_admin_menu() {
		// Welcome Page
		add_dashboard_page(
			__( 'Welcome to WP RSS Aggregator', WPRSS_TEXT_DOMAIN ),
			__( 'Welcome to WP RSS Aggregator', WPRSS_TEXT_DOMAIN ),
			'manage_options',
			'wprss-welcome',
			'wprss_show_welcome_screen'
		);

	}


	/**
	 * Callback for the Welcome Dashboard page.
	 * It merely includes the contents of the admin-welcome.php file,
	 * which contains the markup for the welcome screen.
	 *
	 * @since 3.3
	 */
	function wprss_show_welcome_screen() {
		include_once( 'admin-welcome.php' );
	}


	add_action( 'admin_init', 'wprss_welcome' );
	/**
	 * Detects an activation and redirects the user to
	 * the welcome page.
	 *
	 * @since 3.3
	 */
	function wprss_welcome() {
		// Bail if no activation redirect
		if ( ! get_transient( '_wprss_activation_redirect' ) )
			return;

		// Delete the redirect transient
		delete_transient( '_wprss_activation_redirect' );

		// Bail if activating from network, or bulk
		if ( is_network_admin() || isset( $_GET['activate-multi'] ) )
			return;

		wp_safe_redirect( admin_url( 'index.php?page=wprss-welcome' ) );
		exit;
	}


	add_action( 'admin_head', 'wprss_admin_head' );
	/**
	 * Removes the dashboard welcome page from the dashboard
	 * menu, and adds some styles for the welcome page.
	 *
	 * @since 3.3
	 */
	function wprss_admin_head() {
		remove_submenu_page( 'index.php', 'wprss-welcome' );
		?>
		<style type="text/css" media="screen">
		/*<![CDATA[*/

			/*.wprss-welcome-table > tbody > tr > td {
				line-height: 30px;
			}
			.wprss-welcome-table > tbody > tr {
				font-size: 1.3em;
				font-weight: normal;
			}
			.wprss-welcome-table > thead > tr > th{
				font-size: 2em;
				border-bottom: 8px solid #999;
			}*/

		/*]]>*/
		</style>
		<?php
	}

	add_filter( 'admin_footer_text', 'wprss_admin_footer' );
	/**
	 * Adds footer text on the plugin pages.
	 *
	 * @param  string $footer The footer text to filter
	 * @return string         The filtered footer text with added plugin text, or the param
	 *                        value if the page is not specific to the plugin.
	 */
	function wprss_admin_footer( $footer ) {
		// Current post type
		global $typenow;
		// Check if type is a plugin type. If not, stop
		// Plugin type is in the form 'wprss_*'' where * is 'feed', 'blacklist', etc)
		if ( stripos( $typenow, 'wprss_' ) !== 0 )
			return $footer;
		// Prepare fragments of the message
		$thank_you = sprintf(
			__( 'Thank you for using <a href="%1$s" target="_blank">WP RSS Aggregator</a>!', WPRSS_TEXT_DOMAIN ),
			'http://www.wprssaggregator.com/'
		);
		$rate_us = sprintf(
			__( 'Please <a href="%1$s" target="_blank">rate us</a>!', WPRSS_TEXT_DOMAIN ),
			'https://wordpress.org/support/view/plugin-reviews/wp-rss-aggregator?filter=5#postform'
		);
		// Return the final text
		return sprintf( '%1$s | <span class="wp-rss-footer-text">%2$s %3$s</span>', $footer, $thank_you, $rate_us );
	}
