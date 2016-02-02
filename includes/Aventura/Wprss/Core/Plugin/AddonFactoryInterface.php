<?php

namespace Aventura\Wprss\Core\Plugin;

/**
 * An interface for something that creates add-ons.
 *
 * @since [*next-version*]
 */
interface AddonFactoryInterface extends FactoryInterface
{

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     * @return AddonInterface
     */
    static public function create();

    /**
     * Get the parent of the add-ons created by this interface.
     *
     * @since [*next-version*]
     * @return PluginInterface
     */
    public function getParent();
}