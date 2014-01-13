<?php


	/**
	 * Clears the log file.
	 *
	 * @since 3.9.6
	 */
	function wprss_clear_log() {
		file_put_contents( WPRSS_LOG_FILE, '' );
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
		$str .= str_repeat(' ', strlen( strval( $date ) ) + 3 );
		$str .= "$message\n";
		file_put_contents( WPRSS_LOG_FILE , $str, FILE_APPEND );

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
		if ( !file_exists( WPRSS_LOG_FILE ) ) {
			wprss_clear_log();
		}
		$contents = file_get_contents(  WPRSS_LOG_FILE , '' );
		// Trim the log file to a fixed number of chars
		$limit = 10000;
		if ( strlen( $contents ) > $limit ) {
			file_put_contents( WPRSS_LOG_FILE, substr( $contents, 0, $limit ) );
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
		file_put_contents( WPRSS_LOG_FILE, "\n", FILE_APPEND );	
	}