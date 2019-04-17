<?php

namespace RebelCode\Wpra\Core\Templates\Feeds\Types;

/**
 * An implementation for the list template type.
 *
 * @since [*next-version*]
 */
class ListTemplateType extends AbstractWpraFeedTemplateType
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
    protected function enqueueAssets()
    {
        $general_settings = get_option('wprss_settings_general');

        // Enqueue scripts
        wp_enqueue_script('jquery.colorbox-min', WPRSS_JS . 'jquery.colorbox-min.js', ['jquery']);
        wp_enqueue_script('wprss_custom', WPRSS_JS . 'custom.js', ['jquery', 'jquery.colorbox-min']);

        wp_enqueue_script('wpra-manifest', WPRSS_APP_JS . 'wpra-manifest.min.js', ['jquery']);
        wp_enqueue_script('wpra-pagination', WPRSS_APP_JS . 'pagination.min.js', ['wpra-manifest']);

        wp_localize_script('wpra-pagination', 'WpraPagination', [
            'baseUri' => rest_url('/wpra/v1/templates/%s/render/'),
        ]);

        if (empty($general_settings['styles_disable'])) {
            wp_enqueue_style('colorbox', WPRSS_CSS . 'colorbox.css', [], '1.4.33');
            wp_enqueue_style('wpra-list-template-styles', WPRSS_CSS . 'templates/list/styles.css', [], WPRSS_VERSION);
            wp_enqueue_style('wpra-pagination', WPRSS_APP_CSS . 'pagination.min.css');
        }
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
            'pagination_enabled' => [
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
            'custom_css_classname' => [
                'filter' => FILTER_DEFAULT,
                'default' => '',
            ],
            'bullets_enabled' => [
                'filter' => FILTER_VALIDATE_BOOLEAN,
                'default' => true,
            ],
            'bullet_type' => [
                'filter' => 'enum',
                'options' => ['default', 'numbers'],
                'default' => 'default',
            ],
        ];
    }
}
