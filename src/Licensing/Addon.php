<?php

namespace RebelCode\Wpra\Core\Licensing;

/**
 * A class for standard WP RSS Aggregator addon class.
 *
 * @since [*next-version*]
 */
class Addon
{
    /**
     * @since [*next-version*]
     *
     * @var string
     */
    public $key;

    /**
     * @since [*next-version*]
     *
     * @var string
     */
    public $name;

    /**
     * @since [*next-version*]
     *
     * @var string
     */
    public $version;

    /**
     * @since [*next-version*]
     *
     * @var string
     */
    public $filePath;

    /**
     * @since [*next-version*]
     *
     * @var string
     */
    public $storeUrl;

    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param string $key The addon key.
     * @param string $name The addon name.
     * @param string $version The addon plugin version.
     * @param string $filePath The addon plugin file ath.
     * @param string $storeUrl The addon licensing store URL.
     */
    public function __construct($key, $name, $version, $filePath, $storeUrl)
    {
        $this->key = $key;
        $this->name = $name;
        $this->version = $version;
        $this->filePath = $filePath;
        $this->storeUrl = $storeUrl;
    }
}
