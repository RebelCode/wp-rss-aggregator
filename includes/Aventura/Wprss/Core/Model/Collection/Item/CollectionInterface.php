<?php

namespace Aventura\Wprss\Core\Model\Collection\Item;

/**
 * A base for collection interfaces.
 *
 * Designed to be compatible with PSR-6.
 * 
 * This is extracted to its own interface because in the future,
 * ideally, a cache pool may be a list of limited collections.
 *
 * @link https://github.com/php-fig/cache/blob/master/src/CacheItemPoolInterface.php CachePoolInterface
 * @since [*next-version*]
 */
interface CollectionInterface
{
    /**
     * Get a single item.
     *
     * @since [*next-version*]
     * @param string $key The key of the item to return.
     * @throws \InvalidArgumentException If the $key string is not a legal value.
     * @return ItemCollectionItemInterface A collection item wrapper, regardless of whether or not
     *  the key has matched an existing item.
     */
    public function getItem($key);

    /**
     * Get multiple items.
     *
     * @since [*next-version*]
     * @param string[] $keys Keys of items to return. Empty array to return all items.
     * @throws InvalidArgumentException If any of the keys in $keys are not a legal value.
     * @return array|\Traversable An array of items that match the keys. Keys that do not exist will have empty items.
     *  If empty array was specified, returns all items, which can result in an empty array.
     */
    public function getItems(array $keys = array());

    /**
     * Determine whether an item with the specified key exists in this collection.
     *
     * @since [*next-version*]
     * @param string $key The key of the item to return.
     * @throws \InvalidArgumentException If the $key string is not a legal value.
     * @return boolean True if there is an item witht he specified key; false otherwise.
     */
    public function hasItem($key);

    /**
     * Delete all items from this collection.
     *
     * @since [*next-version*]
     * @return bool True if the pool was successfully cleared; false if there was an error.
     */
    public function clear();

    /**
     * Delete an item with the specified key from this collection.
     *
     * @since [*next-version*]
     * @param string $key The key of the item to delete
     * @throws \InvalidArgumentException If the $key string is not a legal value.
     * @return bool True if item deleted successfully; false otherwise.
     */
    public function deleteItem($key);

    /**
     * Delete items with specified keys from this collection.
     *
     * @since [*next-version*]
     * @param array[] $keys
     * @throws \InvalidArgumentException If any of the keys in $keys are not a legal value.
     * @return bool True if the items were successfully removed; false if there was an error.
     */
    public function deleteItems(array $keys);
}