<?php

namespace RebelCode\Wpra\Core\Modules\Handlers;

/**
 * A generic handler implementation that registers a WordPress shortcode.
 *
 * @since [*next-version*]
 */
class RegisterShortcodeHandler
{
    /**
     * The name of the shortcode, or a list of names.
     *
     * @since [*next-version*]
     *
     * @var string|string[]
     */
    protected $name;

    /**
     * The shortcode callback handler.
     *
     * @since [*next-version*]
     *
     * @var callable
     */
    protected $handler;

    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param string|string[] $name The name of the shortcode, or a list of names.
     * @param callable        $handler The shortcode callback handler.
     */
    public function __construct($name, callable $handler)
    {
        $this->name = $name;
        $this->handler = $handler;
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function __invoke()
    {
        foreach ((array) $this->name as $name) {
            add_shortcode($name, $this->handler);
        }
    }
}
