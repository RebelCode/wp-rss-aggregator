<?php

namespace RebelCode\Wpra\Core\Data;

use RebelCode\Wpra\Core\Util\NormalizeWpPostCapableTrait;
use WP_Post;

/**
 * An implementation of a data set specifically tailored for WordPress posts that rely heavily on application
 * specific meta data.
 *
 * @since [*next-version*]
 */
class WpCptDataSet extends AbstractDelegateDataSet
{
    /* @since [*next-version*] */
    use NormalizeWpPostCapableTrait;

    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param int|string|WP_Post $postOrId   The post instance or ID.
     * @param string             $metaPrefix Optional meta data prefix.
     * @param string[]           $aliases    Optional list of aliases mapping to real post or meta keys.
     */
    public function __construct($postOrId, $metaPrefix = '', $aliases = [])
    {
        $post = $this->normalizeWpPost($postOrId);

        parent::__construct($this->createInnerDataSet($post, $metaPrefix, $aliases));
    }

    /**
     * Creates the inner data set.
     *
     * @since [*next-version*]
     *
     * @param WP_Post  $post       The post instance.
     * @param string   $metaPrefix Optional meta data prefix.
     * @param string[] $aliases    Optional list of aliases mapping to real post or meta keys.
     *
     * @return DataSetInterface The created data set instance.
     */
    protected function createInnerDataSet(WP_Post $post, $metaPrefix = '', $aliases = [])
    {
        $postDataSet = new PrefixingDataSet(
            new WpPostDataSet($post),
            'post_'
        );
        $metaDataSet = new PrefixingDataSet(
            new WpMetaDataSet($post),
            $metaPrefix
        );
        $fullAliases = array_merge(['id' => 'ID'], $aliases);
        $fullDataSet = new AliasingDataSet(new MergedDataSet($postDataSet, $metaDataSet), $fullAliases);

        return $fullDataSet;
    }
}
