<?php

namespace Aventura\Wprss\Core\Util;

/**
 * Functionality for sanitizing a comma-separated string list of IDs into an array.
 *
 * @since [*next-version*]
 */
trait IdCommaListSanitizeTrait
{
    /**
     * Sanitizes a list of IDs.
     *
     * @since [*next-version*]
     *
     * @param string|array $value A comma separated string list or an array.
     *
     * @return array The list of IDs.
     */
    protected function _sanitizeIdCommaList($value)
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
