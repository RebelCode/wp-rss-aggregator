<?php
        define( 'WPRSS_OPTION_CODE_LOG_LEVEL', 'log_level' );
        define( 'WPRSS_LOG_LEVEL_NONE', 0 );
        define( 'WPRSS_LOG_LEVEL_SYSTEM', 1 );
        define( 'WPRSS_LOG_LEVEL_INFO', 2 );
        define( 'WPRSS_LOG_LEVEL_NOTICE', 4 );
        define( 'WPRSS_LOG_LEVEL_WARNING', 8 );
        define( 'WPRSS_LOG_LEVEL_ERROR', 16 );
        define( 'WPRSS_LOG_LEVEL_DEFAULT', 'default' );

        if (!defined('WPRSS_LOG_FILENAME_SEPARATOR'))
            define('WPRSS_LOG_FILENAME_SEPARATOR', '_');

        if (!defined('WPRSS_LOG_FILENAME_CONCATENATOR'))
            define('WPRSS_LOG_FILENAME_CONCATENATOR', '-');

        // Number of chars to display in log
        if (!defined('WPRSS_LOG_DISPLAY_LIMIT'))
            define( 'WPRSS_LOG_DISPLAY_LIMIT', 100000 ); // 100Kb


        /**
         * Returns the log file path.
         *
         * @since 4.0.4
         */
        function wprss_log_file()
        {
            return WPRSS_LOG_FILE . wprss_log_suffix() . WPRSS_LOG_FILE_EXT;
        }

        /**
         * Writes a message to the log file.
         *
         * If directories on the log filepath don't exist, creates them.
         *
         * @since 4.10
         *
         * @param string $message The message to write to the log.
         * @param int $flags Flags to be used with {@see file_put_contents()} for writing.
         * @return bool True if message written successfully; false otherwise.
         */
        function wprss_log_write($message, $flags = 0)
        {
            $file = wprss_log_file();
            $dir = dirname($file);
            if (!file_exists($dir)) {
                if (!wp_mkdir_p($dir)) {
                    return false;
                }
            }

            return file_put_contents($file, $message, $flags);
        }

        /**
         * Reads a certain amount of data from the log file.
         *
         * By default, reads that data from the end of the file.
         *
         * @since 4.10
         *
         * @param null|int $length How many characters at most to read from the log.
         *  Default: {@see WPRSS_LOG_DISPLAY_LIMIT}.
         * @param null|int $start Position, at which to start reading.
         *  If negative, represents that number of chars from the end.
         *  Default: The amount of characters equal to $length away from the end of the file,
         *  or the beginning of the file if the size of the file is less than or equal to $length.
         *
         * @return string|bool The content of the log, or false if the read operation failed.
         */
        function wprss_log_read($length = null, $start = null)
        {
            $origStart = $start;

            if (is_null($length)) {
                $length = WPRSS_LOG_DISPLAY_LIMIT;
            }

            $file = wprss_log_file();

            if (!($fh = fopen($file, 'r'))) {
                return false;
            }

            $info = fstat($fh);
            $size = $info['size'];

            if ($size === 0 || $length === 0) {
                return '';
            }

            // Can't read more than the length of the file
            if ($length > $size) {
                $length = $size;
            }

            // Default start is length before end
            if (is_null($start)) {
                $start = -$length;
            }

            // Allowing negative
            if ($start < 0) {
                $start = $size - abs($start);
            }

            // Can't start before start of file
            if ($start < 0) {
                $start = 0;
            }

            // If reading over EOF,
            $end = $start + $length;
            if ($end > $size) {
                $over = $end - $size;
                // If start is not fixed, shift start to allow reading as much length as possible
                if (is_null($origStart)) {
                    $start = $start - $over;
                }
                // If start is fixed, shift length to allow reading from start until end
                else {
                    $length = $length - $over;
                }
            }

            // Can't start before start of file
            if ($start < 0) {
                $start = 0;
            }

            // Returns 0 when failed
            if (fseek($fh, $start)) {
                return false;
            }

            $str = fread($fh, $length);
            fclose($fh);

            return $str;
        }

        /**
         * Determines a suffix for a log file based on context and some globally accessible variables.
         *
         * @since 4.10
         *
         * @param array $context Options for the suffix.
         *  Default: ['blog_id' => {{current blog id}}]
         *
         * @return string The log file suffix. Prefixed with separator.
         */
        function wprss_log_suffix(array $context = null)
        {
            if (is_null($context)) {
                $context = array('blog_id' => get_current_blog_id());
            }

            $s = WPRSS_LOG_FILENAME_SEPARATOR;
            $c = WPRSS_LOG_FILENAME_CONCATENATOR;
            $parts = array();

            if (isset($context['blog_id'])) {
                $parts[] = 'blg' . $c . $context['blog_id'];
            }

            $suffix = $s . implode($s, $parts);
            $suffix = apply_filters('wprss_log_suffix', $suffix, $context);

            return $suffix;
        }

        /**
        * Clears the log file.
        *
        * @since 3.9.6
        */
        function wprss_clear_log()
        {
            wprss_log_write( '' );
        }

        /**
         * Alias for wprss_clear_log().
         *
         * Used for code readability.
         *
         * @since 3.9.6
         */
        function wprss_reset_log()
        {
            wprss_clear_log();
        }

        /**
         * Gets log level from the database.
         * @return string The string representing the log level threshold or type.
         */
        function wprss_log_get_level_db()
        {
            return wprss_get_general_setting( WPRSS_OPTION_CODE_LOG_LEVEL );
        }

        /**
         * Gets log level used.
         * @return string The string representing the log level threshold.
         */
        function wprss_log_get_level()
        {
            $log_level = wprss_log_get_level_db();
            if ( $log_level === WPRSS_LOG_LEVEL_DEFAULT ) {
                $log_level = WPRSS_LOG_LEVEL;
            }

            return apply_filters( 'wprss_log_level', $log_level );
        }

        /**
         * Check whether or not the specified logging level is the same as, or one of (only for positive),
         * the currently used logging level.
         *
         * @param int $log_level The log level to check. Must be an unsigned whole number.
         */
        function wprss_log_is_level( $log_level, $used_log_level = null )
        {
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
        function wprss_log_is_logging_level( $log_level )
        {
            $original_used_level = $used_log_level = wprss_log_get_level();

            // Whether to use the indicated level and below
            $is_below = ( substr( $used_log_level, 0, 1 ) === '-' );
            if ( $is_below ) {
                $used_log_level = substr( $used_log_level, 1 );
            }

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
         * @return array An array, where key is level, and value is level's human-readable name.
         */
        function wprss_log_get_levels( $levels_only = true )
        {
            $log_levels =  array(
                WPRSS_LOG_LEVEL_NONE            => 'None',
                WPRSS_LOG_LEVEL_SYSTEM          => 'System',
                WPRSS_LOG_LEVEL_INFO            => 'Info',
                WPRSS_LOG_LEVEL_NOTICE          => 'Notice',
                WPRSS_LOG_LEVEL_WARNING         => 'Warning',
                WPRSS_LOG_LEVEL_ERROR           => 'Error'
            );

            if( !$levels_only ) {
                $log_levels[ WPRSS_LOG_LEVEL_DEFAULT ] = 'Default';
            }

            return apply_filters( 'wprss_log_levels', $log_levels, $levels_only );
        }

        /**
         *
         * @param string|int $level Any valid level value.
         * @return string The untranslated label of the specified level, or $default if no such level exists.
         */
        function wprss_log_get_level_label( $level, $default = 'N/A' )
        {
            $levels = wprss_log_get_levels( false );
            return isset( $levels[$level] ) ? $levels[ $level ] : $default;
        }

        /**
         * Adds a log entry to the log file.
         *
         * @since 3.9.6
         */
        function wprss_log( $message, $src = NULL, $log_level = WPRSS_LOG_LEVEL_ERROR )
        {
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
            wprss_log_write( $str, FILE_APPEND );

            add_action( 'shutdown', 'wprss_log_separator' );
        }

        /**
         * Dumps an object to the log file.
         *
         * @since 3.9.6
         */
        function wprss_log_obj( $message, $obj, $src = '', $log_level = WPRSS_LOG_LEVEL_ERROR )
        {
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

            $log = wprss_log_read();

            return $log;
        }

        /**
         * Downloads the log file.
         *
         * @since 4.7.8
         */
        function wprss_download_log()
        {
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
         * This function is called on WordPress shutdown, if at least one new line
         * is logged in the log file, to separate logs from different page loads.
         *
         * @since 3.9.6
         */
        function wprss_log_separator()
        {
            wprss_log_write( "\n", FILE_APPEND );
        }

        /**
         * Adding the default setting value.
         */
        add_filter( 'wprss_default_settings_general', 'wprss_log_default_settings_general' );
        function wprss_log_default_settings_general( $settings )
        {
            /* @todo Add version info */
            $settings[ WPRSS_OPTION_CODE_LOG_LEVEL ] = WPRSS_LOG_LEVEL_DEFAULT;
            return $settings;
        }

        /**
         * Adding the setting field
         */
        add_filter( 'wprss_settings_array', 'wprss_log_settings_array' );
        function wprss_log_settings_array( $sections )
        {
            $sections['general'][ WPRSS_OPTION_CODE_LOG_LEVEL ] = array(
                'label'         => __( 'Log level threshold', WPRSS_TEXT_DOMAIN ),
                'callback'      => 'wprss_setting_' . WPRSS_OPTION_CODE_LOG_LEVEL . '_callback'
            );
            return $sections;
        }

        /**
         * Renders the 'log_level' setting field.
         *
         * @param array $field Info about the field
         */
        function wprss_setting_log_level_callback( $field )
        {
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
