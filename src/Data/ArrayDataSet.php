<?php

namespace RebelCode\Wpra\Core\Data;

use ArrayAccess;
use ArrayIterator;
use RebelCode\Wpra\Core\Util\Normalize;
use stdClass;

/**
 * A data set implementation that uses a static array or object data store.
 *
 * @since 4.13
 */
class ArrayDataSet extends AbstractDataSet
{
    /**
     * The options data as an associative array.
     *
     * @since 4.13
     *
     * @var array
     */
    protected $data;

    /**
     * @since 4.14
     *
     * @var bool
     */
    protected $recursive;

    /**
     * Constructor.
     *
     * @since 4.13
     *
     * @param iterable|stdClass $data     The data store, as an associative array, object or iterator.
     * @param bool             $recursive Whether to recursively set data to children data sets.
     */
    public function __construct($data, $recursive = false)
    {
        $this->data = Normalize::toArray($data);
        $this->recursive = $recursive;
    }

    /**
     * {@inheritdoc}
     *
     * @since 4.13
     */
    protected function get($key)
    {
        return $this->data[$key];
    }

    /**
     * {@inheritdoc}
     *
     * @since 4.13
     */
    protected function has($key)
    {
        return array_key_exists($key, (array) $this->data);
    }

    /**
     * {@inheritdoc}
     *
     * @since 4.13
     */
    protected function set($key, $value)
    {
        if ($this->recursive && (is_array($value) || $value instanceof ArrayAccess)) {
            foreach ($value as $subKey => $subValue) {
                $this->data[$key][$subKey] = $subValue;
            }

            return;
        }

        $this->data[$key] = $value;
    }

    /**
     * {@inheritdoc}
     *
     * @since 4.13
     */
    protected function delete($key)
    {
        unset($this->data[$key]);
    }

    /**
     * {@inheritdoc}
     *
     * @since 4.13
     */
    protected function getIterator()
    {
        return new ArrayIterator($this->data);
    }
}
