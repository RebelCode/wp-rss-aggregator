<?php
	/**
	 * This file contains code related to the custom button added to Wordpress' TinyMCE editor.
	 *
	 * @since 3.5
	 */



	add_action( 'admin_init', 'wprss_add_editor_button' );
	/**
	 * Adds the WPRSS button to WordPress' editor
	 *
	 * @since 3.5 
	 */	
	function wprss_add_editor_button() {
		if ( ! current_user_can( 'edit_posts' ) && !current_user_can( 'edit_pages' ) )
			return;
		if ( get_user_option( 'rich_editing' ) == 'true') {
			add_filter( 'mce_external_plugins', 'wprss_register_tinymce_plugin' );
			add_filter( 'mce_buttons', 'wprss_register_tinymce_button' );
		}
	}


	/**
	 * Adds a separator and the wprss button to the buttons array.
	 *
	 * @since 3.5
	 */
	function wprss_register_tinymce_button( $buttons ) {
		array_push( $buttons, "|", "wprss" );
		return $buttons;
	}


	/**
	 * Adds the button action JS file to TinyMCE's plugin list
	 *
	 * @todo add filter to skip showing the editor button
	 * @since 3.5
	 */
	function wprss_register_tinymce_plugin($plugin_array) {
		// add filter here
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
	 * Renders the TinyMCE button dialog contents.
	 */
	function wprss_return_dialog_contents() {
		$templates_collection = wpra_get('feeds/templates/collection');
		$templates_options = [];
		foreach ($templates_collection as $template) {
		    $template_name = $template['name'];
		    $template_slug = ($template['type'] === '__built_in')
                ? ''
                : $template['slug'];

		    $templates_options[$template_slug] = $template_name;
        }
		$templates_select = wprss_settings_render_select(
            'wprss-dialog-templates',
            '',
            $templates_options,
            '',
            ['class' => 'widefat']
        );

		$feed_sources = get_posts( array(
			'post_type'			=> 'wprss_feed',
			'post_status'		=> 'publish',
			'posts_per_page'	=> -1,
			'no_found_rows'		=>	true
		));
		$feed_sources_names = [];
        foreach ( $feed_sources as $source ) {
            $feed_sources_names[$source->ID] = $source->post_title;
        }
		$feed_sources_select = wprss_settings_render_select(
            'wprss-dialog-feed-source-list',
            '',
            $feed_sources_names,
            '',
            ['multiple' => 'multiple', 'class' => 'widefat']
        );
        $feed_sources_exclude_select = wprss_settings_render_select(
            'wprss-dialog-exclude-list',
            '',
            $feed_sources_names,
            '',
            ['multiple' => 'multiple', 'class' => 'widefat']
        );

		$feed_sources_both_select = '<p>' . __( 'To select more than one feed source, click and drag with your mouse pointer or click individual feed sources while holding down the Ctrl (Windows) or Command (Mac) key.' , WPRSS_TEXT_DOMAIN ) . '</p>';
		$feed_sources_select .= $feed_sources_both_select;
		$feed_sources_exclude_select .= $feed_sources_both_select;

		?>
		<table cellspacing="20">
			<tbody>
                <tr>
                    <td id="wprss-dialog-templates-label">
                        <label for="wprss-dialog-templates">
                            <?php _e( 'Template', WPRSS_TEXT_DOMAIN ) ?>
                        </label>
                    </td>
                    <td>
                        <?php echo $templates_select; ?>
                    </td>
                </tr>
				<tr>
					<td id="wprss-dialog-all-sources-label">
                        <?php _e( 'Sources', WPRSS_TEXT_DOMAIN ) ?>
                    </td>
					<td>
						<input id="wprss-dialog-all-sources" type="checkbox" checked>
                        <label for="wprss-dialog-all-sources">
                            <?php _e( 'All feed sources', WPRSS_TEXT_DOMAIN ) ?>
                        </label>
					</td>
				</tr>

				<tr id="wprss-dialog-exclude-row">
					<td id="wprss-dialog-exclude-label">
                        <label id="wprss-dialog-exclude-list-label" for="wprss-dialog-exclude-list">
                            <?php _e( 'Exclude', WPRSS_TEXT_DOMAIN ) ?>
                        </label>
                    </td>
					<td>
                        <div id="wprss-dialog-excludes-container">
						    <p><?php _e( 'You may choose to exclude some feed sources:', WPRSS_TEXT_DOMAIN ) ?></p>
						    <?php echo $feed_sources_exclude_select; ?>
                        </div>

                        <div id="wprss-dialog-sources-container" style="display:none">
                            <p><?php _e( 'Choose which feed sources to show:', WPRSS_TEXT_DOMAIN ) ?></p>
                            <?php echo $feed_sources_select; ?>
                        </div>

                        <script type="text/javascript">
                            jQuery('#wprss-dialog-all-sources').click( function(){
                                if ( jQuery(this).is(':checked') ) {
                                    jQuery( '#wprss-dialog-sources-container' ).hide();
                                    jQuery( '#wprss-dialog-excludes-container' ).show();
                                    jQuery( '#wprss-dialog-exclude-list-label' ).show();
                                } else {
                                    jQuery( '#wprss-dialog-sources-container' ).show();
                                    jQuery( '#wprss-dialog-excludes-container' ).hide();
                                    jQuery( '#wprss-dialog-exclude-list-label' ).hide();
                                }
                            });
                            jQuery('#wprss-dialog-submit').click( wprss_dialog_submit );
                        </script>
					</td>
				</tr>

				<tr>
					<td><?php _e( 'Number of items', WPRSS_TEXT_DOMAIN ) ?></td>
					<td>
                        <input id="wprss-dialog-feed-limit"
                               type="number"
                               class="wprss-number-roller widefat"
                               placeholder="<?php _e( 'Use template setting', WPRSS_TEXT_DOMAIN ) ?>"
                               min="0"
                        />
                    </td>
				</tr>

                <tr>
                    <td><?php _e( 'Pagination', WPRSS_TEXT_DOMAIN ) ?></td>
                    <td>
                        <label>
                            <select id="wprss-dialog-pagination">
                                <option value=""><?php _e( 'Use template setting', WPRSS_TEXT_DOMAIN ) ?></option>
                                <option value="on"><?php _e( 'Enabled', WPRSS_TEXT_DOMAIN ) ?></option>
                                <option value="off"><?php _e( 'Disabled', WPRSS_TEXT_DOMAIN ) ?></option>
                            </select>
                            <br/>
                            <span>
                                <?php _e( 'Choose whether to show or hide pagination controls', WPRSS_TEXT_DOMAIN ) ?>
                            </span>
                        </label>
                    </td>
                </tr>

                <tr>
                    <td><?php _e( 'Starting page', WPRSS_TEXT_DOMAIN ) ?></td>
                    <td>
                        <input id="wprss-dialog-start-page"
                               type="number"
                               class="wprss-number-roller widefat"
                               placeholder="<?php _e( 'Use template setting', WPRSS_TEXT_DOMAIN ) ?>"
                               min="1"
                        />
                    </td>
                </tr>

				<?php do_action( 'wprss_return_dialog_contents' ); ?>

				<tr>
					<td></td>
					<td>
						<button id="wprss-dialog-submit">
                            <?php _e( 'Add shortcode', WPRSS_TEXT_DOMAIN ) ?>
                        </button>
					</td>
				</tr>

			</tbody>
		</table>
		<?php
		die();
	}
