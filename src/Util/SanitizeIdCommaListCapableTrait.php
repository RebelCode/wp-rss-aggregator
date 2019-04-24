<?php

namespace RebelCode\Wpra\Core\Util;

/**
 * Functionality for sanitizing a comma-separated string list of IDs into an array.
 *
 * @since 4.13
 */
trait SanitizeIdCommaListCapableTrait
{
    /**
     * Sanitizes a list of IDs.
     *
     * @since 4.13
     *
     * @param string|array $value A comma separated string list or an array.
     *
     * @return array The list of IDs.
     */
    protected function sanitizeIdCommaList($value)
    {
        if (empty($value)) {
            return [];
        }

        $array = is_array($value)
            ? $value
            : explode(',', strval($value));

        $ids = array_map(function ($part) {
            return intval(trim($part));
        }, $array);

        return array_filter($ids);
    }
}
