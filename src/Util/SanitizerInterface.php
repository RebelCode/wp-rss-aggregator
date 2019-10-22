<?php

namespace RebelCode\Wpra\Core\Util;

/**
 * Interface for objects that can sanitize values.
 *
 * @since [*next-version*]
 */
interface SanitizerInterface
{
    /**
     * Sanitizes a given value.
     *
     * @since [*next-version*]
     *
     * @param mixed $value The value to sanitize.
     *
     * @return mixed The sanitized value.
     */
    public function sanitize($value);
}
