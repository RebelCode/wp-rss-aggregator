<?php

namespace RebelCode\Wpra\Core\Templates;

use RebelCode\Wpra\Core\Data\AliasingDataSet;
use RebelCode\Wpra\Core\Data\ArrayDataSet;
use RebelCode\Wpra\Core\Data\MergedDataSet;
use RebelCode\Wpra\Core\Data\WpArrayOptionDataSet;
use RebelCode\Wpra\Core\Data\WpCptDataSet;
use WP_Post;

/**
 * A specialized feed template model implementation that uses the old WP RSS Aggregator display settings as the
 * template options.
 *
 * @since [*next-version*]
 */
class BuiltInFeedTemplate extends WpCptDataSet
{
    /**
     * The name of the option from which to retrieve the template settings.
     *
     * @since [*next-version*]
     */
    const OPTION_NAME = 'wprss_settings_general';

    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param int|string|WP_Post $postOrId The post instance or ID.
     */
    public function __construct($postOrId)
    {
        parent::__construct($postOrId, '', $this->createAliases());
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function createInnerDataSet(WP_Post $post, $metaPrefix = '', $aliases = [])
    {
        $primary = new AliasingDataSet(
            new WpArrayOptionDataSet(static::OPTION_NAME),
            $aliases
        );
        $secondary = new ArrayDataSet([
            'id' => $post->ID,
            'name' => $post->post_title,
            'slug' => $post->post_name,
            'template_type' => '__built_in',
            'pagination_enabled' => false,
            'author_prefix' => __('By', 'wprss'),
        ]);
        $inner = new MergedDataSet($primary, $secondary);

        return $inner;
    }

    /**
     * Creates the aliases for this instance's data set.
     *
     * @since [*next-version*]
     *
     * @return array
     */
    protected function createAliases()
    {
        return [
            'items_max_num' => 'feed_limit',
            'title_max_length' => 'title_limit',
            'title_is_link' => 'title_link',
            'pagination_type' => 'pagination',
            'source_enabled' => 'source_enable',
            'source_prefix' => 'text_preceding_source',
            'source_is_link' => 'source_link',
            'author_enabled' => 'authors_enable',
            'date_enabled' => 'date_enable',
            'date_prefix' => 'text_preceding_date',
            'date_format' => 'date_format',
            'date_use_time_ago' => 'time_ago_format_enable',
            'links_open_behavior' => 'open_dd',
            'links_rel_nofollow' => 'follow_dd',
            'links_video_embed_page' => 'video_link',
        ];
    }
}
