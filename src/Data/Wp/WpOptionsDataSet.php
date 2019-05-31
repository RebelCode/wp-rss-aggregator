<?php

namespace RebelCode\Wpra\Core\Data\Wp;

use Dhii\Collection\CallbackIterator;
use RebelCode\Wpra\Core\Data\AbstractDataSet;
use RebelCode\Wpra\Core\Data\DataSetInterface;

/**
 * A data set implementation for WordPress options.
 *
 * @since [*next-version*]
 */
class WpOptionsDataSet extends AbstractDataSet
{
    /**
     * An associative array of option names as keys and their defaults as values.
     *
     * @since [*next-version*]
     *
     * @var DataSetInterface
     */
    protected $options;

    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param DataSetInterface $options A data set with option names as keys and their defaults as values.
     */
    public function __construct($options)
    {
        $this->options = $options;
    }

    /**
     * @inheritdoc
     *
     * @since [*next-version*]
     */
    protected function get($key)
    {
        return get_option($key, $this->options[$key]);
    }

    /**
     * @inheritdoc
     *
     * @since [*next-version*]
     */
    protected function has($key)
    {
        return isset($key, $this->options);
    }

    /**
     * @inheritdoc
     *
     * @since [*next-version*]
     */
    protected function set($key, $value)
    {
        update_option($key, $value);
    }

    /**
     * Deletes a specific entry by key.
     *
     * @since 4.13
     *
     * @param string $key The key of the entry to delete.
     */
    protected function delete($key)
    {
        delete_option($key);
    }

    /**
     * @inheritdoc
     *
     * @since [*next-version*]
     */
    protected function getIterator()
    {
        return new CallbackIterator($this->options, function ($key) {
            return $this->get($key);
        });
    }
}
