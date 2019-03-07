<?php

namespace RebelCode\Wpra\Core\Data;

use ArrayIterator;
use CallbackFilterIterator;
use OutOfRangeException;
use WP_Post;

/**
 * An implementation of a data set that acts as a wrapper for a WordPress post's meta data.
 *
 * @since [*next-version*]
 */
class WpMetaDataSet extends AbstractDataSet
{
    /**
     * The WordPress post instance.
     *
     * @since [*next-version*]
     *
     * @var WP_Post
     */
    protected $post;

    /**
     * True to include hidden meta data (meta key starts with an underscore).
     *
     * @since [*next-version*]
     *
     * @var bool
     */
    protected $hiddenMeta;

    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param int|string|WP_Post $post       The WordPress post instance or post ID.
     * @param bool               $hiddenMeta True to include hidden meta data (meta key starts with an underscore).
     */
    public function __construct($post, $hiddenMeta = false)
    {
        $this->post = ($post instanceof WP_Post) ? $post : get_post($post);

        if (!($this->post instanceof WP_Post)) {
            throw new OutOfRangeException(
                sprintf(__('Post with ID %s does not exist', WPRSS_TEXT_DOMAIN), $post)
            );
        }

        $this->hiddenMeta = $hiddenMeta;
    }

    /**
     * Checks if a meta key is hidden or not.
     *
     * @since [*next-version*]
     *
     * @param string $key The meta key to check.
     *
     * @return bool True if the key is hidden, false otherwise.
     */
    protected function isHidden($key)
    {
        return !$this->hiddenMeta && strpos($key, '_') === 0;
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function get($key)
    {
        return get_post_meta($this->post->ID, $key, true);
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function has($key)
    {
        return metadata_exists('post', $this->post->ID, $key) && !$this->isHidden($key);
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function set($key, $value)
    {
        return update_post_meta($this->post->ID, $key, $value);
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function delete($key)
    {
        return delete_post_meta($this->post->ID, $key);
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function getIterator()
    {
        $meta = get_post_meta($this->post->ID);
        $array = array_map(function ($v) {
            return $v[0];
        }, $meta);

        $iterator = new ArrayIterator($array);

        return new CallbackFilterIterator($iterator, function ($current, $key) {
            return !$this->isHidden($key);
        });
    }
}
