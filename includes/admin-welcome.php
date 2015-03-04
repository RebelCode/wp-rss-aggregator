<?php

	/**
	 * @todo Localize
	 */

	// Exit if the page is accessed directly
	if ( ! defined( 'ABSPATH' ) ) exit;
	
	// The readme lib
	require_once( WPRSS_INC . '/readme.php' );


	// The tabs to be shown
	$tabs = array(
	/*	'cat'	=>	'Categories',
		'et'	=>	'Excerpts &amp; Thumbnails',
		'kf'	=>	'Keyword Filtering'*/
	);

	// Determine the tab currently being shown
	$tab = null;
	if ( isset( $_GET['tab'] ) && !empty( $_GET['tab'] ) ) {
		$tab = $_GET['tab'];
	}

	$settings_url = admin_url( 'edit.php?post_type=wprss_feed&page=wprss-aggregator-settings');
	
?>

	 <div class="wrap about-wrap">
			<h1><?php printf( __( 'Welcome to WP RSS Aggregator %1$s!', WPRSS_TEXT_DOMAIN ), WPRSS_VERSION ); ?></h1>
			<div class="wprss-about-text">
				<?php _e( 'Thank you for upgrading to the latest version!', WPRSS_TEXT_DOMAIN ) ?>
			</div>
			<!-- <div class="wprss-badge">Version</div>-->

			<!-- TAB WRAPPER -->
			<h2 class="nav-tab-wrapper">
				<!--<a class="nav-tab <?php if ( $tab === null ) echo 'nav-tab-active'; ?>"
					href="<?php echo esc_url( admin_url( add_query_arg( array( 'page' => 'wprss-welcome' ), 'index.php' ) ) ); ?>">
					<?php _e( "What's New?", WPRSS_TEXT_DOMAIN ) ?>
				</a>-->

				<!-- SHOW ALL TABS -->
				<?php foreach ($tabs as $slug => $title) : ?>

					<a class="nav-tab <?php if ( $tab === $slug ) echo 'nav-tab-active'; ?>"
						href="<?php echo esc_url( admin_url( add_query_arg( array( 'page' => 'wprss-welcome', 'tab' => $slug ), 'index.php' ) ) ); ?>">
						<?php _e( $title, WPRSS_TEXT_DOMAIN ) ?>
					</a>

				<?php endforeach; ?>

			</h2>

			<!-- TAB CONTENT -->
			<?php
				/* Show content depending on the current tab */
				switch( $tab ) {

					// Default tab. ( when tab = null )
					default: ?>
		 					<div class="changelog">

								<!--<h2 class="about-headline-callout"><?php _e( 'Bulk Adding Feed Sources', WPRSS_TEXT_DOMAIN ) ?></h2>
								<div class="about-overview">
									<img src="<?php echo WPRSS_IMG; ?>welcome-page/bulk-add.png" />
									<?php echo wpautop( sprintf( __('The new bulk adding option saves you time by allowing you to enter your feed names and URLs all at once.
										Simply type in or paste your feed sources, and with the press of a button, your feed sources will instantly be created!
										Try it now from the <a href="%1$s">Import &amp; Export</a> page.', WPRSS_TEXT_DOMAIN), 'edit.php?post_type=wprss_feed&page=wprss-import-export-settings' ) ) ?>
								</div>
								
								<h2 class="about-headline-callout"><?php _e( 'Feed Item Blacklist', WPRSS_TEXT_DOMAIN ) ?></h2>
								<div class="feature-section col three-col">
									<div class="col-1">
										<img src="<?php echo WPRSS_IMG;?>welcome-page/trash-feed-item.png" />
										<h4><?php _e( 'Trash undesired items', WPRSS_TEXT_DOMAIN ) ?></h4>
										<?php echo wpautop( sprintf( __('Did a feed import an item that you do not wish to keep? Up till now, <strong>WP RSS Aggregator</strong>'
												. 'only allowed you to trash the item and keep it in your trash.', WPRSS_TEXT_DOMAIN) ) ) ?>
									</div>
									<div class="col-2">
										<img src="<?php echo WPRSS_IMG;?>welcome-page/blacklist-feed-item.png" />
										<h4><?php _e( 'Blacklist Trashed Items', WPRSS_TEXT_DOMAIN ) ?></h4>
										<?php echo wpautop( sprintf( __('Permanently deleting the item will cause it to be re-imported. '
											. 'Using the new <strong>Delete Permanently &amp; Blacklist</strong> option, the feed item is deleted '
											. 'and added to the <strong>Blacklist</strong>.', WPRSS_TEXT_DOMAIN) ) ) ?>
									</div>
									<div class="col-3 last-feature">
										<img src="<?php echo WPRSS_IMG;?>welcome-page/blacklist.png" />
										<h4><?php _e( 'The Blacklist', WPRSS_TEXT_DOMAIN ) ?></h4>
										<?php echo wpautop( sprintf( __('This is your list of unwanted feed item links. Any item in this list will be ignored by '
											. "<strong>WP RSS Aggregator</strong> in the future, meaning it won't be imported from any of your feed sources."
											. 'and added to the <strong>Blacklist</strong>.', WPRSS_TEXT_DOMAIN) ) ) ?>
									</div>
								</div>-->

								<hr/>

								<h3><?php _e( 'Check out our add-ons:', WPRSS_TEXT_DOMAIN ) ?></h3>

									<ul>
										<li><strong><a href="http://www.wprssaggregator.com/extension/feed-post/" target="wprss_ftp"><?php _e( 'Feed to Post', WPRSS_TEXT_DOMAIN ); ?></a></strong></li>
										<li><strong><a href="http://www.wprssaggregator.com/extension/excerpts-thumbnails/"  target="wprss_et"><?php _e( 'Excerpts &amp; Thumbnails', WPRSS_TEXT_DOMAIN ); ?></a></strong></li>
										<li><strong><a href="http://www.wprssaggregator.com/extension/categories/" target="wprss_cat"><?php _e( 'Categories', WPRSS_TEXT_DOMAIN ); ?></a></strong></li>
										<li><strong><a href="http://www.wprssaggregator.com/extension/keyword-filtering/" target="wprss_kf"><?php _e( 'Keyword Filtering', WPRSS_TEXT_DOMAIN ); ?></a></strong></li>
										<li><strong><a href="http://www.wprssaggregator.com/extension/full-text-rss-feeds/" target="wprss_kf"><?php _e( 'Full Text RSS Feeds', WPRSS_TEXT_DOMAIN ); ?></a></strong></li>
										<li><strong><a href="http://www.wprssaggregator.com/extension/wordai/" target="wprss_ai"><?php _e( 'WordAi', WPRSS_TEXT_DOMAIN ); ?></a></strong></li>
									</ul>
								</p>
								<?php echo wpautop( sprintf( __( 'More information about add-ons can be found on our website <a href="%1$s">%2$s</a>', WPRSS_TEXT_DOMAIN ), 'http://www.wprssaggregator.com', 'www.wprssaggregator.com' ) ) ?>

		 						<hr/>

								<?php $changelog = wprss_parse_changelog() ?>
								<?php if ( count( $changelog ) ): foreach( $changelog as $_version => $_changes_html ): ?>
		 						<h3><?php printf( __( 'Changelog for v%1$s', WPRSS_TEXT_DOMAIN ), $_version ) ?></h3>
									<div class="changelog-changeset" >
										<?php echo $_changes_html ?>
									</div>
								<?php break; endforeach; endif; ?>
								
		 						<?php echo wpautop( sprintf( __( 'Need functionality not already available in core or the add-ons? You can <a href="%1$s">suggest new features</a>!', WPRSS_TEXT_DOMAIN ), 'https://trello.com/b/UJJwpvZu/wp-rss-aggregator-public-roadmap' ) ) ?>

							</div>

						<?php
						break;

					// Excerpts and Thumbnails tab
					case 'et': ?>

							<p class="about-description">
								<?php _e( 'Fetch RSS feed excerpts to your blog and add thumbnails! Perfect for adding some life and color to your feeds.', WPRSS_TEXT_DOMAIN ) ?>
							</p>

						<?php
						break;

					// Categories Tab
					case 'cat': ?>

							<p class="about-description">
								<?php _e( 'Organize your feeds into custom categories. Filter feed items by category and make custom WordPress feeds for specific categories.', WPRSS_TEXT_DOMAIN ) ?>
							</p>

						<?php
						break;

					// Keyword Filtering tab
					case 'kf': ?>

							<p class="about-description">
								<?php _e( 'Import and store feeds that contain specific keywords in either the title or their content. Control what gets imported to your blog.', WPRSS_TEXT_DOMAIN ) ?>
							</p>

						<?php
						break;
				}
			?>

			<hr/>

			<p><a href="<?php echo $settings_url; ?>"><?php _e( 'Go to WP RSS Aggregator settings', WPRSS_TEXT_DOMAIN ) ?></a></p>

	</div>

<?php update_option( 'wprss_pwsv', WPRSS_VERSION ); // Update the previous welcome screen version ?>
