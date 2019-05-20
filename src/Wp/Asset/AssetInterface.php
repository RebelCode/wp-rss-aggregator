<?php

namespace RebelCode\Wpra\Core\Wp\Asset;

/**
 * Interface AssetInterface
 *
 * Assets provide ability to to include styles or scripts.
 *
 * @since [*next-version*]
 */
interface AssetInterface
{
    /**
     * Enqueue current asset.
     *
     * @since [*next-version*]
     *
     * @return void
     */
    public function enqueue();
}
