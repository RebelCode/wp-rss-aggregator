<?php

namespace RebelCode\Wpra\Core\Wp\Asset;

/**
 * Interface for script assets.
 *
 * @since [*next-version*]
 */
interface ScriptInterface extends AssetInterface
{
    /**
     * Localizes the script with given data.
     *
     * @since [*next-version*]
     *
     * @param string   $key      The key, also used for the JS var name.
     * @param callable $callback The callback that returns the localization data.
     *
     * @return AssetInterface A copy of this instance with the localized data.
     */
    public function localize($key, callable $callback);
}
