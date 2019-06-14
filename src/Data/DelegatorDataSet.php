<?php

namespace RebelCode\Wpra\Core\Data;

use ArrayAccess;
use RebelCode\Wpra\Core\Util\IteratorDelegateTrait;
use RebelCode\Wpra\Core\Util\MergedIterator;
use RuntimeException;

/**
 * A data set implementation that delegates functionality to one of many children data sets, determined by a callback.
 *
 * @since [*next-version*]
 */
class DelegatorDataSet implements DataSetInterface
{
    /* @since [*next-version*] */
    use IteratorDelegateTrait;

    /**
     * The children data set mapping.
     *
     * @since [*next-version*]
     *
     * @var DataSetInterface[]|ArrayAccess
     */
    protected $children;

    /**
     * The callable that maps a key to a data set.
     *
     * @since [*next-version*]
     *
     * @var callable
     */
    protected $mappingFn;

    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param ArrayAccess|DataSetInterface[] $children  The mapping of data set keys to data set instances.
     * @param callable                       $mappingFn The mapping function to use to determine which data set to
     *                                                  use for which keys.
     */
    public function __construct($children, callable $mappingFn)
    {
        $this->children = $children;
        $this->mappingFn = $mappingFn;
    }

    /**
     * Retrieves the child data set to use for the given key.
     *
     * @since [*next-version*]
     *
     * @param int|string $key The data key.
     *
     * @return DataSetInterface The data set instance to use.
     *
     * @throws RuntimeException If the key returned by the callable does not correspond to a child data set.
     */
    protected function getChildDataSetFor($key)
    {
        $dataSetKey = call_user_func_array($this->mappingFn, [$key]);

        if (!isset($this->children[$dataSetKey])) {
            throw new RuntimeException(sprintf('Child data set "%s" does not exist', $dataSetKey));
        }

        return $this->children[$dataSetKey];
    }

    /**
     * @inheritdoc
     *
     * @since [*next-version*]
     */
    public function offsetGet($key)
    {
        return $this->getChildDataSetFor($key)->offsetGet($key);
    }

    /**
     * @inheritdoc
     *
     * @since [*next-version*]
     */
    public function offsetExists($key)
    {
        return $this->getChildDataSetFor($key)->offsetExists($key);
    }

    /**
     * @inheritdoc
     *
     * @since [*next-version*]
     */
    public function offsetSet($key, $value)
    {
        $this->getChildDataSetFor($key)->offsetSet($key, $value);
    }

    /**
     * @inheritdoc
     *
     * @since [*next-version*]
     */
    public function offsetUnset($key)
    {
        $this->getChildDataSetFor($key)->offsetUnset($key);
    }

    /**
     * @inheritdoc
     *
     * @since [*next-version*]
     */
    protected function getIterator()
    {
        return new MergedIterator($this->children);
    }

    /**
     * Creates a mapping function that uses a fixed map of keys.
     *
     * @since [*next-version*]
     *
     * @param string[]|ArrayAccess $keyMap  The mapping of entry keys to child data set keys.
     * @param string               $default The default child data set key to use.
     *
     * @return callable
     */
    public static function fixedMap($keyMap, $default)
    {
        return function ($key) use ($keyMap, $default) {
            if (isset($keyMap[$key])) {
                return $keyMap[$key];
            }

            return $default;
        };
    }
}
