<?php

namespace RebelCode\Wpra\Core\Data\Wp;

use RebelCode\Wpra\Core\Data\ArrayDataSet;

/**
 * An implementation of a data set that acts as a wrapper for serialized arrays stored in the `wp_options` table.
 *
 * @since [*next-version*]
 */
class WpArrayOptionDataSet extends ArrayDataSet
{
    /**
     * The name of the WordPress option.
     *
     * @since [*next-version*]
     *
     * @var string
     */
    protected $optionName;

    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param string $optionName The name of the WordPress option.
     */
    public function __construct($optionName)
    {
        parent::__construct([]);

        $this->optionName = $optionName;
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function getIterator()
    {
        $this->loadData();

        return parent::getIterator();
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function get($key)
    {
        $this->loadData();

        return parent::get($key);
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function has($key)
    {
        $this->loadData();

        return parent::has($key);
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function set($key, $value)
    {
        $this->loadData();

        parent::set($key, $value);

        $this->saveData();
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function delete($key)
    {
        $this->loadData();

        parent::delete($key);

        $this->saveData();
    }

    /**
     * Loads the data from the database.
     *
     * @since [*next-version*]
     */
    protected function loadData()
    {
        $this->data = get_option($this->optionName, []);
    }

    /**
     * Saves the data to the database.
     *
     * @since [*next-version*]
     */
    protected function saveData()
    {
        update_option($this->optionName, $this->data);
    }
}
