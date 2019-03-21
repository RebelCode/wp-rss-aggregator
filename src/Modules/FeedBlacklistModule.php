<?php

namespace RebelCode\Wpra\Core\Modules;

use Psr\Container\ContainerInterface;
use RebelCode\Wpra\Core\Modules\Handlers\RegisterCptHandler;

/**
 * The feed blacklist module for WP RSS Aggregator.
 *
 * @since [*next-version*]
 */
class FeedBlacklistModule implements ModuleInterface
{
    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function getFactories()
    {
        return [
            /*
             * The name of the feed item blacklist CPT.
             *
             * @since [*next-version*]
             */
            'wpra/feeds/blacklist/cpt_name' => function () {
                return 'wprss_blacklist';
            },
            /*
             * The labels for the feed item blacklist CPT.
             *
             * @since [*next-version*]
             */
            'wpra/feeds/blacklist/cpt_labels' => function () {
                return [
                    'name' => __('Blacklisted items', 'wprss'),
                    'singular_name' => __('Blacklisted item', 'wprss'),
                    'add_new' => __('Blacklist an item', 'wprss'),
                    'all_items' => __('Blacklist', 'wprss'),
                    'add_new_item' => __('Blacklist an item', 'wprss'),
                    'edit_item' => __('Edit blacklisted item', 'wprss'),
                    'new_item' => __('Blacklist an item', 'wprss'),
                    'view_item' => __('View blacklisted items', 'wprss'),
                    'search_items' => __('Search blacklisted items', 'wprss'),
                    'not_found' => __('No blacklisted items', 'wprss'),
                    'not_found_in_trash' => __('No blacklisted items found in the trash', 'wprss'),
                ];
            },
            /*
             * The capability for the feed item blacklist CPT.
             *
             * @since [*next-version*]
             */
            'wpra/feeds/blacklist/cpt_capability' => function () {
                return 'feed_blacklist';
            },
            /*
             * The full arguments for the feed item blacklist CPT.
             *
             * @since [*next-version*]
             */
            'wpra/feeds/blacklist/cpt_args' => function (ContainerInterface $c) {
                return [
                    'public' => false,
                    'exclude_from_search' => true,
                    'show_ui' => true,
                    'show_in_menu' => 'edit.php?post_type=wprss_feed',
                    'capability_type' => $c->get('wpra/feeds/blacklist/cpt_capability'),
                    'map_meta_cap' => true,
                    'supports' => ['title'],
                    'labels' => $c->get('wpra/feeds/blacklist/cpt_labels'),
                ];
            },
            /*
             * The handler that registers the feed item blacklist CPT.
             *
             * @since [*next-version*]
             */
            'wpra/feeds/blacklist/register_cpt_handler' => function (ContainerInterface $c) {
                return new RegisterCptHandler(
                    $c->get('wpra/feeds/blacklist/cpt_name'),
                    $c->get('wpra/feeds/blacklist/cpt_args')
                );
            },
        ];
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function getExtensions()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function run(ContainerInterface $c)
    {
        add_action('init', $c->get('wpra/feeds/blacklist/register_cpt_handler'), 11);
    }
}
