<?php

namespace RebelCode\Wpra\Core\Templates\Feeds;

use RebelCode\Wpra\Core\Data\Collections\WpPostCollection;
use RebelCode\Wpra\Core\Templates\Feeds\Models\BuiltInFeedTemplate;
use RebelCode\Wpra\Core\Templates\Feeds\Models\WpPostFeedTemplate;
use WP_Post;

/**
 * A posts collection for WP RSS Aggregator feed templates.
 *
 * @since [*next-version*]
 */
class FeedTemplateCollection extends WpPostCollection
{
    /**
     * The default template's type.
     *
     * @since [*next-version*]
     *
     * @var string
     */
    protected $defType;

    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param string      $postType The name of the post type.
     * @param string      $defType  The default template's type.
     * @param string|null $search   Optional search term or null to use the full posts collection.
     */
    public function __construct($postType, $defType, $search = null)
    {
        parent::__construct($postType, [], $search);

        $this->defType = $defType;
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function createModel(WP_Post $post)
    {
        $model = new WpPostFeedTemplate($post);

        if (isset($model['type']) && $model['type'] === $this->defType) {
            return new BuiltInFeedTemplate($post);
        }

        return $model;
    }

    /**
     * {@inheritdoc}
     *
     * Overridden to ensure that the title is set (for auto slug generation) and the status is "publish".
     *
     * @since [*next-version*]
     */
    protected function getNewPostData($data)
    {
        $post = parent::getNewPostData($data);
        $post['post_title'] = isset($data['name']) ? $data['name'] : '';
        $post['post_status'] = 'publish';

        return $post;
    }
}
