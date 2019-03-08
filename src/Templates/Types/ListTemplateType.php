<?php

namespace RebelCode\Wpra\Core\Templates\Types;

/**
 * An implementation for the list template type.
 *
 * @since [*next-version*]
 */
class ListTemplateType extends AbstractTemplateType
{
    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function getKey()
    {
        return 'list';
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function getName()
    {
        return __('List', WPRSS_TEXT_DOMAIN);
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function getOptions()
    {
        return [
            'items_max_num' => [
                'filter' => FILTER_VALIDATE_INT,
                'options' => ['min_range' => 1],
                'default' => 15
            ],
            'title_max_length' => [
                'filter' => FILTER_VALIDATE_INT,
                'options' => ['min_range' => 0],
                'default' => 0,
            ],
            'title_is_link' => [
                'filter' => FILTER_VALIDATE_BOOLEAN,
                'flags' => [],
                'default' => true,
            ],
            'pagination' => [
                'key' => 'pagination_enabled',
                'filter' => FILTER_VALIDATE_BOOLEAN,
                'default' => true,
            ],
            'pagination_type' => [
                'filter' => 'enum',
                'options' => ['default', 'numbered'],
                'default' => 'default',
            ],
            'source_enabled' => [
                'filter' => FILTER_VALIDATE_BOOLEAN,
                'default' => true,
            ],
            'source_prefix' => [
                'filter' => FILTER_DEFAULT,
                'default' => __('Source:', 'wprss'),
            ],
            'source_is_link' => [
                'filter' => FILTER_VALIDATE_BOOLEAN,
                'default' => true,
            ],
            'author_enabled' => [
                'filter' => FILTER_VALIDATE_BOOLEAN,
                'default' => false,
            ],
            'author_prefix' => [
                'filter' => FILTER_DEFAULT,
                'default' => __('By', 'wprss'),
            ],
            'date_enabled' => [
                'filter' => FILTER_VALIDATE_BOOLEAN,
                'default' => true,
            ],
            'date_prefix' => [
                'filter' => FILTER_DEFAULT,
                'default' => __('Published on:', 'wprss'),
            ],
            'date_format' => [
                'filter' => FILTER_DEFAULT,
                'default' => 'Y-m-d',
            ],
            'date_use_time_ago' => [
                'filter' => FILTER_VALIDATE_BOOLEAN,
                'default' => false,
            ],
            'links_behavior' => [
                'key' => 'links_open_behavior',
                'filter' => 'enum',
                'options' => ['self', 'blank', 'lightbox'],
                'default' => 'blank',
            ],
            'links_nofollow' => [
                'key' => 'links_rel_nofollow',
                'filter' => FILTER_VALIDATE_BOOLEAN,
                'default' => false,
            ],
            'links_video_embed_page' => [
                'filter' => FILTER_VALIDATE_BOOLEAN,
                'default' => false,
            ],
        ];
    }
}
