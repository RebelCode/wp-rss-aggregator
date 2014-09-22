<?php

	/**
	 * @todo Localize
	 */

	// Exit if the page is accessed directly
	if ( ! defined( 'ABSPATH' ) ) exit;


	/**
	 * Parses the changelog, and returns an array of the last version entry.
	 * 
	 * @since 4.4
	 * @return array
	 */
	function wprss_parse_changelog() {
		// Read changelog file
		$contents = file_get_contents( WPRSS_DIR . 'changelog.txt' );
		// Split into lines and remove first line
		$lines = explode( "\n", $contents );
		unset($lines[0]);
		
		// Lines chosen for last changelog entry i.e. lines until an empty line is encountered
		$chosen = array();
		// Iterate the lines
		foreach( $lines as $line ) {
			// if the line is empty, stop iterating
			if ( trim($line) == '' ) {
				break;
			}
			// otherwise, add it to chosen
			$chosen[] = $line;
		}
		
		$final = array();
		// Iterate lines
		foreach( $chosen as $line ) {
			// Split by colon
			$colon = strpos( $line, ":" );
			// Get the type (New Feature, Enhanced, Fixed Bug)
			$type = trim( substr( $line, 0, $colon ) );
			// Get the description
			$desc = trim( substr( $line, $colon + 1 ) );
			// Add it to the final array
			$final[] = array(
				'type'	=>	$type,
				'desc'	=>	$desc
			);
		}
		
		// Return the final array
		return $final;
	}


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
			<h1><?php printf( __( 'Welcome to WP RSS Aggregator %s !', WPRSS_TEXT_DOMAIN ), WPRSS_VERSION ); ?></h1>
			<div class="wprss-about-text">
				Thank you for upgrading to the latest version! 
			</div>
			<!-- <div class="wprss-badge">Version</div>-->

			<!-- TAB WRAPPER -->
			<h2 class="nav-tab-wrapper">
				<a class="nav-tab <?php if ( $tab === null ) echo 'nav-tab-active'; ?>"
					href="<?php echo esc_url( admin_url( add_query_arg( array( 'page' => 'wprss-welcome' ), 'index.php' ) ) ); ?>">
					What's New?
				</a>

				<!-- SHOW ALL TABS -->
				<?php foreach ($tabs as $slug => $title) : ?>

					<a class="nav-tab <?php if ( $tab === $slug ) echo 'nav-tab-active'; ?>"
						href="<?php echo esc_url( admin_url( add_query_arg( array( 'page' => 'wprss-welcome', 'tab' => $slug ), 'index.php' ) ) ); ?>">
						<?php echo $title; ?>
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

								<h2 class="about-headline-callout">Bulk Adding Feed Sources</h2>
								<div class="about-overview">
									<img src="<?php echo WPRSS_IMG;?>welcome-page/bulk-add.png" />
									<p>
										The new bulk adding option saves you time by allowing you to enter your feed names and URLs
										all at once.
										<br/>
										Simply type in or paste your feed sources, and with the press of a button, your feed sources will instantly be created!
										<br/>
										Try it now from the 
										<a href="<?php echo admin_url('edit.php?post_type=wprss_feed&page=wprss-import-export-settings'); ?>">Import &amp; Export</a>
										page.
									</p>
								</div>
								
								<h2 class="about-headline-callout">Feed Item Blacklist</h2>
								<div class="feature-section col three-col">
									<div class="col-1">
										<img src="<?php echo WPRSS_IMG;?>welcome-page/trash-feed-item.png" />
										<h4>Trash undesired items</h4>
										<p>
											Did a feed import an item that you do not wish to keep? Up till now, <strong>WP RSS Aggregator</strong>
											only allowed you to trash the item and keep it in your trash.
										</p>
									</div>
									<div class="col-2">
										<img src="<?php echo WPRSS_IMG;?>welcome-page/blacklist-feed-item.png" />
										<h4>Blacklist Trashed Items</h4>
										<p>
											Permanently deleting the item will cause it to be re-imported.
											Using the new <strong>Delete Permanently &amp; Blacklist</strong> option, the feed item is deleted
											and added to the <strong>Blacklist</strong>.
										</p>
									</div>
									<div class="col-3 last-feature">
										<img src="<?php echo WPRSS_IMG;?>welcome-page/blacklist.png" />
										<h4>The Blacklist</h4>
										<p>
											This is your list of unwanted feed item links. Any item in this list will be ignored by
											<strong>WP RSS Aggregator</strong> in the future, meaning it won't be imported from any of your
											feed sources.
										</p>
									</div>
								</div>

								<hr/>

								<h3>Check out our add-ons:</h3>

									<ul>
										<li><strong><a href="http://www.wprssaggregator.com/extension/feed-post/" target="wprss_ftp">Feed to Post</a></strong></li>
										<li><strong><a href="http://www.wprssaggregator.com/extension/excerpts-thumbnails/"  target="wprss_et">Excerpts &amp; Thumbnails</a></strong></li>
										<li><strong><a href="http://www.wprssaggregator.com/extension/categories/" target="wprss_cat">Categories</a></strong></li>
										<li><strong><a href="http://www.wprssaggregator.com/extension/keyword-filtering/" target="wprss_kf">Keyword Filtering</a></strong></li>
										<li><strong><a href="http://www.wprssaggregator.com/extension/wordai/" target="wprss_ai">WordAi</a></strong></li>
									</ul>
								</p>
								<p>More information about add-ons can be found on our website <a href="http://www.wprssaggregator.com">www.wprssaggregator.com</a></p>

		 						<hr/>

		 						<h3>Changelog for v<?php echo WPRSS_VERSION; ?></h3>
		 						<ul>
									<?php // CHANGELOG
										$changelog = wprss_parse_changelog();
										foreach( $changelog as $entry ): ?>
											<li><strong><?php echo $entry['type']; ?></strong>: <?php echo $entry['desc']; ?></li>
									<?php endforeach; ?>
		 						</ul>
		 						
								<p>Need functionality not already available in core or the add-ons? You can <a href="http://www.wprssaggregator.com/feature-requests/">suggest new features</a>!</p>

							</div>

						<?php
						break;

					// Excerpts and Thumbnails tab
					case 'et': ?>

							<p class="about-description">
								Fetch RSS feed excerpts to your blog and add thumbnails! Perfect for adding some life and color to your feeds.
							</p>

						<?php
						break;

					// Categories Tab
					case 'cat': ?>

							<p class="about-description">
								Organize your feeds into custom categories. Filter feed items by category and make custom WordPress feeds for specific categories.
							</p>

						<?php
						break;

					// Keyword Filtering tab
					case 'kf': ?>

							<p class="about-description">
								Import and store feeds that contain specific keywords in either the title or their content. Control what gets imported to your blog.
							</p>

						<?php
						break;
				}
			?>

			<hr/>

			<p><a href="<?php echo $settings_url; ?>">Go to WP RSS Aggregator settings</a></p>

	</div>

<?php update_option( 'wprss_pwsv', WPRSS_VERSION ); // Update the previous welcome screen version ?>
