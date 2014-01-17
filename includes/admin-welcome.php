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
								Check out our add-ons:</p> 

								<ul>
									<li><strong><a href="http://www.wprssaggregator.com/extension/feed-post/" target="wprss_ftp">Feed to Post</a> <!--<span style="color: green;">*New*</span></strong>--></li>
									<li><strong><a href="http://www.wprssaggregator.com/extension/excerpts-thumbnails/"  target="wprss_et">Excerpts &amp; Thumbnails</a></strong></li>
									<li><strong><a href="http://www.wprssaggregator.com/extension/categories/" target="wprss_cat">Categories</a></strong></li>
									<li><strong><a href="http://www.wprssaggregator.com/extension/keyword-filtering/" target="wprss_kf">Keyword Filtering</a></strong></li>
								</ul>
							</p>
							<p>Need functionality not already available in core or the add-ons? You <a href="http://www.wprssaggregator.com/feature-requests/">suggest new features</a>!</p>
							<p>More information about add-ons can be found on our website <a href="http://www.wprssaggregator.com">www.wprssaggregator.com</a></p>



							<h3>Changelog for v<?php echo WPRSS_VERSION; ?></h3>
							<ul>
								<li>Fixed bug: Bug in admin-debugging.php causing trouble with admin login</li>
							</ul>


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