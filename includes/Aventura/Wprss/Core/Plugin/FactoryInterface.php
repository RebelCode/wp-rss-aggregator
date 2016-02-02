<?php

namespace Aventura\Wprss\Core\Plugin;

/**
 * An interface for something that creates plugins.
 *
 * @since [*next-version*]
 */
interface FactoryInterface
{
    /**
     * Create a plugin.
     *
     * @since [*next-version*]
     * @return PluginInterface
     */
    static public function create();
}