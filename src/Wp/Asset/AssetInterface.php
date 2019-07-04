<?php

namespace RebelCode\Wpra\Core\Wp\Asset;

/**
 * Interface for UI assets.
 *
 * Assets provide ability to to include styles or scripts.
 *
 * @since [*next-version*]
 */
interface AssetInterface
{
    /**
     * Registers the asset.
     *
     * @since [*next-version*]
     */
    public function register();

    /**
     * Enqueues the asset.
     *
     * @since [*next-version*]
     */
    public function enqueue();
}
