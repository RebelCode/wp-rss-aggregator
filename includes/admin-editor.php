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






	add_action( 'wp_ajax_wprss_editor_dialog', 'wprss_return_dialog_contents' );
	/**
	 *
	 *
	 */
	function wprss_return_dialog_contents() {
		$feed_sources = get_posts( array(
			'post_type'			=> 'wprss_feed',
			'post_status'		=> 'publish',
			'posts_per_page'	=> -1,
			'no_found_rows'		=>	true
		));
		$feed_sources_select = '<select id="wprss-dialog-feed-source-list" multiple>';
		$feed_sources_exclude_select = '<select id="wprss-dialog-exclude-list" multiple>';
		$feed_sources_both_select = '';
		foreach ( $feed_sources as $source ) {
			$feed_sources_both_select .= '<option value="' . $source->ID . '" >' . $source->post_title . '</option>';
		}
		$feed_sources_both_select .= '</select><p>Hold Ctrl or Mac Command key when clicking to select more than one feed source.</p>';
		
		$feed_sources_select .= $feed_sources_both_select;
		$feed_sources_exclude_select .= $feed_sources_both_select;

		?>
		<table cellspacing="20">
			<tbody>

				<tr>
					<td id="wprss-dialog-all-sources-label">Feed Sources</td>
					<td>
						<input id="wprss-dialog-all-sources" type="checkbox" checked> <label for="wprss-dialog-all-sources">All feed sources</label>
						<div id="wprss-dialog-sources-container" style="display:none">
							<p>Choose the feed source to display:</p>
							<?php echo $feed_sources_select; ?>
						</div>
						<script>
							jQuery('#wprss-dialog-all-sources').click( function(){
								if ( jQuery(this).is(':checked') ) {
									jQuery( '#wprss-dialog-sources-container' ).hide();
									jQuery( '#wprss-dialog-exclude-row' ).show();
									jQuery( '#wprss-dialog-all-sources-label' ).css('vertical-align', 'middle');
								} else {
									jQuery( '#wprss-dialog-sources-container' ).show();
									jQuery( '#wprss-dialog-exclude-row' ).hide();
									jQuery( '#wprss-dialog-all-sources-label' ).css('vertical-align', 'top');
								}
							});
							jQuery('#wprss-dialog-submit').click( wprss_dialog_submit );
						</script>
					</td>
				</tr>

				<tr id="wprss-dialog-exclude-row">
					<td id="wprss-dialog-exclude-label">Exclude:</td>
					<td>
						<p>Choose the feed sources to exclude:</p>
						<?php echo $feed_sources_exclude_select; ?>
					</td>
				</tr>

				<tr>
					<td>Feed Limit:</td>
					<td> <input id="wprss-dialog-feed-limit" type="number" class="wprss-number-roller" placeholder="Ignore" min="0" /> </td>
				</tr>

				<?php do_action( 'wprss_return_dialog_contents' ); ?>

				<tr>
					<td></td>
					<td>
						<button id="wprss-dialog-submit">Add shortcode</button>
					</td>
				</tr>

			</tbody>
		</table>
		<?php
		die();
	}