<?php

namespace RebelCode\Wpra\Core\Data\Collections;

use RebelCode\Wpra\Core\Templates\Models\FeedTemplate;
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
    protected function createModel(WP_Post $post)
    {
        return new FeedTemplate($post);
    }

    /**
     * {@inheritdoc}
     *
     * Overridden to ensure that the status is "publish".
     *
     * @since [*next-version*]
     */
    protected function getNewPostData($data)
    {
        $data = parent::getNewPostData($data);
        $data['post_status'] = 'publish';

        return $data;
    }
}
