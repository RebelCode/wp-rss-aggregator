<?php

namespace RebelCode\Wpra\Core\Modules\Handlers\FeedTemplates;

use stdClass;
use Traversable;

/**
 * The handler that auto creates the default feed template.
 *
 * @since 4.13
 */
class CreateDefaultFeedTemplateHandler
{
    /**
     * Description
     *
     * @since 4.13
     *
     * @var array|Traversable
     */
    protected $collection;

    /**
     * The data to use for creating the default feed template.
     *
     * @since 4.13
     *
     * @var array
     */
    protected $data;

    /**
     * Constructor.
     *
     * @since 4.13
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
     * @since 4.13
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
