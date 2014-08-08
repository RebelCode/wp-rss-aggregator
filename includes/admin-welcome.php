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

	$settings_url = admin_url( 'edit.php?post_type=wprss_feed&page=wprss-aggregator-settings');
	
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

								<h2 class="about-headline-callout">New Author Importing!</h2>
								<div class="about-overview">
									<img src="<?php echo WPRSS_IMG;?>welcome-page/authors.png" />
									<h4>Author Names for Feed Items</h4>
									<p>
										Show the name of the author for each feed item you import!
										<br/>
										Perfect for giving attribution to the original author of an article.
										<br/>
										Head over to the
										<a href="<?php echo $settings_url; ?>">settings page</a>
										to enable the option and start importing authors names!
										<br/>
										More options soon!
									</p>
								</div>
								
								
								<h2 class="about-headline-callout">New Pagination Options</h2>
								<div class="feature-section col two-col">
									<div class="col-1">
										<img src="<?php echo WPRSS_IMG;?>welcome-page/default-pagination.png" />
										<h4>Default Pagination</h4>
										<p>
											The default pagination from the previous versions of WP RSS Aggregator,
											showing links for <strong>Older Posts</strong> and <strong>Newer Posts</strong>.
										</p>
									</div>
									<div class="col-2 last-feature">
										<img src="<?php echo WPRSS_IMG;?>welcome-page/numbered-pagination.png" />
										<h4>Numbered Pagination</h4>
										<p>
											The new numbered pagination - Show <strong>Next</strong> and <strong>Previous</strong> links,
											along with links for each page of feed items.
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
									<li><strong>Enhanced:</strong> Better wording on settings page.</li>
									<li><strong>Fixed bug:</strong> The <strong>Links Behaviour</strong> option in the settings was not working.</li>
									<li><strong>Fixed bug:</strong> The wrong feed items were being shown for some sources when using the "View Items" row action.</li>
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
