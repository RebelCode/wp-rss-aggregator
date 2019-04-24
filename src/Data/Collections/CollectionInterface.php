<?php

namespace RebelCode\Wpra\Core\Data\Collections;

use RebelCode\Wpra\Core\Data\DataSetInterface;
use stdClass;
use Traversable;

/**
 * Interface for objects that act as collections.
 *
 * @since 4.13
 */
interface CollectionInterface extends DataSetInterface
{
    /**
     * Filters the contents of the dataset for entries that match a given filter.
     *
     * Collection filtering is guaranteed to be idempotent. Collections may implement this method to return collections
     * that may be filtered further. If this is case, it should be assumed that the filters given to a collection are
     * used in an AND relationship with any filters currently assigned to the current collection instance.
     *
     * @since 4.13
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
     * @since 4.13
     *
     * @return int An integer number of entries.
     */
    public function getCount();

    /**
     * Clears the contents of the collection by deleting all the entries.
     *
     * @since 4.13
     */
    public function clear();
}
