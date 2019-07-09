<?php

namespace RebelCode\Wpra\Core\Data\Wp;

use OutOfRangeException;
use RebelCode\Wpra\Core\Data\MaskingDataSet;
use RebelCode\Wpra\Core\Data\PrefixingDataSet;
use RebelCode\Wpra\Core\Util\NormalizeWpPostCapableTrait;
use WP_Post;

/**
 * An implementation of a data set specifically tailored for WordPress posts of a custom post type that rely heavily on
 * application-specific meta data.
 *
 * @since 4.13
 */
class WpCptDataSet extends WpPostDataSet
{
    /* @since 4.13 */
    use NormalizeWpPostCapableTrait;

    /**
     * The meta prefix.
     *
     * @since 4.13
     *
     * @var string|null
     */
    protected $metaPrefix;

    /**
     * The post mask.
     *
     * @since 4.13
     *
     * @var string[]|null
     */
    protected $postMask;

    /**
     * Constructor.
     *
     * @since 4.13
     *
     * @param int|string|WP_Post $postOrId   The post instance or ID.
     * @param string|null        $metaPrefix Optional meta data prefix to strip from keys, or null for no stripping.
     * @param string[]|null      $postFields Optional list of post fields to use, or null to use all of them.
     *
     * @throws OutOfRangeException If the post does not exist.
     */
    public function __construct($postOrId, $metaPrefix = null, $postFields = null)
    {
        $this->metaPrefix = $metaPrefix;
        $this->postMask = $postFields;

        parent::__construct($postOrId);
    }

    /**
     * {@inheritdoc}
     *
     * @since 4.13
     */
    protected function createPostDataSet($postOrId)
    {
        $original = parent::createPostDataSet($postOrId);

        return (is_array($this->postMask) && count($this->postMask) > 0)
            ? new MaskingDataSet($original, array_flip($this->postMask), false)
            : $original;
    }

    /**
     * {@inheritdoc}
     *
     * @since 4.13
     */
    protected function createMetaDataSet($postOrId)
    {
        $original = parent::createMetaDataSet($postOrId);

        return (is_string($this->metaPrefix) && strlen($this->metaPrefix) > 0)
            ? new PrefixingDataSet($original, $this->metaPrefix)
            : $original;
    }
}
