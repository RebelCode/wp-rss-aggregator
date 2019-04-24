<?php

namespace RebelCode\Wpra\Core\Data;

use RebelCode\Wpra\Core\Util\IteratorDelegateTrait;
use RebelCode\Wpra\Core\Util\MergedIterator;
use RuntimeException;
use stdClass;
use Traversable;

/**
 * A data set implementation that groups multiple data sets together as one.
 *
 * This implementation has the following properties:
 * - Data is read from the first child that has the requested key.
 * - Existence checking is performed on all children and if at least one child has the key, true is returned.
 * - Data writing and deletion is performed on all children.
 * - All children are used during iteration, yielding the aggregated data among all of them without duplicate keys.
 *
 * This data set is also useful for synchronizing multiple data sets. By iterating the composite data set, consumers
 * get access to the aggregate data from all the children data sets. By setting each key-value pair on the composite
 * data set, each child dataset will receive that key-value pair.
 *
 * @since 4.13
 */
class CompositeDataSet implements DataSetInterface
{
    /* @since 4.13 */
    use IteratorDelegateTrait;

    /**
     * Additional data sets to write to and delete from.
     *
     * @since 4.13
     *
     * @var DataSetInterface[]
     */
    protected $dataSets;

    /**
     * Constructor.
     *
     * @since 4.13
     *
     * @param DataSetInterface[]|stdClass|Traversable $dataSets The children data sets instances.
     */
    public function __construct($dataSets)
    {
        $this->dataSets = $dataSets;
    }

    /**
     * {@inheritdoc}
     *
     * @since 4.13
     */
    public function offsetGet($key)
    {
        foreach ($this->dataSets as $dataSet) {
            if ($dataSet->offsetExists($key)) {
                return $dataSet->offsetGet($key);
            }
        }

        throw new RuntimeException(sprintf('Entry with key "%s" was not found in the data set', $key));
    }

    /**
     * {@inheritdoc}
     *
     * @since 4.13
     */
    public function offsetExists($key)
    {
        foreach ($this->dataSets as $dataSet) {
            if ($dataSet->offsetExists($key)) {
                return true;
            }
        }

        return false;
    }

    /**
     * {@inheritdoc}
     *
     * @since 4.13
     */
    public function offsetSet($key, $value)
    {
        foreach ($this->dataSets as $dataSet) {
            $dataSet->offsetSet($key, $value);
        }
    }

    /**
     * {@inheritdoc}
     *
     * @since 4.13
     */
    public function offsetUnset($key)
    {
        foreach ($this->dataSets as $dataSet) {
            $dataSet->offsetUnset($key);
        }
    }

    /**
     * {@inheritdoc}
     *
     * @since 4.13
     */
    protected function getIterator()
    {
        return new MergedIterator($this->dataSets);
    }
}
