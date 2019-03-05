<?php

namespace RebelCode\Wpra\Core\Templates;

use RebelCode\Wpra\Core\Data\ArrayDataSet;
use RebelCode\Wpra\Core\Data\WpOptionsDataSet;

/**
 * A specialized implementation for the list view template, made to use the old general display settings.
 *
 * @since [*next-version*]
 */
class ListViewTemplate extends AbstractFeedTemplate
{
    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function getId()
    {
        return 'list-view';
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function getName()
    {
        return __('List Template', WPRSS_TEXT_DOMAIN);
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function getDbOptionKey()
    {
        return 'wprss_settings_general';
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function createOptions()
    {
        $aliases = [
            'items_max_num' => 'feed_limit',
            'title_max_length' => 'title_limit',
            'title_is_link' => 'title_link',
            'pagination_enabled' => 'pagination_enable',
            'pagination_type' => 'pagination',
            'source_enabled' => 'source_enable',
            'source_prefix' => 'text_preceding_source',
            'source_is_link' => 'source_link',
            'author_enabled' => 'authors_enable',
            'author_prefix' => 'text_preceding_author',
            'date_enabled' => 'date_enable',
            'date_prefix' => 'text_preceding_date',
            'date_format' => 'date_format',
            'date_use_time_ago' => 'time_ago_format_enable',
            'links_open_behavior' => 'open_dd',
            'links_rel_nofollow' => 'follow_dd',
            'links_video_embed_page' => 'video_link',
        ];

        return new WpOptionsDataSet($this->getDbOptionKey(), $aliases, $this->getDefaultOptions());
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function getDefaultOptions()
    {
        return new ArrayDataSet([
            'items_max_num' => '15',
            'title_max_length' => 0,
            'title_is_link' => true,
            'pagination_enabled' => true,
            'pagination_type' => 'default',
            'source_enabled' => true,
            'source_prefix' => 'Source:',
            'source_is_link' => true,
            'author_enabled' => false,
            'author_prefix' => 'By',
            'date_enabled' => true,
            'date_prefix' => 'Published on',
            'date_format' => 'Y-m-d',
            'date_use_time_ago' => false,
            'links_open_behavior' => 'blank',
            'links_rel_nofollow' => false,
            'links_video_embed_page' => false,
        ]);
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function getContextSchema()
    {
        return [
            'items_max_num' => [
                'filter' => FILTER_VALIDATE_INT,
                'options' => ['min_range' => 1],
            ],
            'title_max_length' => [
                'filter' => FILTER_VALIDATE_INT,
                'options' => ['min_range' => 0],
            ],
            'title_is_link' => [
                'filter' => FILTER_VALIDATE_BOOLEAN,
                'flags' => [],
            ],
            'pagination' => [
                'key' => 'pagination_enabled',
                'filter' => FILTER_VALIDATE_BOOLEAN,
            ],
            'pagination_type' => [
                'filter' => 'enum',
                'options' => ['default', 'numbered'],
            ],
            'source_enabled' => [
                'filter' => FILTER_VALIDATE_BOOLEAN,
            ],
            'source_prefix' => [
                'filter' => FILTER_DEFAULT,
            ],
            'source_is_link' => [
                'filter' => FILTER_VALIDATE_BOOLEAN,
            ],
            'author_enabled' => [
                'filter' => FILTER_VALIDATE_BOOLEAN,
            ],
            'author_prefix' => [
                'filter' => FILTER_DEFAULT,
            ],
            'date_enabled' => [
                'filter' => FILTER_VALIDATE_BOOLEAN,
            ],
            'date_prefix' => [
                'filter' => FILTER_DEFAULT,
            ],
            'date_format' => [
                'filter' => FILTER_DEFAULT,
            ],
            'date_use_time_ago' => [
                'filter' => FILTER_VALIDATE_BOOLEAN,
            ],
            'links_behavior' => [
                'key' => 'links_open_behavior',
                'filter' => 'enum',
                'options' => ['self', 'blank', 'lightbox'],
            ],
            'links_nofollow' => [
                'key' => 'links_rel_nofollow',
                'filter' => FILTER_VALIDATE_BOOLEAN,
            ],
            'links_video_embed_page' => [
                'filter' => FILTER_VALIDATE_BOOLEAN,
            ],
        ];
    }
}
