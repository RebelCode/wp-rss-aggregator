<?php

	define( 'WPRSS_OPTION_CODE_LOG_LEVEL', 'log_level' );
	define( 'WPRSS_LOG_LEVEL_NONE', 0 );
	define( 'WPRSS_LOG_LEVEL_INFO', 1 );
	define( 'WPRSS_LOG_LEVEL_NOTICE', 2 );
	define( 'WPRSS_LOG_LEVEL_WARNING', 4 );
	define( 'WPRSS_LOG_LEVEL_ERROR', 8 );
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
	function wprss_get_log_level_db() {
		return wprss_get_general_setting( WPRSS_OPTION_CODE_LOG_LEVEL );
	}
	
	/**
	 * Gets log level used.
	 * @return string The string representing the log level threshold.
	 */
	function wprss_get_log_level() {
		$log_level = wprss_get_log_level_db();
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
	function wprss_is_log_level( $log_level, $used_log_level = null ) {
		$used_log_level = is_null( $used_log_level ) ? wprss_get_log_level() : $used_log_level;
		
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
	function wprss_is_logging_level( $log_level ) {
		$original_used_level = $used_log_level = wprss_get_log_level();
		
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
					: wprss_is_log_level( (int)$log_level, $used_log_level );
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
	function wprss_get_log_levels( $levels_only = true ) {
		$log_levels =  array(
			WPRSS_LOG_LEVEL_INFO			=> 'Info',
			WPRSS_LOG_LEVEL_WARNING			=> 'Warning',
			WPRSS_LOG_LEVEL_ERROR			=> 'Error'
		);
		
		if( !$levels_only )
			$log_levels[ WPRSS_LOG_LEVEL_DEFAULT ]		= 'Default';
		
		return apply_filters( 'wprss_log_levels', $log_levels, $levels_only );
	}


	/**
	 * Adds a log entry to the log file.
	 *
	 * @since 3.9.6
	 */
	function wprss_log( $message, $src = NULL ) {
		if ( $src === NULL ) {
			$callers = debug_backtrace();
			$src = $callers[1]['function'];
		}
		$date =  date( 'd-m-Y H:i:s' );
		$source = 'WPRSS' . ( ( strlen( $src ) > 0 )? " > $src" : '' ) ;
		$str = "[$date] $source:\n";
		$str .= "$message\n";
		file_put_contents( wprss_log_file() , $str, FILE_APPEND );

		add_action( 'shutdown', 'wprss_log_separator' );
	}


	/**
	 * Dumps an object to the log file.
	 *
	 * @since 3.9.6
	 */
	function wprss_log_obj( $message, $obj, $src = '' ) {
		wprss_log( "$message: " . print_r( $obj, TRUE ), $src );
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
		$limit = 10000;
		if ( strlen( $contents ) > $limit ) {
			file_put_contents( wprss_log_file(), substr( $contents, 0, $limit ) );
			return wprss_get_log();
		} else {
			return $contents;
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