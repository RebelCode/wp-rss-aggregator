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
     * Searches the dataset for matching entries.
     *
     * @since [*next-version*]
     *
     * @param mixed $search The search terms, arguments or options.
     *
     * @return DataSetInterface[]|stdClass|Traversable The search results.
     */
    public function search($search);
}
