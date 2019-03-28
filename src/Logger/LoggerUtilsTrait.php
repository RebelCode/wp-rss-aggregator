<?php

namespace RebelCode\Wpra\Core\Logger;

/**
 * Utility functionality for loggers.
 *
 * @since [*next-version*]
 */
trait LoggerUtilsTrait
{
    /**
     * Interpolates context values into the message placeholders.
     *
     * @since [*next-version*]
     *
     * @param string   $message The string to interpolate.
     * @param string[] $context An associative array map of values to replace in the message.
     *
     * @return string The interpolated message.
     */
    protected function interpolate($message, array $context)
    {
        $replace = [];

        foreach ($context as $key => $val) {
            $replace['{' . $key . '}'] = $this->normalizeContextString($val);
        }

        return strtr($message, $replace);
    }

    /**
     * Normalizes a context value to a string.
     *
     * @since [*next-version*]
     *
     * @param mixed $value The context value to normalize.
     *
     * @return string The string.
     */
    protected function normalizeContextString($value)
    {
        if (is_object($value) || is_array($value)) {
            return json_encode((array) $value);
        }

        return strval($value);
    }
}
