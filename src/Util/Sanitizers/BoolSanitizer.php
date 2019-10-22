<?php

namespace RebelCode\Wpra\Core\Util\Sanitizers;

use RebelCode\Wpra\Core\Util\SanitizerInterface;

/**
 * A sanitizer implementation that sanitizes boolean values.
 *
 * @since [*next-version*]
 */
class BoolSanitizer implements SanitizerInterface
{
    /**
     * @inheritdoc
     *
     * @since [*next-version*]
     */
    public function sanitize($value)
    {
        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }
}
