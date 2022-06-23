<?php

namespace RebelCode\Wpra\Core\Util;

use InvalidArgumentException;
use stdClass;
use Traversable;

class Normalize
{
    /**
     * Normalizes a value into an array.
     *
     * @since [*next-version*]
     *
     * @param array|stdClass|Traversable $value The value to normalize.
     *
     * @return array The normalized value.
     * @throws InvalidArgumentException If value cannot be normalized.
     *
     */
    public static function toArray($value)
    {
        if (is_array($value) || $value instanceof stdClass) {
            return (array) $value;
        }

        if (!($value instanceof Traversable)) {
            throw new InvalidArgumentException(__('Not an iterable'), null, null);
        } else {
            return iterator_to_array($value, true);
        }
    }
}
