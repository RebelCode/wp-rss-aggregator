<?php

namespace Aventura\Wprss\Core\Plugin;

/**
 * @since [*next-version*]
 */
interface AddonInterface extends PluginInterface
{

    /**
     * Get the plugin, for which this is an add-on.
     *
     * @since [*next-version*]
     * @return PluginInterface The parent plugin instance.
     */
    public function getParent();
}