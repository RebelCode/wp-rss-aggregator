<?php

namespace RebelCode\Wpra\Core\Data\Collections;

use RebelCode\Wpra\Core\Data\DataSetInterface;
use stdClass;
use Traversable;

/**
 * Interface for objects that act as collections.
 *
 * @since [*next-version*]
 */
interface CollectionInterface extends DataSetInterface
{
    /**
     * Filters the contents of the dataset for entries that match a given filter.
     *
     * @since [*next-version*]
     *
     * @param array|stdClass|Traversable $filters A list of filters as key-value pairs, where the key represents the
     *                                            filter identifier and the value represents the value to filter by.
     *
     * @return CollectionInterface The new collection instance.
     */
    public function filter($filters);

    /**
     * Retrieves the total number of entries in the collection.
     *
     * @since [*next-version*]
     *
     * @return int An integer number of entries.
     */
    public function getCount();

    /**
     * Clears the contents of the collection by deleting all the entries.
     *
     * @since [*next-version*]
     */
    public function clear();
}
