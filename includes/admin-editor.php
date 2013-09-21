<?php
	/**
	 * This file contains code related to the custom button added to Wordpress' TinyMCE editor.
	 *
	 * @since 3.5
	 */



	add_action('init', 'wprss_add_editor_button');
	/**
	 * Adds the WPRSS button to WordPress' editor
	 *
	 * @since 3.5 
	 */	
	function wprss_add_editor_button() {
		if ( !current_user_can( 'edit_posts' ) && !current_user_can( 'edit_pages' ) )
			return;
		if ( get_user_option( 'rich_editing' ) == 'true') {
			add_filter('mce_external_plugins', 'wprss_register_tinymce_plugin');
			add_filter('mce_buttons', 'wprss_register_tinymce_button');
		}
	}


	/**
	 * Adds a separator and the wprss button to the buttons array.
	 *
	 * @since 3.5
	 */
	function wprss_register_tinymce_button($buttons) {
		array_push( $buttons, "|", "wprss" );
		return $buttons;
	}


	/**
	 * Adds the button action JS file to TinyMCE's plugin list
	 *
	 * @since 3.5
	 */
	function wprss_register_tinymce_plugin($plugin_array) {
		$plugin_array['wprss'] = WPRSS_JS . 'editor.js';
		return $plugin_array;
	}


	add_filter( 'tiny_mce_version', 'wprss_tinymce_version');
	/**
	 * Intercepts TinyMCE's version check and increments its version by 3.
	 *
	 * This is a hack used to work around TinyMCE's caching, that might prevent the
	 * new wprss button from appearing on the editor.
	 *
	 * @since 3.5
	 */
	function wprss_tinymce_version($ver) {
		$ver += 3;
		return $ver;
	}