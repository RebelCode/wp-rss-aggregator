<?php
/**
 * Helper and misc functions.
 * 
 * @todo Make this part of Core instead
 */

if (!function_exists('wprss_get_namespace')) {

    /**
     * Get the namespace of a class
     *
     * @since 1.0
     * @param string|object|null $class The class name or instance, for which to get the namespace.
     * @param int|null $depth The depth of the namespace to retrieve.
     *  If omitted, the whole namespace will be retrieved.
     * @param bool $asString If true, the result will be a string; otherwise, array with namespace parts.
     * @return array|string The namespace of the class.
     */
    function wprss_get_namespace($class, $depth = null, $asString = false)
    {
        $ns = '\\';

        // Can accept an instance
        if (is_object($class)) {
            $class = get_class($class);
        }

        $namespace = explode($ns, (string) $class);

        // This was a root class name, no namespace
        array_pop($namespace);
        if (!count($namespace)) {
            return null;
        }

        $namespace = array_slice($namespace, 0, $depth);

        return $asString ? implode($ns, $namespace) : $namespace;
    }
}

if (!function_exists('wprss_is_root_namespace')) {

    /**
     * Check if a namespace is a root namespace.
     *
     * @since 1.0
     * @param string $namespace The namespace to check.
     * @param bool $checkClass If true, and a class or interface with the name of the specified namespace exists,
     *  will make this function return true. Otherwise, the result depends purely on the namespace string.
     * @return boolean True if the namespace is a root namespace; false otherwise.
     */
    function wprss_is_root_namespace($namespace, $checkClass = true) {
        $isRoot = substr($namespace, 0, 1) === '\\';
        return $checkClass
            ? $isRoot || class_exists($namespace)
            : $isRoot;
    }
}

if (!function_exists('string_had_prefix')) {

    /**
     * Check for and possibly remove a prefix from a string.
     *
     * @since 1.0
     * @param string $string The string to check and normalize.
     * @param string $prefix The prefix to check for.
     * @return string Checks if a string starts with the specified prefix.
     *  If yes, removes it and returns true; otherwise, false;
     */
    function string_had_prefix(&$string, $prefix)
    {
        $prefixLength = strlen($prefix);
        if (substr($string, 0, $prefixLength) === $prefix) {
            $string = substr($string, $prefixLength);
            return true;
        }

        return false;
    }
}

if (!function_exists('uri_is_absolute')) {

    /**
     * Check if the URI is absolute.
     * 
     * Check is made based on whether or not there's a '//' sequence
     * somewhere in the beginning.
     *
     * @since 1.0
     * @param string $uri The URI to check.
     * @return boolean True of the given string contains '//' within the first 10 chars;
     *  otherwise, false.
     */
    function uri_is_absolute($uri)
    {
        $beginning = substr($uri, 0, 10);
        return strpos($beginning, '//') !== false;
    }
}

if ( ! function_exists('array_merge_recursive_distinct') ) {
	/**
	* array_merge_recursive does indeed merge arrays, but it converts values with duplicate
	* keys to arrays rather than overwriting the value in the first array with the duplicate
	* value in the second array, as array_merge does. I.e., with array_merge_recursive,
	* this happens (documented behavior):
	*
	* array_merge_recursive(array('key' => 'org value'), array('key' => 'new value'));
	*     => array('key' => array('org value', 'new value'));
	*
	* array_merge_recursive_distinct does not change the datatypes of the values in the arrays.
	* Matching keys' values in the second array overwrite those in the first array, as is the
	* case with array_merge, i.e.:
	*
	* array_merge_recursive_distinct(array('key' => 'org value'), array('key' => 'new value'));
	*     => array('key' => array('new value'));
	*
	* Parameters are passed by reference, though only for performance reasons. They're not
	* altered by this function.
	*
     * @since 1.0
	* @param array $array1
	* @param array $array2
	* @return array
	* @author Daniel <daniel (at) danielsmedegaardbuus (dot) dk>
	* @author Gabriel Sobrinho <gabriel (dot) sobrinho (at) gmail (dot) com>
	*/
	function array_merge_recursive_distinct ( array &$array1, array &$array2 ) {
		$merged = $array1;
		foreach ( $array2 as $key => &$value ) {
			if ( is_array ( $value ) && isset ( $merged [$key] ) && is_array ( $merged [$key] ) ) {
				$merged [$key] = array_merge_recursive_distinct ( $merged [$key], $value );
			}
			else $merged [$key] = $value;
		}
		return $merged;
	}
}

if (!function_exists('array_pick')) {

    /**
     * Picks values with certain keys from an array.
     *
     * @since 1.0
     * @param array $array An array, from which to pick. Will not be modified; passed by refrence for efficiency.
     * @param string|int|array $keys A key or array of keys to pick.
     * @return array
     */
    function array_pick($array, $keys)
    {
        $keys = (array)$keys;
        return array_intersect_key($array, array_flip($keys));
    }
}