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
 * @since 4.13
 */
class WpPostArrayMetaDataSet implements DataSetInterface
{
    /* @since 4.13 */
    use NormalizeWpPostCapableTrait;

    /* @since 4.13 */
    use IteratorDelegateTrait;

    /**
     * The WordPress post.
     *
     * @since 4.13
     *
     * @var int|string|WP_Post
     */
    protected $post;

    /**
     * The key of the meta data.
     *
     * @since 4.13
     *
     * @var string
     */
    protected $metaKey;

    /**
     * Constructor.
     *
     * @since 4.13
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
     * @since 4.13
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
     * @since 4.13
     */
    public function offsetExists($key)
    {
        $data = $this->getMetaData();

        return isset($data[$key]);
    }

    /**
     * {@inheritdoc}
     *
     * @since 4.13
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
     * @since 4.13
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
     * @since 4.13
     */
    protected function getIterator()
    {
        return new ArrayIterator($this->getMetaData());
    }

    /**
     * Retrieves the entire meta data array for the post's meta key.
     *
     * @since 4.13
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
     * @since 4.13
     *
     * @param array $data The meta data array.
     */
    protected function setMetaData($data)
    {
        update_post_meta($this->post->ID, $this->metaKey, (array) $data);
    }
}
