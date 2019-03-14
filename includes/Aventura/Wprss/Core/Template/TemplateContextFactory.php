<?php

namespace Aventura\Wprss\Core\Template;

use Aventura\Wprss\Core\Template\Api\FeedItemContextFactoryInterface;
use Aventura\Wprss\Core\Template\Api\TemplateContextFactoryInterface;
use Aventura\Wprss\Core\Util\GetGeneralSettingsTrait;
use Aventura\Wprss\Core\Util\ParseArgsCapableTrait;
use WP_Query;

/**
 * The default template context factory for WP RSS Aggregator.
 *
 * @since [*next-version*]
 */
class TemplateContextFactory implements TemplateContextFactoryInterface
{
    /* @since [*next-version*] */
    use GetGeneralSettingsTrait;

    /* @since [*next-version*] */
    use ParseArgsCapableTrait;

    /**
     * The context factory to use for feed items.
     *
     * @since [*next-version*]
     *
     * @var FeedItemContextFactoryInterface
     */
    protected $itemCtxFactory;

    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param FeedItemContextFactoryInterface $itemCtxFactory The context factory to use for feed items.
     */
    public function __construct(FeedItemContextFactoryInterface $itemCtxFactory)
    {
        $this->itemCtxFactory = $itemCtxFactory;
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function make(WP_Query $query, array $args = [])
    {
        // Build the args into the render context
        $context = $this->_parseArgs($args, $this->_getArgsSchema());

        // Fetch the items from the query and build their render contexts
        $context['items'] = [];
        if ($query instanceof WP_Query) {
            while ($query->have_posts()) {
                $query->the_post();
                $context['items'][] = $this->itemCtxFactory->make($query->post, $context);
            }
        }

        return $context;
    }

    /**
     * Retrieves the schema for the render args.
     *
     * @since [*next-version*]
     *
     * @return array
     */
    protected function _getArgsSchema()
    {
        $generalSettings = $this->_getGeneralSettings();

        return [
            'html_classes' => [
                'key' => 'options/custom_css',
                'default' => $generalSettings['custom_css'],
                'transform' => function ($value) {
                    return is_array($value)
                        ? implode(' ', array_filter(array_map('trim', $value)))
                        : strval($value);
                },
            ],
            'custom_css' => [
                'key' => 'options/custom_css',
                'default' => $generalSettings['custom_css'],
                'transform' => function ($value) {
                    return strval($value);
                },
            ],

            'pagination' => [
                'key' => 'options/pagination/enabled',
                'default' => true,
                'transform' => function ($value) {
                    return filter_var($value, FILTER_VALIDATE_BOOLEAN);
                },
            ],
            'pagination_content' => [
                'key' => 'options/pagination/content',
                'default' => wprss_pagination_links(''),
                'transform' => function ($value) {
                    return strval($value);
                },
            ],
            'pagination_mode' => [
                'key' => 'options/pagination/mode',
                'default' => wprss_get_general_setting('pagination'),
                'transform' => function ($value) {
                    if (in_array($value, ['default', 'numbered'])) {
                        return $value;
                    }

                    return 'default';
                },
            ],
            'page' => [
                'key' => 'options/pagination/page',
                'default' => 1,
                'transform' => function ($value) {
                    return intval($value);
                },
            ],

            'links_before' => [
                'key' => 'options/before_list',
                'default' => '<ul class="rss-aggregator">',
                'transform' => function ($value) {
                    return strval($value);
                },
            ],

            'links_after' => [
                'key' => 'options/after_list',
                'default' => '</ul>',
                'transform' => function ($value) {
                    return strval($value);
                },
            ],

            'link_before' => [
                'key' => 'options/before_item',
                'default' => '<li class="feed-item">',
                'transform' => function ($value) {
                    return strval($value);
                },
            ],

            'link_after' => [
                'key' => 'options/after_item',
                'default' => '</li>',
                'transform' => function ($value) {
                    return strval($value);
                },
            ],

            'no_items_msg' => [
                'key' => 'options/messages/no_items',
                'default' => __('No feed items found.', WPRSS_TEXT_DOMAIN),
                'transform' => function ($value) {
                    return strval($value);
                },
            ],

            'show_source' => [
                'key' => 'options/source/show',
                'default' => $generalSettings['source_enable'] == 1,
                'transform' => function ($value) {
                    return filter_var($value, FILTER_VALIDATE_BOOLEAN);
                },
            ],

            'show_date' => [
                'key' => 'options/date/show',
                'default' => $generalSettings['date_enable'] == 1,
                'transform' => function ($value) {
                    return filter_var($value, FILTER_VALIDATE_BOOLEAN);
                },
            ],

            'show_time_ago' => [
                'key' => 'options/time_ago/show',
                'default' => $generalSettings['time_ago_format_enable'] == 1,
                'transform' => function ($value) {
                    return filter_var($value, FILTER_VALIDATE_BOOLEAN);
                },
            ],

            'time_ago_format' => [
                'key' => 'options/time_ago/format',
                'default' => '%s',
                'transform' => function ($value) {
                    return strval($value);
                },
            ],

            'show_author' => [
                'key' => 'options/author/show',
                'default' => $generalSettings['authors_enable'] == 1,
                'transform' => function ($value) {
                    return filter_var($value, FILTER_VALIDATE_BOOLEAN);
                },
            ],

            'link_title' => [
                'key' => 'options/title/is_link',
                'default' => $generalSettings['title_link'] == 1,
                'transform' => function ($value) {
                    return filter_var($value, FILTER_VALIDATE_BOOLEAN);
                },
            ],

            'link_source' => [
                'key' => 'options/source/is_link',
                'default' => $generalSettings['source_link'] == 1,
                'transform' => function ($value) {
                    return filter_var($value, FILTER_VALIDATE_BOOLEAN);
                },
            ],

            'source_prefix' => [
                'key' => 'options/source/prefix',
                'default' => $generalSettings['text_preceding_source'],
                'transform' => function ($value) {
                    return strval($value);
                },
            ],

            'date_prefix' => [
                'key' => 'options/date/prefix',
                'default' => $generalSettings['text_preceding_date'],
                'transform' => function ($value) {
                    return strval($value);
                },
            ],

            'author_prefix' => [
                'key' => 'options/author/prefix',
                'default' => apply_filters('wprss_author_prefix_text', _x('By', 'By (author)', WPRSS_TEXT_DOMAIN)),
                'transform' => function ($value) {
                    return filter_var($value, FILTER_VALIDATE_BOOLEAN);
                },
            ],
        ];
    }
}
