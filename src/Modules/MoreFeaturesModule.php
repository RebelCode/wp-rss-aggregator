<?php

namespace RebelCode\Wpra\Core\Modules;

use Psr\Container\ContainerInterface;
use RebelCode\Wpra\Core\Handlers\RegisterSubMenuPageHandler;

/**
 * The module that adds the "More Features" page and menu.
 *
 * @since [*next-version*]
 */
class MoreFeaturesModule implements ModuleInterface
{
    /**
     * @inheritdoc
     *
     * @since [*next-version*]
     */
    public function run(ContainerInterface $c)
    {
        // Registers the "More Features" menu and page
        add_action(
            'admin_menu',
            $c->get('wpra/more_features_page/register'),
            $c->get('wpra/more_features_page/menu_pos')
        );
    }

    /**
     * @inheritdoc
     *
     * @since [*next-version*]
     */
    public function getFactories()
    {
        return array(
            /*
             * The function that registers the "More Features" page and menu.
             *
             * @since [*next-version*]
             */
            'wpra/more_features_page/register' => function (ContainerInterface $c) {
                return new RegisterSubMenuPageHandler([
                    'parent' => $c->get('wpra/more_features_page/parent'),
                    'slug' => $c->get('wpra/more_features_page/slug'),
                    'page_title' => $c->get('wpra/more_features_page/title'),
                    'menu_label' => $c->get('wpra/more_features_page/menu_label'),
                    'capability' => $c->get('wpra/more_features_page/capability'),
                    'callback' => $c->get('wpra/more_features_page/render_fn'),
                ]);
            },
            /*
             * The slug of the "More Features"'s parent page.
             *
             * @since [*next-version*]
             */
            'wpra/more_features_page/parent' => function () {
                return 'edit.php?post_type=wprss_feed';
            },
            /*
             * The slug of the "More Features" page.
             *
             * @since [*next-version*]
             */
            'wpra/more_features_page/slug' => function () {
                return 'wprss_addons';
            },
            /*
             * The title for the "More Features" page.
             *
             * @since [*next-version*]
             */
            'wpra/more_features_page/title' => function () {
                return __('More Features', 'wprss');
            },
            /*
             * The required admin capability for viewing the "More Features" page.
             *
             * @since [*next-version*]
             */
            'wpra/more_features_page/capability' => function () {
                return apply_filters('wprss_capability', 'manage_feed_settings');
            },
            /*
             * The label for the "More Features" menu.
             *
             * @since [*next-version*]
             */
            'wpra/more_features_page/menu_label' => function (ContainerInterface $c) {
                return $c->get('wpra/more_features_page/title') . $c->get('wpra/more_features_page/menu_icon');
            },
            /*
             * The icon for the "More Features" menu.
             *
             * @since [*next-version*]
             */
            'wpra/more_features_page/menu_icon' => function () {
                return '<span class="dashicons dashicons-star-filled wprss-more-features-glyph"></span>';
            },
            /*
             * The position of the "More Features" menu.
             *
             * @since [*next-version*]
             */
            'wpra/more_features_page/menu_pos' => function () {
                return 50;
            },
            /*
             * The function to use for rendering the "More Features" page.
             *
             * @since [*next-version*]
             */
            'wpra/more_features_page/render_fn' => function (ContainerInterface $c) {
                if (!$c->has('wpra/twig/collection')) {
                    return function () {
                    };
                }

                return function () use ($c) {
                    $collection = $c->get('wpra/twig/collection');
                    $template = $collection[$c->get('wpra/more_features_page/template')];
                    $items = $c->get('wpra/more_features_page/items');

                    echo $template->render(['items' => $items]);
                };
            },
            /*
             * The path to the template to use when rendering the "More Features" page.
             *
             * @since [*next-version*]
             */
            'wpra/more_features_page/template' => function () {
                return 'admin/more-features/main.twig';
            },
            /*
             * The items to show on the "More Features" page.
             *
             * @since [*next-version*]
             */
            'wpra/more_features_page/items' => function (ContainerInterface $c) {
                $stateFn = $c->get('wpra/more_features_page/plugin_state_fn');

                return apply_filters('wprss_extra_addons', [
                        [
                            'code' => 'ftp',
                            'type' => 'add-on',
                            'title' => 'Feed to Post',
                            'desc' => __(
                                'An advanced importer that lets you import RSS feed items as WordPress posts or any other custom post type. You can use it to populate a website in minutes (auto-blog). This is the most popular and feature-filled extension.',
                                'wprss'
                            ),
                            'url' => 'https://www.wprssaggregator.com/extension/feed-to-post/',
                            'state' => call_user_func_array($stateFn, ['wp-rss-feed-to-post/wp-rss-feed-to-post.php']),
                        ],
                        [
                            'code' => 'ftr',
                            'type' => 'add-on',
                            'title' => 'Full Text RSS Feeds',
                            'desc' => __(
                                'An extension for Feed to Post that adds connectivity to our premium full text service, which allows you to import the full post content for an unlimited number of feed items per feed source, even when the feed itself doesn\'t provide it',
                                'wprss'
                            ),
                            'url' => 'https://www.wprssaggregator.com/extension/full-text-rss-feeds/',
                            'state' => call_user_func_array($stateFn, ['wp-rss-full-text-feeds/wp-rss-full-text.php']),
                        ],
                        [
                            'code' => 'tmp',
                            'type' => 'add-on',
                            'title' => 'Templates',
                            'desc' => __(
                                'Premium templates to display images and excerpts in various ways. It includes a fully customisable grid template and a list template that includes excerpts & thumbnails, both of which will spruce up your site!',
                                'wprss'
                            ),
                            'url' => 'https://www.wprssaggregator.com/extension/templates/',
                            'state' => call_user_func_array($stateFn, ['wp-rss-templates/wp-rss-templates.php']),
                        ],
                        [
                            'code' => 'kf',
                            'type' => 'add-on',
                            'title' => 'Keyword Filtering',
                            'desc' => __(
                                'Filters the feed items to be imported based on your own keywords, key phrases, or tags; you only get the items you\'re interested in. It is compatible with all other add-ons.',
                                'wprss'
                            ),
                            'url' => 'https://www.wprssaggregator.com/extension/keyword-filtering/',
                            'state' => call_user_func_array($stateFn,
                                ['wp-rss-keyword-filtering/wp-rss-keyword-filtering.php']),
                        ],
                        [
                            'code' => 'c',
                            'type' => 'add-on',
                            'title' => 'Source Categories',
                            'desc' => __(
                                'Categorises your feed sources and allows you to display feed items from a particular category within your site using the shortcode parameters.',
                                'wprss'
                            ),
                            'url' => 'https://www.wprssaggregator.com/extension/categories/',
                            'state' => call_user_func_array($stateFn, ['wp-rss-categories/wp-rss-categories.php']),
                        ],
                        [
                            'code' => 'wai',
                            'type' => 'add-on',
                            'title' => 'WordAi',
                            'desc' => __(
                                'An extension for Feed to Post that allows you to integrate the WordAi article spinner so that the imported content is both completely unique and completely readable.',
                                'wprss'
                            ),
                            'url' => 'https://www.wprssaggregator.com/extension/wordai/',
                            'state' => call_user_func_array($stateFn, ['wp-rss-wordai/wp-rss-wordai.php']),
                        ],
                        [
                            'code' => 'spc',
                            'type' => 'add-on',
                            'title' => 'SpinnerChief',
                            'desc' => __(
                                'An extension for Feed to Post that allows you to integrate the SpinnerChief article spinner so that the imported content is both completely unique and completely readable.',
                                'wprss'
                            ),
                            'url' => 'https://www.wprssaggregator.com/extension/spinnerchief/',
                            'state' => call_user_func_array($stateFn, ['wp-rss-spinnerchief/wp-rss-spinnerchief.php']),
                        ],
                    ]
                );
            },
            /*
             * Utility function for checking a plugin's state, even if it's inactive.
             *
             * @since [*next-version*]
             */
            'wpra/more_features_page/plugin_state_fn' => function () {
                return function ($basename) {
                    // ACTIVE
                    if (is_plugin_active($basename)) {
                        return 2;
                    }

                    // INSTALLED & INACTIVE
                    if (file_exists(WP_PLUGIN_DIR . '/' . $basename) && is_plugin_inactive($basename)) {
                        return 1;
                    }

                    // NOT INSTALLED
                    return 0;
                };
            },
        );
    }

    /**
     * @inheritdoc
     *
     * @since [*next-version*]
     */
    public function getExtensions()
    {
        return [];
    }
}
