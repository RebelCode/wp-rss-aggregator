<?php

namespace RebelCode\Wpra\Core\Modules\Handlers;

/**
 * A handler that loads a plugin's text domain.
 *
 * @since [*next-version*]
 */
class LoadTextDomainHandler
{
    /**
     * The text domain to load.
     *
     * @since [*next-version*]
     *
     * @var string
     */
    protected $domain;

    /**
     * The path to the translation files directory.
     *
     * @since [*next-version*]
     *
     * @var string
     */
    protected $directory;

    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param string $domain    The text domain to load.
     * @param string $directory The path to the translation files directory.
     */
    public function __construct($domain, $directory)
    {
        $this->domain = $domain;
        $this->directory = $directory;
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function __invoke()
    {
        load_plugin_textdomain($this->domain, false, $this->directory);
    }
}
