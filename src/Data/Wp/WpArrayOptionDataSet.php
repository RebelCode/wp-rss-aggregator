<?php

namespace RebelCode\Wpra\Core\Data\Wp;

use RebelCode\Wpra\Core\Data\ArrayDataSet;

/**
 * An implementation of a data set that acts as a wrapper for serialized arrays stored in the `wp_options` table.
 *
 * @since 4.13
 */
class WpArrayOptionDataSet extends ArrayDataSet
{
    /**
     * The name of the WordPress option.
     *
     * @since 4.13
     *
     * @var string
     */
    protected $optionName;

    /**
     * Constructor.
     *
     * @since 4.13
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
     * @since 4.13
     */
    protected function getIterator()
    {
        $this->loadData();

        return parent::getIterator();
    }

    /**
     * {@inheritdoc}
     *
     * @since 4.13
     */
    protected function get($key)
    {
        $this->loadData();

        return parent::get($key);
    }

    /**
     * {@inheritdoc}
     *
     * @since 4.13
     */
    protected function has($key)
    {
        $this->loadData();

        return parent::has($key);
    }

    /**
     * {@inheritdoc}
     *
     * @since 4.13
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
     * @since 4.13
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
     * @since 4.13
     */
    protected function loadData()
    {
        $this->data = get_option($this->optionName, []);
    }

    /**
     * Saves the data to the database.
     *
     * @since 4.13
     */
    protected function saveData()
    {
        update_option($this->optionName, $this->data);
    }
}
