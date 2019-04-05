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
     * @param string     $postType The name of the post type.
     * @param string     $defType  The default template's type.
     * @param array|null $filter   Optional filter to restrict the collection query.
     */
    public function __construct($postType, $defType, $filter = null)
    {
        parent::__construct($postType, [], $filter);

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

    /**
     * {@inheritdoc}
     *
     * Overridden to ensure that the slug updates with the title and that the status is still "publish".
     *
     * @since [*next-version*]
     */
    protected function getUpdatePostData($key, $data)
    {
        $post = parent::getUpdatePostData($key, $data);
        // Clear the slug so WordPress re-generates it
        $post['post_name'] = '';
        $post['post_status'] = 'publish';

        return $post;
    }

    /**
     * {@inheritdoc}
     *
     * Overridden to sort the templates by title, with builtin templates served at the very top of the list.
     *
     * @since [*next-version*]
     */
    protected function queryPosts($key = null)
    {
        $posts = parent::queryPosts($key);

        usort($posts, function (WP_Post $a, WP_Post $b) {
            if ($a->wprss_template_type === '__built_in') {
                return -1;
            }

            if ($b->wprss_template_type === '__built_in') {
                return 1;
            }

            return strcmp($a->post_title, $b->post_title);
        });

        return $posts;
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function handleFilter(&$queryArgs, $key, $value)
    {
        $r = parent::handleFilter($queryArgs, $key, $value);

        if ($key === 'type') {
            $subQuery =  [
                'relation' => 'or',
                [
                    'key' => 'wprss_template_type',
                    'value' => $value,
                ]
            ];
            if ($value === 'list') {
                $subQuery[] = [
                    'key' => 'wprss_template_type',
                    'value' => $this->defType,
                ];
            }

            $queryArgs['meta_query'][] = $subQuery;

            return true;
        }

        return $r;
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function createSelfWithFilter($filter)
    {
        return new static($this->postType, $this->defType, $filter);
    }
}
