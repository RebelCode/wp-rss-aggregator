<?php

namespace Aventura\Wprss\Core\Template;

use Aventura\Wprss\Core\Template\Api\FeedItemContextFactoryInterface;
use RuntimeException;
use WP_Post;

/**
 * The default feed item context factory for WP RSS Aggregator.
 *
 * @since [*next-version*]
 */
class FeedItemContextFactory implements FeedItemContextFactoryInterface
{
    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function make(WP_Post $item, array $config = [])
    {
        $sourceId = get_post_meta($item->ID, 'wprss_feed_id', true);
        $source = get_post($sourceId);

        if ($source === null) {
            throw new RuntimeException('Feed item source is null');
        }

        return $this->_processSchema($this->_getSchema(), $item, $source, $config);
    }

    /**
     * Processes a schema for an item or its nested data to generate the context.
     *
     * @since [*next-version*]
     *
     * @param array   $schema The schema to process.
     * @param WP_Post $item   The feed item.
     * @param WP_Post $source The feed item's feed source.
     * @param array   $config The config.
     *
     * @return array The resulting feed item context.
     */
    protected function _processSchema(array $schema, WP_Post $item, WP_Post $source, array $config)
    {
        $context = [];

        foreach ($schema as $key => $value) {
            $context[$key] = is_array($value)
                ? $this->_processSchema($value, $item, $source, $config)
                : call_user_func_array($value, [$item, $source, $config]);
        }

        return $context;
    }

