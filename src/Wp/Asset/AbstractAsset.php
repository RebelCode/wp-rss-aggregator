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
     * WordPress version. If set to null, no version is added.
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
    protected $dependencies = [];

    /**
     * AbstractAsset constructor.
     *
     * @since [*next-version*]
     *
     * @param string $handle Asset's unique name.
     * @param string $src The URL of the asset.
     */
    public function __construct($handle, $src)
    {
        $this->handle = $handle;
        $this->src = $src;
    }

    /**
     * Sets the version of the asset.
     *
     * @since [*next-version*]
     *
     * @param string|bool|null $version
     *
     * @return $this
     */
    public function setVersion($version)
    {
        $this->version = $version;
        return $this;
    }

    /**
     * Sets the names of dependencies.
     *
     * @since [*next-version*]
     *
     * @param string[] $dependencies
     *
     * @return $this
     */
    public function setDependencies($dependencies)
    {
        $this->dependencies = $dependencies;
        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    abstract public function enqueue();
}
