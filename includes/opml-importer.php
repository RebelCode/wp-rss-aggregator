<?php

	/**
	 * Define the WPRSS OPML Importer to set up the Import OPML Page
	 * and parse uploaded OPML files and import them into WPRSS'
	 * custom post types.
	 *
	 * @since 3.3
	 * @package WPRSSAggregator
	 */



	/*
	 * Check if the WP_Importer Class is already loaded.
	 * If not, load it.
	 */
	if ( !class_exists( 'WP_Importer' ) ) {
		$class_wp_importer = ABSPATH . 'wp-admin/includes/class-wp-importer.php';
		if ( file_exists( $class_wp_importer ) )
			require $class_wp_importer;
	}

	/**
	 * WPRSS_OPML_Importer Class handles the OPML Import Page and
	 * OPML file upload. It also constructs a WPRSS_OPML object
	 * for the uploaded file, which parses the file, and then
	 * imports it into WPRSS' custom post types.
	 *
	 * @since 3.3
	 */
	class WPRSS_OPML_Importer extends WP_Importer {
		
		/**
		 * The static Singleton Instance of the importer
		 */
		public static $singleton;

		/**
		 * The ID of the uploaded file to import
		 */
		private $id;


		/**
		 * Constructor: Prepares the Importer
		 */
		public function __contruct() {
			parent::__contruct();
		}



		/**
		 * Shows The Import Page and import form for step 1.
		 * Calls the parsing and importing function for step 2.
		 * 
		 * @since 3.3
		 * @return void
		 */
		public function opml_import() {
			// Show the Icon and Title
			echo '<div class="wrap">';
			screen_icon();
			echo '<h2>Import OPML</h2>';

			// Get the current step from URL query string
			$step = empty( $_GET['step'] ) ? 0 : (int) $_GET['step'];

			// Check the current step
			switch ( $step ) {
				default :
				case 0 :
					// Show the Import Message and the import upload form
					echo '<p>' . __( 'Howdy! Import your feeds here from an OPML (.xml) export file.', 'wprss' ) . '</p>';
					echo '<p>' . __( "Click the button below, choose your file, and click 'Upload'.", 'wprss' ) . '</p>';
					echo '<p>' . __( 'We will take care of the rest.', 'wprss' ) . '</p>';

					// Show an import upload form that submits to the same page, with GET parameter step=1
					wp_import_upload_form( 'admin.php?import=wprss_opml_importer&amp;step=1' );
					break;

				case 1:
					// Check referer
					check_admin_referer( 'import-upload' );
					// If the handle_upload function returns true
					if ( $this->handle_upload() ) {
						// Get the uploaded file
						$file = get_attached_file( $this->id );
						set_time_limit(0);
						// Parse the File and Import the feeds
						$this->parse_and_import( $file );
					}
					break;
			}
			
			echo '</div>';
		}



		private function handle_upload() {
			// Get the upload file
			$file = wp_import_handle_upload();

			// If the 'error' property is set, show the error message and return FALSE
			if ( isset( $file['error'] ) ) {
				echo '<p><strong>' . __( 'Sorry, an error has been encountered.', 'wprss' ) . '</strong><br />';
				echo esc_html( $file['error'] ) . '</p>';
				return false;
			// If the file does not exist, then show the error message and return FALSE
			} else if ( ! file_exists( $file['file'] ) ) {
				echo '<p><strong>' . __( 'Sorry, it seems your uploaded file has been misplaced!', 'wprss' ) . '</strong><br />';
				echo __( 'The uploaded file could not be found at ', 'wprss') . '<code>' . esc_html( $file['file'] ) . '</code>';
				echo __( 'It is likely that this was caused by a permissions problem.' , 'wprss'  );
				echo '</p>';
				return false;
			}


			$this->id = (int) $file['id'];
			return true;
		}

		
		/**
		 * Imports the give <outline> OPML element as a wprss_feed
		 *
		 * @since 3.3
		 * @param $outline The outline OPML element
		 */
		 private function import_opml_feed( $outline ) {
		 	// IF the necassary fields are not present in the element
		 	if ( !isset( $outline['title'] ) && !isset( $outline['xmlUrl'] ) ) {
		 		// Check if the element is an array
		 		if ( is_array( $outline ) ) {
		 			// Treat it as an array of sub <outline> elements
		 			$inserted_ids = array();
		 			// Insert all sub outline elements
		 			foreach ( $outline as $key => $sub_outline ) {
		 				$inserted_ids[] = $this->import_opml_feed( $sub_outline );
		 			}
		 			// Return the inserted IDs
		 			return $inserted_ids;
		 		}
		 		// IF not an array, return NULL
		 		else return NULL;
		 	}

		 	// Create an associative array, with the feed's properties
			$feed = array(
				'post_title' => $outline['title'],
				'post_content' => '',
				'post_status' => 'publish',
				'post_type' => 'wprss_feed'
			);
			// Insert the post into the database and store the inserted ID
			$inserted_id = wp_insert_post( $feed );
			// Update the post's meta
			update_post_meta( $inserted_id, 'wprss_url', $outline['xmlUrl'] );
			// Return inserted ID
			return $inserted_id;
		 }
		

		/**
		 * Attempts to parse the given file as an OPML construct, and
		 * import each found feed.
		 *
		 * @since 3.3
		 * @param file The OPML file to parse and import
		 * @return void
		 * @todo Use the parsed $opml object to import the feeds AND remove var_dump
		 */
		private function parse_and_import( $file ) {
			try {
				$opml = new WPRSS_OPML( $file );

				// Show Success Message				
				echo '<h3>Feeds were imported successfully!</h3>';

				// Show imported feeds
				?>
				<table class="widefat">
					<thead>
						<tr>
							<th>ID</th>
							<th>Title</th>
							<th>URL</th>
						</tr>
					</thead>
					
					<tbody>
						<?php
							foreach ( $opml->body as $opml_feed ) :
								$inserted_ids = $this->import_opml_feed( $opml_feed );
								if ( !is_array( $inserted_ids ) ) {
									$inserted_ids = array( $inserted_ids );
								}
								foreach ( $inserted_ids as $inserted_id ) :
									if ( $inserted_id !== NULL ) :
										$imported_feed = get_post( $inserted_id, 'ARRAY_A' );
									?>

										<tr>
											<td><?php echo $inserted_id; ?></td>
											<td><?php echo $imported_feed['post_title']; ?> </td>
											<td><?php echo get_post_meta( $inserted_id, 'wprss_url', TRUE ); ?></td>
										</tr>

								<?php endif; ?>
							<?php endforeach; ?>
						<?php endforeach; ?>
					</tbody>
					
					<tfoot>
						<tr>
							<th>ID</th>
							<th>Title</th>
							<th>URL</th>
						</tr>
					</tfoot>
					
				</table>
				<?php

			} catch (Exception $e) {
				// Show Error Message
				echo '<div class="error"><p>' . $e->getMessage() . '</p></div>';
			}
		}

	}


	/* Initialize the Singleton Instance of the Importer */
	WPRSS_OPML_Importer::$singleton = new WPRSS_OPML_Importer();



	add_action( 'admin_init', 'wprss_opml_register_import' );
	/**
	 * Initializes and registers the OPML Importer
	 * @since 3.3
	 */
	function wprss_opml_register_import() {

		register_importer(
			'wprss_opml_importer',
			'WP RSS OPML',
			'Import Feeds from an OPML file into WP RSS Aggregator',
			array( WPRSS_OPML_Importer::$singleton ,'opml_import' )
		);

	}


?>