<?php

namespace Aventura\Wprss\Core\Model\Collection;

/**
 * Represents some kind of collection with loose implementation rules.
 *
 * A way for collectoins of different kinds to interoperate without concern for their rules.
 *
 * @since [*next-version*]
 */
interface CollectionInteropInterface
{
    /**
     * An implementation-agnostic way to populate any kind of collection with values.
     *
     * @since [*next-version*]
     * @param array $values The values, with which to populate the collection.
     *  The importance of keys is up to the implementation.
     *  The values must be the actual values to store.
     */
    public function populate(array $values);

    /**
     * An implementation-agnostic way to get the raw items of this collection in a key-value map.
     *
     * @since [*next-version*]
     * @return array A key-value map of this collection's items.
     */
    public function items();

    /**
     * An implementation-agnostic way to retrieve a value from this collection based on an item's representation.
     *
     * @since [*next-version*]
     * @param mixed $item An item identifier or representation, which to retrieve.
     * @return mixed The actual value from this collection, as identified by the parameter.
     */
    public function get($item, $default = null);

    /**
     * An implementation-agnostic way to add an item to this collection based on this item's comprehensive representation.
     *
     * @since [*next-version*]
     * @param mixed $item A comprehensive representation of an item, which to add to this collection.
     */
    public function add($item);

    /**
     * An implementation-agnostic way to remove an item from this collection based on the item's representation.
     *
     * @since [*next-version*]
     * @param mixed $item
     */
    public function remove($item);

    /**
     * An implementation-agnostic way to get the total amount of items in this collection
     *
     * @since [*next-version*]
     * @return int The number of items in this collection
     */
    public function count();

    /**
     * Determines whether this collection contains the given item.
     *
     * @since [*next-version*]
     * @param mixed $item The item to check for.
     * @return boolean True if this collection contains the given item; false otherwise.
     */
    public function has($item);

    /**
     * Remove all items from this collection.
     *
     * @since [*next-version*]
     */
    public function clear();
}
