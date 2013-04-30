<?php
    /**
     * Build the import/export settings page, used to import and export the plugin's settings
     * Based on http://wp.tutsplus.com/tutorials/creative-coding/creating-a-simple-backuprestore-settings-feature/
     * @since 3.0
     * @todo not working properly, need to fix it and also include other settings (excerpts and thumbnails)
     */ 

    function wprss_import_export_settings_page_display() {
      //  ob_start(); // for import and export functionality, need to check about whether this is needed        
        if ( !isset( $_POST['export'] ) ) { 
            ?>
            <div class="wrap">
                <?php screen_icon( 'wprss-aggregator' ); ?>
            
                <h2><?php _e( 'Import & Export Settings', 'wprss' ); ?></h2>

                <h3><?php _e( 'Export Settings', 'wprss' ); ?></h3>
                <p><?php _e( 'Click the <strong>Export Settings</strong> button to generate a file containing all the settings used by WP RSS Aggregator', 'wprss' ); ?></p>
                <p><?php _e( 'After exporting, you can either use the backup file to restore your settings to this site or to another WordPress site.</p>', 'wprss' ); ?></p>            
                
                <form method="post">            
                    <p class="submit">
                        <?php wp_nonce_field( 'wprss-settings-export' ); ?>
                        <input type="submit" name="export" value="<?php _e( 'Export Settings', 'wprss' ); ?>"  class="button" />
                    </p>
                </form>
            
                <h3><?php _e( 'Import Settings', 'wprss' ); ?></h3>
                <p><?php _e( 'Click the <strong>Choose file</strong> button and choose a backup file.', 'wprss' ); ?></p>
                <p><?php _e( 'Press the <strong>Import Settings</strong> button, and WordPress will do the rest for you.', 'wprss' ); ?></p>
                <form method='post' enctype='multipart/form-data'>
                    <p class="submit">
                        <?php wp_nonce_field( 'wprss-settings-import' ); ?>
                        <input type='file' name='import' />
                        <input type='submit' name='import' value="<?php _e( 'Import Settings', 'wprss' ); ?>" class="button" />
                    </p>
                </form>

                <h3><?php _e( 'Importing/Exporting Feed Sources', 'wprss' ); ?></h3>
                <p><?php _e( 'To import/export your feed sources, please use the standard WordPress <a href="' . get_admin_url() . 'import.php">Import</a> and <a href="' . get_admin_url() . 'export.php">Export</a> functionality.', 'wprss' ); ?></p>
                <p><?php _e( 'On the <a href="' . get_admin_url() . 'export.php">Export</a> page, check the <strong>Feed Sources</strong> radio button and click the <strong>Download Export File</strong> button. WordPress will then create an XML file containing all the feed sources.', 'wprss' ); ?></p>
                <p><?php _e( 'On the <a href="' . get_admin_url() . 'import.php">Import</a> page, choose the previously created file and click the <strong>Upload file and import</strong> button.', 'wprss' ); ?></p>

            </div>
        <?php
        }   

        if ( isset( $_FILES['import'] ) && check_admin_referer( 'wprss-settings-import' ) ) {
            if ( $_FILES['import']['error'] > 0) 
                wp_die( "Error during import" );        
            else {                
                $file_name = $_FILES['import']['name'];
                $file_ext = strtolower( end( explode( ".", $file_name ) ) );
                $file_size = $_FILES['import']['size'];
                if ( ( $file_ext == "json" ) && ( $file_size < 500000 ) ) {
                    $encode_options = file_get_contents( $_FILES['import']['tmp_name'] );
                    $options = json_decode( $encode_options, true );
                    foreach ( $options as $key => $value ) {
                        update_option( $key, $value );    
                    }
                    echo '<div class="updated"><p>' . __( 'All options are restored successfully.', 'wprss' ) . '</p></div>';
                }   
                else 
                    echo '<div class="error"><p>' . __( 'Invalid file or file size too big.', 'wprss' ) . '</p></div>';
            }
        }
        
        else if ( isset( $_POST['export'] ) && check_admin_referer( 'wprss-settings-export' ) ) {  
            $blogname = str_replace( " ", "", get_option( 'blogname' ) );
            $date = date( "m-d-Y" );
            $json_name = $blogname . "-" . $date; // Naming the filename that will be generated.

            $options = apply_filters( 
                'wprss_fields_export',
                array(
                    'wprss_settings_general' => get_option( 'wprss_settings_general' ) 
                )
            );    
            $json_file = json_encode( $options );

            foreach ( $options as $key => $value ) {
                $value = maybe_unserialize( $value );
                $need_options[ $key ] = $value;
            }

            $json_file = json_encode( $need_options ); // Encode data into json data

            ob_clean();
            echo $json_file;
            header( "Content-Type: text/json; charset=" . get_option( 'blog_charset' ) );
            header( "Content-Disposition: attachment; filename=$json_name.json" );
            add_action( 'admin_notices', 'my_admin_notice' );
            exit();
        }
    }

