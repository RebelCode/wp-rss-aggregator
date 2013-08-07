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
			<div class="about-text">
				Thank you for upgrading to the latest version! 
				WP RSS Aggregator 3.3 is more powerful than ever before, and you can now check out the new add-ons!<br>
			</div>
			<!-- <div class="wprss-badge">Version</div>-->

			<!-- TAB WRAPPER -->
			<h2 class="nav-tab-wrapper">
				<a class="nav-tab <?php if ( $tab === null ) echo 'nav-tab-active'; ?>"
					href="<?php echo esc_url( admin_url( add_query_arg( array( 'page' => 'wprss-welcome' ), 'index.php' ) ) ); ?>">
					Overview
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

							<p class="about-description">
								We have released three brand new add-ons to go along with v3.3:</p> 

								<ul>
									<li><strong><a href="http://www.wprssaggregator.com/extension/excerpts-thumbnails/">Excerpts & Thumbnails</a></strong></li>
									<li><strong><a href="http://www.wprssaggregator.com/extension/categories/">Categories</a></strong></li>
									<li><strong><a href="http://www.wprssaggregator.com/extension/keyword-filtering/">Keyword Filtering</a></strong></li>
								</ul>
							</p>
							<p>Plus we've got some other add-ons already being developed!</p>
							<p>More information about add-ons can be found on our new website <a href="http://www.wprssaggregator.com">www.wprssaggregator.com</a></p>

								<h3>Change log v3.3</h3>
								<ul>
									<li>New feature: OPML importer</li>
									<li>New feature: Feed item limits for individual Feed Sources</li>
									<li>New feature: Custom feed URL</li>
									<li>New feature: Feed limit on custom feed</li>
									<li>New feature: New 'Fetch feed items' action for each Feed Source in listing display</li>
									<li>New feature: Option to enable link to source</li>
									<li>Enhanced: Date strings now change according to locale being used (i.e. compatible with WPML)</li>
									<li>Enhanced: Capabilities implemented</li>
									<li>Enhanced: Feed Sources row action 'View' removed</li>
									<li>Fixed Bug: Proxy feed URLs resulting in the permalink: example.com/url</li>
								</ul>
							</p>

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