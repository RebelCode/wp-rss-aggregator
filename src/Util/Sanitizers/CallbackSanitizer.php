<?php

namespace RebelCode\Wpra\Core\Util\Sanitizers;

use RebelCode\Wpra\Core\Util\SanitizerInterface;

/**
 * A sanitizer implementation that uses callbacks for sanitization.
 *
 * @since [*next-version*]
 */
class CallbackSanitizer implements SanitizerInterface
{
    /**
     * @since [*next-version*]
     *
     * @var callable
     */
    protected $callback;

    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param callable $callback The callback function. Recieves the value as argument and should return the
     *                           sanitized value.
     */
    public function __construct(callable $callback)
    {
        $this->callback = $callback;
    }

    /**
     * @inheritdoc
     *
     * @since [*next-version*]
     */
    public function sanitize($value)
    {
        return call_user_func_array($this->callback, [$value]);
    }
}
