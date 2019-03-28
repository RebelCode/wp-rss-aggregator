<?php

namespace RebelCode\Wpra\Core\Database;

use RebelCode\Wpra\Core\Data\Collections\CollectionInterface;

/**
 * Interface for objects that represent a database table.
 *
 * @since [*next-version*]
 */
interface TableInterface extends CollectionInterface
{
    /**
     * The filter for specifying the limit.
     *
     * @since [*next-version*]
     */
    const FILTER_LIMIT = 'limit';

    /**
     * The filter for specifying the offset.
     *
     * @since [*next-version*]
     */
    const FILTER_OFFSET = 'offset';

    /**
     * The filter for specifying the field to order by.
     *
     * @since [*next-version*]
     */
    const FILTER_ORDER_BY = 'order_by';

    /**
     * The filter for specifying the order mode.
     *
     * @since [*next-version*]
     */
    const FILTER_ORDER = 'order';

    /**
     * The filter for specifying arbitrary WHERE conditions.
     *
     * @since [*next-version*]
     */
    const FILTER_WHERE = 'where';

    /**
     * Creates the table if it does not exist in the database.
     *
     * @since [*next-version*]
     */
    public function create();

    /**
     * Drops the table if it exists in the database.
     *
     * @since [*next-version*]
     */
    public function drop();
}