    /**
     * Retrieves the schema for a feed item's context.
     *
     * @since [*next-version*]
     *
     * @return array An associative array, where each key corresponds to the item's context key and each value
     *               corresponds to a function (receiving the item, source and config) that resolves to the item's
     *               value for that key. Alternatively, each value may be a sub-schema for nested values.
     */
    protected function _getSchema()
    {
        return [
            /*
             * The item's URL.
             *
             * @since [*next-version*]
             */
            'url' => function (WP_Post $item, $source) {
                $useEnclosure = get_post_meta($source->ID, 'wprss_enclosure', true);
                $permalinkUrl = get_post_meta($item->ID, 'wprss_item_permalink', true);
                $enclosureUrl = get_post_meta($item->ID, 'wprss_item_enclosure', true);
                $url = ($useEnclosure === 'true' && $enclosureUrl !== '')
                    ? $enclosureUrl
                    : $permalinkUrl;

                return apply_filters('wprss_item_url', $url, $item, $source);
            },
            'title' => [
                'show' => function () {
                    return true;
                },
                /*
                 * The item's title text.
                 *
                 * @since [*next-version*]
                 */
                'text' => function (WP_Post $item, WP_Post $source) {
                    return apply_filters('wprss_item_title', $item->post_title, $item, $source);
                },
                /*
                 * Whether or not the item's title is a link to the original article.
                 *
                 * @since [*next-version*]
                 */
                'is_link' => function (WP_Post $item, WP_Post $source, array $config) {
                    return $this->_configGetPath($config, 'options.title.is_link', true);
                },
                /*
                 * Optional content to show after the item's title.
                 *
                 * @since [*next-version*]
                 */
                'after' => function (WP_Post $item, WP_Post $source) {
                    ob_start();
                    do_action('wprss_after_feed_item_title', $item, $source);

                    return ob_get_clean();
                },
            ],
            'source' => [
                /*
                 * Whether or not to show the item's source.
                 *
                 * @since [*next-version*]
                 */
                'show' => function (WP_Post $item, WP_Post $source, array $config) {
                    return $this->_configGetPath($config, 'options.source.show', true);
                },
                /*
                 * The ID of the item's feed source.
                 *
                 * @since [*next-version*]
                 */
                'id' => function (WP_Post $item, WP_Post $source) {
                    return $source->ID;
                },
                /*
                 * The name of the item's feed source.
                 *
                 * @since [*next-version*]
                 */
                'title' => function (WP_Post $item, WP_Post $source) {
                    return apply_filters('wprss_item_source_name', $source->post_title, $item, $source);
                },
                /*
                 * The URL of the item's feed source's site (not RSS feed).
                 *
                 * @since [*next-version*]
                 */
                'url' => function (WP_Post $item, WP_Post $source) {
                    $sourceUrl = get_post_meta($source->ID, 'wprss_site_url', true);

                    if ($sourceUrl === '') {
                        // Fallback for feeds created with older versions of the plugin
                        $sourceUrl = get_post_meta($source->ID, 'wprss_url', true);
                    }

                    return apply_filters('wprss_item_source_url', $sourceUrl, $item);
                },
                /*
                 * Whether or not the source is a link to the original item's site.
                 *
                 * @since [*next-version*]
                 */
                'is_link' => function (WP_Post $item, WP_Post $source, array $config) {
                    $isSourceLink = get_post_meta($source->ID, 'wprss_source_link', true);
                    $isSourceLink = ($isSourceLink === '' || intval($isSourceLink) < 0)
                        ? $this->_configGetPath($config, 'options.source.is_link', true)
                        : $isSourceLink;

                    return intval(trim($isSourceLink));
                },
                /*
                 * Optional prefix text to show before the source name.
                 *
                 * @since [*next-version*]
                 */
                'prefix' => function (WP_Post $item, WP_Post $source, array $config) {
                    return $this->_configGetPath($config, 'options.source.prefix', '');
                },
            ],
            'date' => [
                /*
                 * Whether or not to show the item's date.
                 *
                 * @since [*next-version*]
                 */
                'show' => function (WP_Post $item, WP_Post $source, array $config) {
                    return $this->_configGetPath($config, 'options.date.show', true);
                },
                /*
                 * The text to show for the item's date.
                 *
                 * @since [*next-version*]
                 */
                'text' => function (WP_Post $item, WP_Post $source) {
                    $timestamp = get_the_time('U', $item->ID);
                    $date = wprss_date_i18n($timestamp);

                    return apply_filters('wprss_item_date', $date, $item);
                },
                /*
                 * Optional prefix text to show before the date.
                 *
                 * @since [*next-version*]
                 */
                'prefix' => function (WP_Post $item, WP_Post $source, array $config) {
                    return $this->_configGetPath($config, 'options.date.prefix', true);
                },
            ],
            'author' => [
                /*
                 * Whether or not to show the item's author.
                 *
                 * @since [*next-version*]
                 */
                'show' => function (WP_Post $item, WP_Post $source, array $config) {
                    return $this->_configGetPath($config, 'options.author.show', true);
                },
                /*
                 * The name of the item's author.
                 *
                 * @since [*next-version*]
                 */
                'name' => function (WP_Post $item) {
                    $author = get_post_meta($item->ID, 'wprss_item_author', true);

                    return apply_filters('wprss_item_author', $author, $item);
                },
                /*
                 * Optional prefix text to show before the author's name.
                 *
                 * @since [*next-version*]
                 */
                'prefix' => function (WP_Post $item) {
                    return apply_filters('wprss_author_prefix_text', __('By', WPRSS_TEXT_DOMAIN), $item);
                },
            ],
            'time_ago' => [
                /*
                 * Whether or not to show the item's "time ago" date.
                 *
                 * @since [*next-version*]
                 */
                'show' => function (WP_Post $item, WP_Post $source, array $config) {
                    return $this->_configGetPath($config, 'options.time_ago.show', false);
                },
                /*
                 * The "time ago" text for this item's date.
                 *
                 * @since [*next-version*]
                 */
                'text' => function (WP_Post $item) {
                    $timestamp = get_the_time('U', $item->ID);
                    $timeAgoText = human_time_diff($timestamp, time());

                    return apply_filters('wprss_item_time_ago', $timeAgoText);
                },

                'format' => function (WP_Post $item, WP_Post $source, array $config) {
                    return $this->_configGetPath($config, 'options.time_ago.format', '%s');
                },
            ],
        ];
    }

    /**
     * Utility method for retrieving data from config using a path.
     *
     * @since [*next-version*]
     *
     * @param array  $config  The config.
     * @param string $path    The path string, with period "." separators.
     * @param string $default The default value to return if the path is not in the config.
     *
     * @return mixed The value in the config, or the $default value if the path was not found.
     */
    protected function _configGetPath(array $config, $path, $default = '')
    {
        $arrayPath = explode('.', $path);

        $node = $config;
        foreach ($arrayPath as $segment) {
            if (array_key_exists($segment, $node)) {
                $node = $node[$segment];
                continue;
            }

            return $default;
        }

        return $node;
    }
}
