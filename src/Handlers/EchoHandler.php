<?php

namespace RebelCode\Wpra\Core\Handlers;

/**
 * A simple handler that echoes a string.
 *
 * @since [*next-version*]
 */
class EchoHandler
{
    /**
     * @since [*next-version*]
     *
     * @var string
     */
    protected $string;

    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param string $string The string to output.
     */
    public function __construct($string)
    {
        $this->string = $string;
    }

    /**
     * @inheritdoc
     *
     * @since [*next-version*]
     */
    public function __invoke()
    {
        echo $this->string;
    }
}
