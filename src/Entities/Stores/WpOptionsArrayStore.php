<?php

namespace RebelCode\Wpra\Core\Entities\Stores;

use OutOfBoundsException;
use RebelCode\Entities\Api\StoreInterface;

/**
 * A store implementation for an array stored in WordPress' wp_options table.
 *
 * @since [*next-version*]
 */
class WpOptionsArrayStore implements StoreInterface
{
    /**
     * @since [*next-version*]
     *
     * @var string
     */
    protected $option;

    /**
     * @since [*next-version*]
     *
     * @var array
     */
    protected $data;

    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param string $option The name of the option.
     */
    public function __construct($option)
    {
        $this->option = $option;
        $this->data = get_option($option, []);
    }

    /**
     * @inheritdoc
     *
     * @since [*next-version*]
     */
    public function get($key)
    {
        if (!array_key_exists($key, $this->data)) {
            throw new OutOfBoundsException(sprintf('Option "%s" does not have key "%s"', $this->option, $key));
        }

        return $this->data[$key];
    }

    /**
     * @inheritdoc
     *
     * @since [*next-version*]
     */
    public function has($key)
    {
        return array_key_exists($key, $this->data);
    }

    /**
     * @inheritdoc
     *
     * @since [*next-version*]
     */
    public function set(array $data)
    {
        $instance = clone $this;
        $instance->data = array_merge($this->data, $data);

        update_option($this->option, $instance->data);

        return $instance;
    }
}
