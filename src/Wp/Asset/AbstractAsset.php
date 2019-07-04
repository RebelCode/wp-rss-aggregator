<?php

namespace RebelCode\Wpra\Core\Wp\Asset;

/**
 * {@inheritdoc}
 *
 * @since [*next-version*]
 */
abstract class AbstractAsset implements AssetInterface
{
    /**
     * Name of the asset. Should be unique.
     *
     * @since [*next-version*]
     *
     * @var string
     */
    protected $handle;

    /**
     * Full URL of the asset, or path of the asset relative to the WordPress root directory.
     *
     * @since [*next-version*]
     *
     * @var string
     */
    protected $src;

    /**
     * String specifying asset version number, if it has one, which is added to the URL as a query string for cache
     * busting purposes. If version is set to false, a version number is automatically added equal to current installed
     * WP RSS Aggregator version. If set to null, no version is added.
     *
     * @since [*next-version*]
     *
     * @var string|bool|null
     */
    protected $version = false;

    /**
     * An array of registered handles this asset depends on.
     *
     * @since [*next-version*]
     *
     * @var string[]
     */
    protected $deps = [];

    /**
     * AbstractAsset constructor.
     *
     * @since [*next-version*]
     *
     * @param string $handle  Asset's unique name.
     * @param string $src     The URL of the asset.
     * @param array  $deps    The list of asset handles for this asset's dependencies.
     * @param bool   $version String specifying asset version number.
     */
    public function __construct($handle, $src, $deps = [], $version = false)
    {
        $this->handle = $handle;
        $this->src = $src;
        $this->version = ($version === false) ? WPRSS_VERSION : $version;
        $this->deps = $deps;
    }
}
