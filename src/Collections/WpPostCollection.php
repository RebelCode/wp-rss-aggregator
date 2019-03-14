<?php

namespace RebelCode\Wpra\Core\Collections;

use ArrayIterator;
use OutOfRangeException;
use RebelCode\Wpra\Core\Data\AbstractDataSet;
use RebelCode\Wpra\Core\Data\DataSetInterface;
use RebelCode\Wpra\Core\Data\WpPostDataSet;
use WP_Post;

/**
 * A data set implementation that acts as a wrapper for a collection of posts,
 *
 * @since [*next-version*]
 */
class WpPostCollection extends AbstractDataSet
{
    /**
     * The post type.
     *
     * @since [*next-version*]
     *
     * @var string
     */
    protected $postType;

    /**
     * The meta query.
     *
     * @since [*next-version*]
     *
     * @var array
     */
    protected $metaQuery;

    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param string $postType  The post type.
     * @param array  $metaQuery The meta query.
     */
    public function __construct($postType, $metaQuery = [])
    {
        $this->postType = $postType;
        $this->metaQuery = $metaQuery;
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function get($key)
    {
        $posts = $this->queryPosts($key);

        if (count($posts) === 0) {
            throw new OutOfRangeException(
                sprintf(__('Post with %s "%s" was not found', 'wprss'), $this->getPostQueryKey(), $key)
            );
        }

        $post = reset($posts);
        $result = $this->createResult($post);

        return $result;
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function has($key)
    {
        $posts = $this->queryPosts($key);

        return count($posts) === 1;
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function set($key, $value)
    {
        $postArray = $value;
        $postArray[$this->getPostQueryKey()] = $key;

        wp_update_post($postArray);
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function delete($key)
    {
        wp_delete_post($key, true);
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function getIterator()
    {
        return new ArrayIterator($this->queryPosts());
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function current()
    {
        return $this->createResult(parent::current());
    }

    /**
     * Queries the posts.
     *
     * @since [*next-version*]
     *
     * @param int|string|null $id Optional ID which if not null narrows down the query to only that post.
     *
     * @return WP_Post[] An array of posts objects.
     */
    protected function queryPosts($id = null)
    {
        $queryArgs = [
            'post_type' => $this->postType,
            'cache_results' => true,
            'posts_per_page' => -1,
            'meta_query' => $this->metaQuery,
        ];

        if ($id !== null) {
            $queryArgs[$this->getPostQueryKey()] = $id;
        }

        return get_posts($queryArgs);
    }

    /**
     * Retrieves the post key to use when querying for particular posts.
     *
     * @since [*next-version*]
     *
     * @return string
     */
    protected function getPostQueryKey()
    {
        return 'ID';
    }

    /**
     * Creates the resulting data set.
     *
     * @since [*next-version*]
     *
     * @param WP_Post $post The post.
     *
     * @return DataSetInterface The resulting data set.
     */
    protected function createResult(WP_Post $post)
    {
        return new WpPostDataSet($post);
    }
}
