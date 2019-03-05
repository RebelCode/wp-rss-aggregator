<?php

namespace RebelCode\Wpra\Core\Data;

use ArrayIterator;

/**
 * An implementation of a data set that acts as a wrapper for a post's meta data.
 *
 * @since [*next-version*]
 */
class PostMetaDataSet extends AbstractInheritingDataSet
{
    /**
     * The ID of the post.
     *
     * @since [*next-version*]
     *
     * @var int|string
     */
    protected $postId;

    /**
     * Optional prefix for meta keys.
     *
     * @since [*next-version*]
     *
     * @var string
     */
    protected $metaPrefix;

    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param int|string            $postId     The ID of the post.
     * @param string                $metaPrefix Optional prefix for meta keys.
     * @param array                 $aliases    A mapping of input keys to real meta keys.
     * @param DataSetInterface|null $parent     Optional parent data set to inherit from.
     */
    public function __construct($postId, $metaPrefix, $aliases = [], DataSetInterface $parent = null)
    {
        parent::__construct($aliases, $parent);

        $this->postId = $postId;
        $this->metaPrefix = $metaPrefix;
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function getIterator()
    {
        return new ArrayIterator(get_post_meta($this->postId));
    }

    /**
     * {@inheritdoc}
     *
     * Additionally adds the meta prefix to keys.
     *
     * @since [*next-version*]
     */
    protected function aliasKey($key)
    {
        return $this->metaPrefix . parent::aliasKey($key);
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function get($key)
    {
        return get_post_meta($this->postId, $key, true);
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function has($key)
    {
        $arr = get_post_meta($this->postId, $key);

        return count($arr) > 0;
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function set($key, $value)
    {
        update_post_meta($this->postId, $key, $value);
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function delete($key)
    {
        delete_post_meta($this->postId, $key);
    }
}
