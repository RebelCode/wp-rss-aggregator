<?php

namespace RebelCode\Wpra\Core\Handlers\Templates;

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
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param array|Traversable $collection The templates collection.
     */
    public function __construct($collection)
    {
        $this->collection = $collection;
    }

    /**
     * @since [*next-version*]
     */
    public function __invoke()
    {
        $count = (is_array($this->collection) || $this->collection instanceof stdClass)
            ? count((array)$this->collection)
            : iterator_count($this->collection);

        if ($count === 0) {
            wp_insert_post([
                'post_type' => WPRSS_FEED_TEMPLATE_CPT,
                'post_title' => __('Default'),
                'post_name' => 'default',
                'post_status' => 'publish',
                'meta_input' => [
                    'wprss_template_type' => '__built_in',
                ],
            ]);
        }
    }
}
