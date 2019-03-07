<?php

namespace Aventura\Wprss\Core\Template;

use Aventura\Wprss\Core\Template\Api\TemplateQueryFactoryInterface;
use Aventura\Wprss\Core\Util\IdCommaListSanitizeTrait;
use Aventura\Wprss\Core\Util\ParseArgsCapableTrait;
use WP_Query;

/**
 * A factory that creates the {@link WP_Query} instance for feed items used in templates.
 *
 * @since [*next-version*]
 */
class TemplateQueryFactory implements TemplateQueryFactoryInterface
{
    /* @since [*next-version*] */
    use ParseArgsCapableTrait;

    /* @since [*next-version*] */
    use IdCommaListSanitizeTrait;

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     *
     * @return WP_Query
     */
    public function make(array $args = [])
    {
        $fullArgs = $this->getFeedItemsQueryArgs($args);

        return new WP_Query($fullArgs);
    }

    /**
     * Retrieves the {@link WP_Query} args.
     *
     * @since [*next-version*]
     *
     * @param array $args Configuration arguments for the query.
     *
     * @return array The {@link WP_Query} args array.
     */
    public function getFeedItemsQueryArgs($args)
    {
        $args = $this->_parseArgs($args, $this->_getArgsSchema());

        $queryArgs = [
            'post_type' => 'wprss_feed_item',
            'orderby' => 'date',
            'order' => 'DESC',
            'suppress_filters' => true,
            'posts_per_page' => $args['limit'],
            'paged' => $args['page'],
        ];

        // If either the source or exclude arguments are set (but not both), prepare a meta query
        if (!empty($args['source']) xor !empty($args['exclude'])) {
            // Check which one was true; "source" or "exclude"
            // Set negation to true if "exclude" is set, false if "source" is set
            $negation = count($args['exclude']) > count($args['source']);
            // Get the value and operator based on negation
            $feedSourceIds = ($negation) ? $args['exclude'] : $args['source'];
            $operator = ($negation) ? 'NOT IN' : 'IN';

            if (!empty($feedSourceIds)) {
                $queryArgs['meta_query'] = [
                    [
                        'key' => 'wprss_feed_id',
                        'value' => $feedSourceIds,
                        'type' => 'numeric',
                        'compare' => $operator,
                    ],
                ];
            }
        }

        return apply_filters('wprss_display_feed_items_query', $queryArgs, $args);
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function _getArgsSchema()
    {
        return [
            'limit' => [
                'default' => wprss_get_general_setting('feed_limit'),
                'transform' => function ($value, $args, $schema) {
                    return filter_var($value, FILTER_VALIDATE_INT, [
                        'options' => [
                            'min_range' => 1,
                            'default' => $schema['default'],
                        ],
                    ]);
                },
            ],
            'pagination' => [
                'key' => 'pagination',
                'default' => true,
                'transform' => function ($value) {
                    return filter_var($value, FILTER_VALIDATE_BOOLEAN);
                },
            ],
            'page' => [
                'key' => 'page',
                'default' => $this->_getPagedVar(),
                'transform' => function ($value, $args, $schema) {
                    return filter_var($value, FILTER_VALIDATE_INT, [
                        'options' => [
                            'min_range' => 1,
                            'default' => $schema['default'],
                        ],
                    ]);
                },
            ],
            'source' => [
                'default' => [],
                'transform' => function ($value, $args) {
                    if (array_key_exists('exclude', $args)) {
                        return [];
                    }

                    return $this->_sanitizeIdCommaList($value);
                },
            ],
            'exclude' => [
                'default' => [],
                'transform' => function ($value, $args) {
                    if (array_key_exists('source', $args)) {
                        return [];
                    }

                    return $this->_sanitizeIdCommaList($value);
                },
            ],
        ];
    }

    /**
     * Retrieves the paged var from WordPress.
     *
     * @since [*next-version*]
     *
     * @return int|mixed
     */
    protected function _getPagedVar()
    {
        global $paged;

        if ($pagedVar = get_query_var('paged')) {
            return $paged = $pagedVar;
        }

        if ($pageVar = get_query_var('page')) {
            return $paged = $pageVar;
        }

        return $paged = 1;
    }
}
