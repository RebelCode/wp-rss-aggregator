<?php

namespace RebelCode\Wpra\Core\Modules;

use Psr\Container\ContainerInterface;

/**
 * The module that adds Lorem's embeds in WP RSS Aggregator.
 *
 * @since [*next-version*]
 */
class LoremModule implements ModuleInterface
{
    /**
     * @inheritdoc
     *
     * @since [*next-version*]
     */
    public function run(ContainerInterface $c)
    {
        // Registers the script on the admin-side
        add_action('admin_init', $c->get('wpra/lorem/script/register_fn'));

        // Enqueues the script on the admin-side
        add_action('wprss_admin_exclusive_scripts_styles', $c->get('wpra/lorem/script/enqueue_fn'));

        // Adds the Lorem embed on the "Help & Support" page, after the page title.
        add_action('wpra/help_page/after_title', function () use ($c) {
            echo $c->get('wpra/lorem/help_page/html');
        });
    }

    /**
     * @inheritdoc
     *
     * @since [*next-version*]
     */
    public function getFactories()
    {
        return [
            /*
             * The key (or "handle") for the Lorem embed script.
             *
             * @since [*next-version*]
             */
            'wpra/lorem/script/key' => function (ContainerInterface $c) {
                return 'lorem-embed-script';
            },
            /*
             * The URL of the Lorem embed script.
             *
             * @since [*next-version*]
             */
            'wpra/lorem/script/url' => function (ContainerInterface $c) {
                return 'https://embed.asklorem.com/load.js';
            },
            /*
             * The function that registers the Lorem embed script.
             *
             * @since [*next-version*]
             */
            'wpra/lorem/script/register_fn' => function (ContainerInterface $c) {
                $scriptKey = $c->get('wpra/lorem/script/key');
                $scriptUrl = $c->get('wpra/lorem/script/url');

                return function () use ($scriptKey, $scriptUrl) {
                    wp_register_script($scriptKey, $scriptUrl, [], null, true);
                };
            },
            /*
             * The function that enqueues the Lorem embed script.
             *
             * @since [*next-version*]
             */
            'wpra/lorem/script/enqueue_fn' => function (ContainerInterface $c) {
                $scriptKey = $c->get('wpra/lorem/script/key');

                return function () use ($scriptKey) {
                    return wp_enqueue_script($scriptKey);
                };
            },
            /*
             * The Lorem item to show on the "More features" page.
             *
             * @since [*next-version*]
             */
            'wpra/lorem/more_features_page/item' => function (ContainerInterface $c) {
                return [
                    'type' => 'lorem',
                ];
            },
            /**
             * The HTML to add on the "Help & Support" page.
             *
             * @since [*next-version*]
             */
            'wpra/lorem/help_page/html' => function (ContainerInterface $c) {
                return '<div data-lorem-embed-id="rss-help-and-support"></div>';
            },
        ];
    }

    /**
     * @inheritdoc
     *
     * @since [*next-version*]
     */
    public function getExtensions()
    {
        return [
            /*
             * Extends the items on the "More Features" page to add the Lorem item.
             *
             * @since [*next-version*]
             */
            'wpra/more_features_page/items' => function (ContainerInterface $c, $items) {
                $items[] = $c->get('wpra/lorem/more_features_page/item');

                return $items;
            },
        ];
    }
}
