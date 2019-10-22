<?php

namespace RebelCode\Wpra\Core\Util\Sanitizers;

use RebelCode\Wpra\Core\Util\SanitizerInterface;

/**
 * A sanitizer implementation for any native PHP filters.
 *
 * @since [*next-version*]
 */
class PhpFilterSanitizer implements SanitizerInterface
{
    /**
     * @since [*next-version*]
     *
     * @var int
     */
    protected $filter;

    /**
     * @since [*next-version*]
     *
     * @var int
     */
    protected $flags;

    /**
     * @since [*next-version*]
     *
     * @var array
     */
    protected $options;

    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param int   $filter  The filter.
     * @param int   $flags   The filter flags.
     * @param array $options The filter options.
     */
    public function __construct($filter, $flags = 0, array $options = [])
    {
        $this->filter = $filter;
        $this->flags = $flags;
        $this->options = $options;
    }

    /**
     * @inheritdoc
     *
     * @since [*next-version*]
     */
    public function sanitize($value)
    {
        return filter_var($value, $this->filter, [
            'flags' => $this->flags,
            'options' => $this->options,
        ]);
    }

    public static function validateInt($min = null, $max = null, $default = 0)
    {
        $options = [
            'default' => $default,
        ];

        if (is_int($min)) {
            $options['min_range'] = $min;
        }

        if (is_int($max)) {
            $options['max_range'] = $max;
        }

        return new static(FILTER_VALIDATE_INT, 0, $options);
    }

    public static function validateBool()
    {
        return new static(FILTER_VALIDATE_BOOLEAN);
    }
}
