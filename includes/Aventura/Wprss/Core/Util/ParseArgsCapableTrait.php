<?php

namespace Aventura\Wprss\Core\Util;

/**
 * Functionality for parsing array arguments against a known schema.
 *
 * @since [*next-version*]
 */
trait ParseArgsCapableTrait
{
    /**
     * Parses an args array with a given schema.
     *
     * @since [*next-version*]
     *
     * @param array  $args     The args to parse.
     * @param array  $schema   The schema with each element's key being the arg key and each value being a sub-array
     *                         containing the following data:
     *                         * "default" - the default value to use if the value is not in the args
     *                         * "transform" - Optional callback that returns the transformed value. The callback
     *                                         receives the original value, the full original args array and the schema
     *                                         sub-array as arguments.
     *                         * "key" - Optional destination key to remap the args entry.
     * @param string $keyDelim The key delimiter to use for nesting keys.
     *
     * @return array The parsed args.
     */
    protected function _parseArgs($args, $schema, $keyDelim = '/')
    {
        $prepared = [];

        foreach ($schema as $_key => $_singleSchema) {
            // Check if the args has the value
            $hasValue = array_key_exists($_key, $args);

            // Get the value, using the default if missing
            $origValue = ($hasValue)
                ? $args[$_key]
                : $_singleSchema['default'];

            // If the value is NOT the default and a sanitize function is set in the schema,
            // Run the value through the sanitize function
            $finalValue = ($hasValue && array_key_exists('transform', $_singleSchema))
                ? call_user_func_array($_singleSchema['transform'], [$origValue, $args, $_singleSchema])
                : $origValue;

            // Get the destination key from schema if given, using the original key if not
            $destKey = array_key_exists('key', $_singleSchema)
                ? $_singleSchema['key']
                : $_key;
            // Explode the key into an array path using the param delimiter
            $pathKey = explode($keyDelim, $destKey);

            // Save the final value into the destination key
            $this->_arrayDeepSet($prepared, $pathKey, $finalValue);
        }

        return $prepared;
    }

    /**
     * Utility method for setting a deep value in an array.
     *
     * @since [*next-version*]
     *
     * @param array $array The array in which to set the value.
     * @param array $path  An array of keys, each corresponding to a path segment.
     * @param mixed $value The value to set.
     */
    protected function _arrayDeepSet(&$array, $path, $value)
    {
        if (empty($path)) {
            return;
        }

        $head = array_shift($path);

        if (count($path) === 0) {
            $array[$head] = $value;

            return;
        }

        if (!array_key_exists($head, $array)) {
            $array[$head] = [];
        }

        static::_arrayDeepSet($array[$head], $path, $value);
    }
}
