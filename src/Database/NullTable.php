<?php

namespace RebelCode\Wpra\Core\Database;

use RebelCode\Wpra\Core\Data\Collections\NullCollection;

/**
 * A null implementation of a table.
 *
 * @since [*next-version*]
 */
class NullTable extends NullCollection implements TableInterface
{
    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function create()
    {
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function drop()
    {
    }
}
