<?php

namespace RebelCode\Wpra\Core\Collections;

use RebelCode\Wpra\Core\Models\FeedTemplate;
use WP_Post;

/**
 * A posts collection for WP RSS Aggregator feed templates.
 *
 * @since [*next-version*]
 */
class FeedTemplateCollection extends WpPostCollection
{
    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     */
    public function __construct()
    {
        parent::__construct(WPRSS_FEED_TEMPLATE_CPT, []);
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function createResult(WP_Post $post)
    {
        return new FeedTemplate($post);
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function getPostQueryKey()
    {
        return 'name';
    }
}
