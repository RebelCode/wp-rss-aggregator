<?php

namespace RebelCode\Wpra\Core\Data;

use ArrayIterator;
use stdClass;

/**
 * A data set implementation that uses a static array or object data store.
 *
 * @since [*next-version*]
 */
class ArrayDataSet extends AbstractInheritingDataSet
{
    /**
     * The options data as an associative array.
     *
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
     * @param array|stdClass        $data    The data store, as an associative array or object.
     * @param array                 $aliases A mapping of input keys to the actual data store keys.
     * @param DataSetInterface|null $parent  Optional parent data set to inherit from.
     */
    public function __construct($data, $aliases = [], DataSetInterface $parent = null)
    {
        parent::__construct($aliases, $parent);

        $this->data = (array) $data;
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function get($key)
    {
        return $this->data[$key];
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function has($key)
    {
        return array_key_exists($key, $this->data);
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function set($key, $value)
    {
        $this->data[$key] = $value;
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function delete($key)
    {
        unset($this->data[$key]);
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function getIterator()
    {
        return new ArrayIterator($this->data);
    }
}
