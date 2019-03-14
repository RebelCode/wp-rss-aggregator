<?php

namespace RebelCode\Wpra\Core\Collections;

use ArrayAccess;
use ArrayIterator;
use Dhii\Exception\CreateInvalidArgumentExceptionCapableTrait;
use Dhii\I18n\StringTranslatingTrait;
use Dhii\Util\Normalization\NormalizeArrayCapableTrait;
use Exception;
use OutOfRangeException;
use RebelCode\Wpra\Core\Data\AbstractDataSet;
use RebelCode\Wpra\Core\Data\DataSetInterface;
use RebelCode\Wpra\Core\Data\WpPostDataSet;
use RuntimeException;
use stdClass;
use Traversable;
use WP_Error;
use WP_Post;

/**
 * A data set implementation that acts as a wrapper for a collection of posts,
 *
 * @since [*next-version*]
 */
class WpPostCollection extends AbstractDataSet
{
    /* @since [*next-version*] */
    use NormalizeArrayCapableTrait;

    /* @since [*next-version*] */
    use CreateInvalidArgumentExceptionCapableTrait;

    /* @since [*next-version*] */
    use StringTranslatingTrait;

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
     * The ID of the last inserted post.
     *
     * @since [*next-version*]
     *
     * @var int|string
     */
    protected $lastInsertedId;

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
        if ($key === null && $this->lastInsertedId !== null) {
            return $this->get($this->lastInsertedId);
        }

        $posts = $this->queryPosts($key);

        if (count($posts) === 0) {
            throw new OutOfRangeException(
                sprintf(__('Post "%s" was not found', 'wprss'), $key)
            );
        }

        $post = reset($posts);
        $result = $this->createModel($post);

        return $result;
    }

    /**
     * {@inheritdoc}
     *
     * Overridden to remove the existence check to prevent double-queries and to allow retrieval with a null `$key`,
     * since existence checking with a null `$key` always returns false.
     *
     * @since [*next-version*]
     */
    public function offsetGet($key)
    {
        try {
            return $this->get($key);
        } catch (Exception $exception) {
            throw new RuntimeException(
                sprintf('An error occurred while reading the value for the "%s" key', $key), null, $exception
            );
        }
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function has($key)
    {
        if ($key === null) {
            return false;
        }

        $posts = $this->queryPosts($key);

        return count($posts) === 1;
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function set($key, $data)
    {
        if ($key === null) {
            $this->create($data);

            return;
        }

        $this->update($key, $data);
    }

    /**
     * Creates a new post using the given data.
     *
     * @since [*next-version*]
     *
     * @param array $data The data to create the post with.
     */
    protected function create($data)
    {
        $post = $this->getNewPostData($data);
        $result = wp_insert_post($post, true);

        if ($result instanceof WP_Error) {
            throw new RuntimeException($result->get_error_message(), $result->get_error_code());
        }

        $this->lastInsertedId = $result;
        $this->update($result, $data);
    }

    /**
     * Updates a post.
     *
     * @since [*next-version*]
     *
     * @param int|string $key  The post's key (ID or slug).
     * @param array      $data The data to update the post with.
     */
    protected function update($key, $data)
    {
        $post = $this->get($key);
        $data = $this->getUpdatePostData($key, $data);

        foreach ($data as $k => $v) {
            $post[$k] = $v;
        }
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
        return $this->createModel(parent::current());
    }

    /**
     * Queries the posts.
     *
     * @since [*next-version*]
     *
     * @param int|string|null $key Optional ID or slug which, if not null, narrows down the query to only that post.
     *
     * @return WP_Post[] An array of posts objects.
     */
    protected function queryPosts($key = null)
    {
        $queryArgs = [
            'post_type' => $this->postType,
            'suppress_filters' => true,
            'cache_results' => false,
            'posts_per_page' => -1,
            'meta_query' => $this->metaQuery,
        ];

        if ($key !== null && is_numeric($key)) {
            $queryArgs['p'] = $key;
        }

        if ($key !== null && is_string($key) && !is_numeric($key)) {
            $queryArgs['name'] = $key;
        }

        return get_posts($queryArgs);
    }

    /**
     * Creates the resulting dataset model.
     *
     * @since [*next-version*]
     *
     * @param WP_Post $post The post.
     *
     * @return DataSetInterface The dataset model.
     */
    protected function createModel(WP_Post $post)
    {
        return new WpPostDataSet($post);
    }

    /**
     * Retrieves the data to use for creating a new post.
     *
     * @since [*next-version*]
     *
     * @param array $data The data being used to create the post.
     *
     * @return array The actual data to use with {@link wp_insert_post}.
     */
    protected function getNewPostData($data)
    {
        return [
            'post_type' => $this->postType,
        ];
    }

    /**
     * Retrieves the data to use for updating a post.
     *
     * @since [*next-version*]
     *
     * @param int|string $key  The post key (ID or slug).
     * @param array      $data The data being used to update the post.
     *
     * @return array The actual data to update the post with.
     */
    protected function getUpdatePostData($key, $data)
    {
        return $data;
    }

    /**
     * Normalizes a variable into a post array,
     *
     * @since [*next-version*]
     *
     * @param array|stdClass|Traversable|WP_Post $post Post data array, object or iterable, or a WP_Post instance.
     *
     * @return array The post data array.
     */
    protected function toPostArray($post)
    {
        if ($post instanceof WP_Post) {
            return $post->to_array();
        }

        return $this->_normalizeArray($post);
    }

    /**
     * Recursively patches a subject with every entry in a given patch data array.
     *
     * @since [*next-version*]
     *
     * @param array|ArrayAccess          $subject The subject to patch.
     * @param array|stdClass|Traversable $patch   The data to patch the subject with.
     *
     * @return array|ArrayAccess The patched subject.
     */
    protected function recursivePatch($subject, $patch)
    {
        foreach ($patch as $key => $value) {
            $subject[$key] = $value;
        }

        return $subject;
    }
}
