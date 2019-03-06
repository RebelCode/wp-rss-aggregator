<?php

namespace RebelCode\Wpra\Core\Templates;

use RebelCode\Wpra\Core\Data\ArrayDataSet;
use RebelCode\Wpra\Core\Data\WpOptionsDataSet;
use WP_Post;

/**
 * A specialized feed template model implementation that uses the old WP RSS Aggregator display settings as the
 * template options.
 *
 * @since [*next-version*]
 */
class BuiltInFeedTemplate extends WpOptionsDataSet
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
     * @param int|string|WP_Post $post The post ID or {@link WP_Post} instance for the feed template.
     */
    public function __construct($post)
    {
        parent::__construct(static::OPTION_NAME, $this->createAliases(), $this->createParent($post));
    }

    /**
     * Creates the aliases for this instance.
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

    /**
     * Creates the parent data set for this instance.
     *
     * @since [*next-version*]
     *
     * @param int|string|WP_Post $post The post ID or {@link WP_Post} instance for the feed template.
     *
     * @return ArrayDataSet
     */
    protected function createParent($post)
    {
        $post = ($post instanceof WP_Post) ? $post : get_post($post);
        $parent = new ArrayDataSet([
            'id' => $post->ID,
            'name' => $post->post_title,
            'slug' => $post->post_name,
            'template_type' => '__built_in',
            'pagination_enabled' => false,
            'author_prefix' => __('By', 'wprss'),
        ]);

        return $parent;
    }
}
