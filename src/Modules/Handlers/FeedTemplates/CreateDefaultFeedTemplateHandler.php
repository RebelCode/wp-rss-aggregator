<?php

namespace RebelCode\Wpra\Core\Modules\Handlers\FeedTemplates;

use stdClass;
use Traversable;

/**
 * The handler that auto creates the default feed template.
 *
 * @since [*next-version*]
 */
class CreateDefaultFeedTemplateHandler
{
    /**
     * Description
     *
     * @since [*next-version*]
     *
     * @var array|Traversable
     */
    protected $collection;

    /**
     * The data to use for creating the default feed template.
     *
     * @since [*next-version*]
     *
     * @var array
     */
    protected $data;

    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param array|Traversable $collection The feed templates collection.
     * @param array             $data       The data to use for creating the default feed template.
     */
    public function __construct($collection, $data)
    {
        $this->collection = $collection;
        $this->data = $data;
    }

    /**
     * @since [*next-version*]
     */
    public function __invoke()
    {
        $count = (is_array($this->collection) || $this->collection instanceof stdClass)
            ? count((array) $this->collection)
            : iterator_count($this->collection);

        if ($count === 0) {
            $this->collection[] = $this->data;
        }
    }
}
