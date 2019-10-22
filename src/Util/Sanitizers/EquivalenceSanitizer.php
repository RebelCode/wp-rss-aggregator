<?php

namespace RebelCode\Wpra\Core\Util\Sanitizers;

use RebelCode\Wpra\Core\Util\SanitizerInterface;

/**
 * A sanitizer that compares values against another static literal value and yields booleans, with support for negated
 * equivalence checking.
 *
 * @since [*next-version*]
 */
class EquivalenceSanitizer implements SanitizerInterface
{
    /**
     * @since [*next-version*]
     *
     * @var mixed
     */
    protected $value;

    /**
     * @since [*next-version*]
     *
     * @var bool
     */
    protected $notEqual;

    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param mixed $value    The value to compare against.
     * @param bool  $notEqual Whether to use negated equivalence.
     */
    public function __construct($value, $notEqual = false)
    {
        $this->value = $value;
        $this->notEqual = $notEqual;
    }

    /**
     * @inheritdoc
     *
     * @since [*next-version*]
     */
    public function sanitize($value)
    {
        return $value === $this->value xor $this->notEqual;
    }
}
