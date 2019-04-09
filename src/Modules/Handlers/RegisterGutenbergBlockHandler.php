<?php

namespace RebelCode\Wpra\Core\Modules\Handlers;

/**
 * A handler for Gutenberg block registration.
 *
 * @since [*next-version*]
 */
class RegisterGutenbergBlockHandler
{
    /**
     * The name of the block.
     *
     * @since [*next-version*]
     *
     * @var string
     */
    protected $name;

    /**
     * Gutenberg block's configuration.
     *
     * @since [*next-version*]
     *
     * @var array
     */
    protected $config;

    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param string $name The name of the block.
     * @param array  $config Gutenberg block's configuration.
     */
    public function __construct($name, $config)
    {
        $this->name = $name;
        $this->config = $config;
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function __invoke()
    {
        /*
         * Register the block only when Gutenberg editor is enabled.
         */
        if (!function_exists('register_block_type')) {
            return;
        }

        register_block_type($this->name, $this->config);
    }
}
