<?php

namespace RebelCode\Wpra\Core\Data\Wp;

use LogicException;
use OutOfRangeException;
use RebelCode\Wpra\Core\Data\ArrayDataSet;
use RebelCode\Wpra\Core\Util\NormalizeWpPostCapableTrait;
use WP_Post;

/**
 * A data set implementation that acts as a wrapper for standard WP_Post data.
 *
 * @since 4.13
 */
class WpPostDataDataSet extends ArrayDataSet
{
    /* @since 4.13 */
    use NormalizeWpPostCapableTrait;

    /**
     * Constructor.
     *
     * @since 4.13
     *
     * @param int|string|WP_Post $postOrId The WordPress post instance or post ID.
     */
    public function __construct($postOrId)
    {
        $post = $this->normalizeWpPost($postOrId);
        $array = $post->to_array();

        parent::__construct($array);
    }

    /**
     * {@inheritdoc}
     *
     * @since 4.13
     */
    protected function set($key, $value)
    {
        if (!$this->has($key)) {
            throw new OutOfRangeException(sprintf('Cannot set value to key "%s" for a WordPress post', $key));
        }

        parent::set($key, $value);

        wp_update_post($this->data);
    }

    /**
     * {@inheritdoc}
     *
     * @since 4.13
     */
    protected function delete($key)
    {
        throw new LogicException('Cannot delete data from a WordPress post');
    }
}
