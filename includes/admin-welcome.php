<?php

	/**
	 * @todo Localize
	 */

	// Exit if the page is accessed directly
	if ( ! defined( 'ABSPATH' ) ) exit;


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
	
?>

	 <div class="wrap about-wrap">
			<h1><?php printf( __( 'Welcome to WP RSS Aggregator %s !', 'wprss' ), WPRSS_VERSION ); ?></h1>
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

								<h3>Feed Sources Page Visual Updated!</h3>

								<div class="feature-section col three-col">

									<div class="col-1">
										<img src="<?php echo WPRSS_IMG;?>welcome-page/spinning-icon.gif" />
										<h4>Live Updates</h4>
										<p>
											Your feed sources page now shows you new information as soon as it's available.
											With the new live updates, you no longer have to refresh the page to check for updates and changes.
										</p>
									</div>

									<div class="col-2">
										<img src="<?php echo WPRSS_IMG;?>welcome-page/updates.png" />
										<h4>More Information</h4>
										<p>
											We've renamed the <strong>Next Update</strong> column to <strong>Updates</strong>,
											and added to it <strong>two</strong> new fields, showing you the last time
											your feed source was updated, and how many items it imported.
										</p>
									</div>

									<div class="col-3 last-feature">
										<img src="<?php echo WPRSS_IMG;?>welcome-page/view-items.png" />
										<h4>View Items</h4>
										<p>
											The new <strong>View items</strong> row action link lets you view the feed items for that feed source
											alone, separate from the rest of your imported feed items. We've also cleaned up the rest of the row actions.
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
									</ul>
								</p>
								<p>More information about add-ons can be found on our website <a href="http://www.wprssaggregator.com">www.wprssaggregator.com</a></p>

		 						<hr/>

								<h3>Changelog for v<?php echo WPRSS_VERSION; ?></h3>
								<ul>
									<li><strong>Enhanced:</strong> Improved live updating performace on the Feed Sources page.</li>
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

			<p><a href="<?php echo admin_url( 'edit.php?post_type=wprss_feed&page=wprss-aggregator-settings'); ?>">Go to WP RSS Aggregator settings</a></p>

	</div>

<?php update_option( 'wprss_pwsv', WPRSS_VERSION ); // Update the previous welcome screen version ?>
