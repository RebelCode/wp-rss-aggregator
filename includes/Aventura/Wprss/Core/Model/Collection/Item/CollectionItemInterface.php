<?php

namespace Aventura\Wprss\Core\Model\Collection\Item;

/**
 * Represents an item of an item-object-based collection.
 *
 * Designed to be compatible with PSR-6.
 *
 * @link https://github.com/php-fig/cache/blob/master/src/CacheItemInterface.php CacheItemInterface
 *
 * @since [*next-version*]
 */
interface CollectionItemInterface
{
    /**
     * Get this item's key.
     *
     * @since [*next-version*]
     * @return string The key for this item's value;
     */
    public function getKey();

    /**
     * Get this item's value.
     *
     * @since [*next-version*]
     * @return mixed The value of this item.
     */
    public function get();

    /**
     * Determine if the retrieved item exists in its collection.
     *
     * @since [*next-version*]
     * @return boolean True if this item's key corresponds to a value in the collection, from which
     *  it was retrieved; false otherwise.
     */
    public function isHit();

    /**
     * Set the value of this item.
     *
     * @since [*next-version*]
     * @param mixed $value The value for this item
     * @return ItemCollectionItemInterface This instance.
     */
    public function set($value);
}