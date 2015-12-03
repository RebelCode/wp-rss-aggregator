<?php

	define( 'WPRSS_LOG_DISPLAY_LIMIT', 100000 ); // Number of chars to display in log
	define( 'WPRSS_OPTION_CODE_LOG_LEVEL', 'log_level' );
	define( 'WPRSS_LOG_LEVEL_NONE', 0 );
	define( 'WPRSS_LOG_LEVEL_SYSTEM', 1 );
	define( 'WPRSS_LOG_LEVEL_INFO', 2 );
	define( 'WPRSS_LOG_LEVEL_NOTICE', 4 );
	define( 'WPRSS_LOG_LEVEL_WARNING', 8 );
	define( 'WPRSS_LOG_LEVEL_ERROR', 16 );
	define( 'WPRSS_LOG_LEVEL_DEFAULT', 'default' );
	

	/**
	 * Returns the log file path.
	 * 
	 * @since 4.0.4
	 */
	function wprss_log_file() {
		return WPRSS_LOG_FILE . '-' . get_current_blog_id() . WPRSS_LOG_FILE_EXT;
	}


	/**
	 * Clears the log file.
	 *
	 * @since 3.9.6
	 */
	function wprss_clear_log() {
		file_put_contents( wprss_log_file(), '' );
	}


	/**
	 * Alias for wprss_clear_log(). Used for code readability.
	 *
	 * @since 3.9.6
	 */
	function wprss_reset_log() {
		wprss_clear_log();
	}
	
	/**
	 * Gets log level from the database.
	 * @return string The string representing the log level threshold or type.
	 */
	function wprss_log_get_level_db() {
		return wprss_get_general_setting( WPRSS_OPTION_CODE_LOG_LEVEL );
	}
	
	/**
	 * Gets log level used.
	 * @return string The string representing the log level threshold.
	 */
	function wprss_log_get_level() {
		$log_level = wprss_log_get_level_db();
		if ( $log_level === WPRSS_LOG_LEVEL_DEFAULT )
			$log_level = WPRSS_LOG_LEVEL;
		
		return apply_filters( 'wprss_log_level', $log_level );
	}
	
	
	/**
	 * Check whether or not the specified logging level is the same as, or one of (only for positive),
	 * the currently used logging level.
	 * 
	 * @param int $log_level The log level to check. Must be an unsiged whole number.
	 */
	function wprss_log_is_level( $log_level, $used_log_level = null ) {
		$used_log_level = is_null( $used_log_level ) ? wprss_log_get_level() : $used_log_level;
		
		if( is_numeric( $log_level ) ) {
			$log_level = intval( $log_level );
			$used_log_level = intval( $used_log_level );
			
			return ($log_level > 0 && $used_log_level > 0)
					// Mostly for the case of 0
					? intval( $log_level ) & intval( $used_log_level )
					: $log_level === $used_log_level;
		}
		
		return trim( $log_level ) === trim( $used_log_level );
	}
	
	
	/**
	 * Check whether or not messages with the specified logging level should be logged.
	 * 
	 * @param int $log_level The log level to check. Must be an unsigned whole number
	 * @return bool True if messages with the specified logging level should be logged; false otherwise.
	 */
	function wprss_log_is_logging_level( $log_level ) {
		$original_used_level = $used_log_level = wprss_log_get_level();
		
		// Whether to use the indicated level and below
		$is_below = ( substr( $used_log_level, 0, 1 ) === '-' );
		if ( $is_below )
			$used_log_level = substr( $used_log_level, 1 );
		
		if( (int)$used_log_level === WPRSS_LOG_LEVEL_NONE ) {
			$is_log_level = WPRSS_LOG_LEVEL_NONE;
		}
		else {
			$is_log_level = $is_below
					? ((int)$log_level <= (int)$used_log_level && (int)$log_level !== WPRSS_LOG_LEVEL_NONE)
					: wprss_log_is_level( (int)$log_level, $used_log_level );
		}
		
		return apply_filters( 'wprss_is_logging_level', $is_log_level, $log_level, $used_log_level, $is_below );
	}
	
	
	/**
	 * Get the available log levels.
	 * 
	 * @param bool $levels_only Whether or not only numeric actual levels are to be returned.
	 * If false, returns other types as well.
	 * @return array An array, where key is level, and value is level's human-readable name
	 */
	function wprss_log_get_levels( $levels_only = true ) {
		$log_levels =  array(
			WPRSS_LOG_LEVEL_NONE			=> 'None',
			WPRSS_LOG_LEVEL_SYSTEM			=> 'System',
			WPRSS_LOG_LEVEL_INFO			=> 'Info',
			WPRSS_LOG_LEVEL_NOTICE			=> 'Notice',
			WPRSS_LOG_LEVEL_WARNING			=> 'Warning',
			WPRSS_LOG_LEVEL_ERROR			=> 'Error'
		);
		
		if( !$levels_only )
			$log_levels[ WPRSS_LOG_LEVEL_DEFAULT ]		= 'Default';
		
		return apply_filters( 'wprss_log_levels', $log_levels, $levels_only );
	}
	
	
	/**
	 * 
	 * @param string|int $level Any valid level value.
	 * @return string The untranslated label of the specified level, or $default if no such level exists.
	 */
	function wprss_log_get_level_label( $level, $default = 'N/A' ) {
		$levels = wprss_log_get_levels( false );
		return isset( $levels[$level] ) ? $levels[ $level ] : $default;
	}


	/**
	 * Adds a log entry to the log file.
	 *
	 * @since 3.9.6
	 */
	function wprss_log( $message, $src = NULL, $log_level = WPRSS_LOG_LEVEL_ERROR ) {
		if( !wprss_log_is_logging_level( $log_level ) ) return;
		
		if ( $src === NULL ) {
			$callers = debug_backtrace();
			$src = $callers[1]['function'];
			if ( $src === 'wprss_log_obj' ) {
				$src = $callers[2]['function'];
			}
		}
		$log_level_label = wprss_log_get_level_label( $log_level );
		$date =  date( 'd-m-Y H:i:s' );
		$source = 'WPRSS' . ( ( strlen( $src ) > 0 )? " > $src" : '' ) ;
		$str = "[$date] [$log_level_label] $source:\n";
		$str .= "$message\n\n";
		file_put_contents( wprss_log_file() , $str, FILE_APPEND );

		add_action( 'shutdown', 'wprss_log_separator' );
	}


	/**
	 * Dumps an object to the log file.
	 *
	 * @since 3.9.6
	 */
	function wprss_log_obj( $message, $obj, $src = '', $log_level = WPRSS_LOG_LEVEL_ERROR ) {
		wprss_log( "$message: " . print_r( $obj, TRUE ), $src, $log_level );
	}


	/**
	 * Returns the contents of the log file.
	 *
	 * @since 3.9.6
	 */
	function wprss_get_log() {
		if ( !file_exists( wprss_log_file() ) ) {
			wprss_clear_log();
		}
		$contents = file_get_contents(  wprss_log_file() , '' );
		// Trim the log file to a fixed number of chars
		$limit = WPRSS_LOG_DISPLAY_LIMIT;
		if ( strlen( $contents ) > $limit ) {
			file_put_contents( wprss_log_file(), substr( $contents, 0, $limit ) );
			return wprss_get_log();
		} else {
			return $contents;
		}
	}


	/**
	 * Downloads the log file.
	 *
	 * @since 4.7.8
	 */
	function wprss_download_log() {
		if ( !file_exists( wprss_log_file() ) ) {
			wprss_clear_log();
		}
		else {
			$file = wprss_log_file();
		    header( 'Content-Description: File Transfer' );
			header( 'Content-type: text/plain' );
			header( 'Content-Disposition: attachment; filename="error-log.txt"' );
		    header( 'Expires: 0' );
		    header( 'Cache-Control: must-revalidate' );
		    header( 'Pragma: public' );
		    header( 'Content-Length: ' . filesize( $file ) );
		    readfile( $file );
		    exit;
		}		
	}	


	/**
	 * Adds an empty line at the end of the log file.
	 *
	 * This function is called on wordpress shutdown, if at least one new line
	 * is logged in the log file, to separate logs from different page loads.
	 *
	 * @since 3.9.6
	 */
	function wprss_log_separator() {
		file_put_contents( wprss_log_file(), "\n", FILE_APPEND );	
	}
	
	
	/**
	 * Adding the default setting value.
	 */
	add_filter( 'wprss_default_settings_general', 'wprss_log_default_settings_general' );
	function wprss_log_default_settings_general( $settings ) {
		/* @todo Add version info */
		$settings[ WPRSS_OPTION_CODE_LOG_LEVEL ]	= WPRSS_LOG_LEVEL_DEFAULT;
		return $settings;
	}
	
	
	/**
	 * Adding the setting field
	 */
	add_filter( 'wprss_settings_array', 'wprss_log_settings_array' );
	function wprss_log_settings_array( $sections ) {
		$sections['general'][ WPRSS_OPTION_CODE_LOG_LEVEL ] = array(
			'label'			=> __( 'Log level threshold', WPRSS_TEXT_DOMAIN ),
			'callback'		=> 'wprss_setting_' . WPRSS_OPTION_CODE_LOG_LEVEL . '_callback'
		);
		return $sections;
	}
	
	
	/**
	 * Renders the 'log_level' setting field.
	 * 
	 * @param array $field Info about the field
	 */
	function wprss_setting_log_level_callback( $field ) {
        $log_level = wprss_get_general_setting( $field['field_id'] );
		
		foreach( wprss_log_get_levels( false ) as $_level => $_label ) {
			$options[ $_level ] = $_label;
			if( is_numeric( $_level ) && ($_level/2 >= 1) ) $options[ (int)$_level * -1 ] = $_label . ' and below';
		}
		
		krsort( $options, defined( 'SORT_NATURAL' ) ? SORT_NATURAL : SORT_STRING );
        ?>
		<select id="<?php echo $field['field_id'] ?>" name="wprss_settings_general[<?php echo $field['field_id'] ?>]">
		<?php
		foreach( $options as $value => $text ) {
			$selected = ( (string)$value === (string)$log_level )? 'selected="selected"' : '';
			?><option value="<?php echo $value ?>" <?php echo $selected ?>><?php echo __( $text, WPRSS_TEXT_DOMAIN ) ?></option><?php
		}
		?>
		</select>
		<?php echo wprss_settings_inline_help( $field['field_id'], $field['tooltip'] );
	}
	