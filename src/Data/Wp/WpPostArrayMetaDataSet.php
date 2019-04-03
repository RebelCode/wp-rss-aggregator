<?php

namespace RebelCode\Wpra\Core\Data\Wp;

use ArrayIterator;
use OutOfRangeException;
use RebelCode\Wpra\Core\Data\DataSetInterface;
use RebelCode\Wpra\Core\Util\IteratorDelegateTrait;
use RebelCode\Wpra\Core\Util\NormalizeWpPostCapableTrait;
use WP_Post;

/**
 * A dataset implementation that can manipulate serialized array posts meta data.
 *
 * @since [*next-version*]
 */
class WpPostArrayMetaDataSet implements DataSetInterface
{
    /* @since [*next-version*] */
    use NormalizeWpPostCapableTrait;

    /* @since [*next-version*] */
    use IteratorDelegateTrait;

    /**
     * The WordPress post.
     *
     * @since [*next-version*]
     *
     * @var int|string|WP_Post
     */
    protected $post;

    /**
     * The key of the meta data.
     *
     * @since [*next-version*]
     *
     * @var string
     */
    protected $metaKey;

    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param int|string|WP_Post $postOrId The WordPress post instance or post ID.
     * @param string             $metaKey  The key of the meta data.
     */
    public function __construct($postOrId, $metaKey)
    {
        $this->post = $this->normalizeWpPost($postOrId);
        $this->metaKey = $metaKey;
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function offsetGet($key)
    {
        $data = $this->getMetaData();

        if (isset($data[$key])) {
            return $data[$key];
        }

        throw new OutOfRangeException(
            sprintf(
                __('Meta data "%s" for post %d does not have a "%s" key', 'wprss'),
                $this->metaKey,
                $this->post->ID, $key
            )
        );
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function offsetExists($key)
    {
        $data = $this->getMetaData();

        return isset($data[$key]);
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function offsetSet($key, $value)
    {
        $data = $this->getMetaData();
        $data[$key] = $value;

        $this->setMetaData($data);
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function offsetUnset($key)
    {
        $data = $this->getMetaData();
        unset($data[$key]);

        $this->setMetaData($data);
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function getIterator()
    {
        return new ArrayIterator($this->getMetaData());
    }

    /**
     * Retrieves the entire meta data array for the post's meta key.
     *
     * @since [*next-version*]
     *
     * @return array
     */
    protected function getMetaData()
    {
        $data = get_post_meta($this->post->ID, $this->metaKey, true);

        return empty($data) ? [] : (array) $data;
    }

    /**
     * Sets the entire meta data array for the posts's meta key.
     *
     * @since [*next-version*]
     *
     * @param array $data The meta data array.
     */
    protected function setMetaData($data)
    {
        update_post_meta($this->post->ID, $this->metaKey, (array) $data);
    }
}
