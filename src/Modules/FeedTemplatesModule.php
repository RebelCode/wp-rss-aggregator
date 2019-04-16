<?php

namespace RebelCode\Wpra\Core\Modules;

use Psr\Container\ContainerInterface;
use Psr\Log\NullLogger;
use RebelCode\Wpra\Core\Data\Collections\NullCollection;
use RebelCode\Wpra\Core\Modules\Handlers\AddCptMetaCapsHandler;
use RebelCode\Wpra\Core\Modules\Handlers\FeedTemplates\AjaxRenderFeedsTemplateHandler;
use RebelCode\Wpra\Core\Modules\Handlers\FeedTemplates\CreateDefaultFeedTemplateHandler;
use RebelCode\Wpra\Core\Modules\Handlers\FeedTemplates\HidePublicTemplateContentHandler;
use RebelCode\Wpra\Core\Modules\Handlers\FeedTemplates\PreviewTemplateRedirectHandler;
use RebelCode\Wpra\Core\Modules\Handlers\FeedTemplates\RenderAdminTemplatesPageHandler;
use RebelCode\Wpra\Core\Modules\Handlers\FeedTemplates\RenderTemplateContentHandler;
use RebelCode\Wpra\Core\Modules\Handlers\NullHandler;
use RebelCode\Wpra\Core\Modules\Handlers\RegisterCptHandler;
use RebelCode\Wpra\Core\Modules\Handlers\RegisterSubMenuPageHandler;
use RebelCode\Wpra\Core\RestApi\EndPoints\EndPoint;
use RebelCode\Wpra\Core\RestApi\EndPoints\FeedTemplates\CreateUpdateTemplateEndPoint;
use RebelCode\Wpra\Core\RestApi\EndPoints\FeedTemplates\DeleteTemplateEndPoint;
use RebelCode\Wpra\Core\RestApi\EndPoints\FeedTemplates\GetTemplatesEndPoint;
use RebelCode\Wpra\Core\RestApi\EndPoints\FeedTemplates\PatchTemplateEndPoint;
use RebelCode\Wpra\Core\RestApi\EndPoints\FeedTemplates\RenderTemplateEndPoint;
use RebelCode\Wpra\Core\Templates\Feeds\FeedTemplateCollection;
use RebelCode\Wpra\Core\Templates\Feeds\MasterFeedsTemplate;
use RebelCode\Wpra\Core\Templates\Feeds\Types\ListTemplateType;

/**
 * The templates module for WP RSS Aggregator.
 *
 * @since [*next-version*]
 */
class FeedTemplatesModule implements ModuleInterface
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
             * The default feed template's slug name.
             *
             * @since [*next-version*]
             */
            'wpra/templates/feeds/default_template' => function (ContainerInterface $c) {
                return 'default';
            },
            /*
             * The master feed template.
             *
             * @since [*next-version*]
             */
            'wpra/templates/feeds/master_template' => function (ContainerInterface $c) {
                return new MasterFeedsTemplate(
                    $c->get('wpra/templates/feeds/default_template'),
                    $c->get('wpra/templates/feeds/template_types'),
                    $c->get('wpra/templates/feeds/collection'),
                    $c->get('wpra/templates/feeds/feed_item_collection'),
                    $c->get('wpra/display/feeds/legacy_template'),
                    $c->get('wpra/templates/feeds/master_template_logger')
                );
            },
            /*
             * The available template types.
             */
            'wpra/templates/feeds/template_types' => function (ContainerInterface $c) {
                return [
                    'list' => $c->get('wpra/templates/feeds/list_template_type'),
                ];
            },
            /*
             * The feed item collection to use with the master template.
             *
             * Uses the collection from the feed items module, if available.
             *
             * @since [*next-version*]
             */
            'wpra/templates/feeds/feed_item_collection' => function (ContainerInterface $c) {
                if (!$c->has('wpra/importer/items/collection')) {
                    return new NullCollection();
                }

                return $c->get('wpra/importer/items/collection');
            },
            /*
             * The logger to use for the master template.
             *
             * Uses the core plugin's loader, if available.
             *
             * @since [*next-version*]
             */
            'wpra/templates/feeds/master_template_logger' => function (ContainerInterface $c) {
                if ($c->has('wpra/logging/logger')) {
                    return new NullLogger();
                }

                return $c->get('wpra/logging/logger');
            },
            /*
             * The collection of file templates.
             *
             * Uses the core plugin's Twig template collection, if available.
             *
             * @since [*next-version*]
             */
            'wpra/templates/feeds/file_template_collection' => function (ContainerInterface $c) {
                if (!$c->has('wpra/twig/collection')) {
                    return new NullCollection();
                }

                return $c->get('wpra/twig/collection');
            },
            /*
             * The list template type.
             *
             * @since [*next-version*]
             */
            'wpra/templates/feeds/list_template_type' => function (ContainerInterface $c) {
                return new ListTemplateType(
                    $c->get('wpra/templates/feeds/file_template_collection'),
                    $c->get('wpra/templates/feeds/feed_item_collection')
                );
            },
            /*
             * The collection of feed templates.
             *
             * @since [*next-version*]
             */
            'wpra/templates/feeds/collection' => function (ContainerInterface $c) {
                return new FeedTemplateCollection(
                    $c->get('wpra/templates/feeds/cpt/name'),
                    $c->get('wpra/templates/feeds/default_template_type')
                );
            },
            /*
             * The handler that creates the default template if there are no user templates.
             *
             * @since [*next-version*]
             */
            'wpra/templates/feeds/create_default_template_handler' => function (ContainerInterface $c) {
                return new CreateDefaultFeedTemplateHandler(
                    $c->get('wpra/templates/feeds/collection'),
                    $c->get('wpra/templates/feeds/default_template_data')
                );
            },
            /*
             * The data for the default feed template.
             *
             * @since [*next-version*]
             */
            'wpra/templates/feeds/default_template_data' => function (ContainerInterface $c) {
                return [
                    'name' => __('Default', 'wprss'),
                    'type' => $c->get('wpra/templates/feeds/default_template_type'),
                ];
            },
            /*
             * The template type to use for the default template.
             *
             * @since [*next-version*]
             */
            'wpra/templates/feeds/default_template_type' => function () {
                return '__built_in';
            },
            /*
             * The handler that responds to AJAX requests with rendered feed items.
             *
             * @since [*next-version*]
             */
            'wpra/templates/feeds/ajax_render_handler' => function (ContainerInterface $c) {
                return new AjaxRenderFeedsTemplateHandler($c->get('wpra/templates/feeds/master_template'));
            },

            /*
             * The name of the feeds template CPT.
             *
             * @since [*next-version*]
             */
            'wpra/templates/feeds/cpt/name' => function (ContainerInterface $c) {
                return 'wprss_feed_template';
            },
            /*
             * The labels for the feeds template CPT.
             *
             * @since [*next-version*]
             */
            'wpra/templates/feeds/cpt/labels' => function (ContainerInterface $c) {
                return [
                    'name' => __('Templates', WPRSS_TEXT_DOMAIN),
                    'singular_name' => __('Template', WPRSS_TEXT_DOMAIN),
                    'add_new' => __('Add New', WPRSS_TEXT_DOMAIN),
                    'all_items' => __('Templates', WPRSS_TEXT_DOMAIN),
                    'add_new_item' => __('Add New Template', WPRSS_TEXT_DOMAIN),
                    'edit_item' => __('Edit Template', WPRSS_TEXT_DOMAIN),
                    'new_item' => __('New Template', WPRSS_TEXT_DOMAIN),
                    'view_item' => __('View Template', WPRSS_TEXT_DOMAIN),
                    'search_items' => __('Search Feeds', WPRSS_TEXT_DOMAIN),
                    'not_found' => __('No Templates Found', WPRSS_TEXT_DOMAIN),
                    'not_found_in_trash' => __('No Templates Found In Trash', WPRSS_TEXT_DOMAIN),
                    'menu_name' => __('Templates', WPRSS_TEXT_DOMAIN),
                ];
            },
            /*
             * The capability for the feed templates CPT.
             *
             * @since [*next-version*]
             */
            'wpra/templates/feeds/cpt/capability' => function () {
                return 'feed_template';
            },
            /*
             * The user roles that have the feed templates CPT capabilities.
             *
             * Equal to the feed sources' CPT capability roles, if available.
             *
             * @since [*next-version*]
             */
            'wpra/templates/feeds/cpt/capability_roles' => function (ContainerInterface $c) {
                if (!$c->has('wpra/feeds/sources/cpt/capability_roles')) {
                    return ['administrator'];
                }

                return $c->get('wpra/feeds/sources/cpt/capability_roles');
            },
            /*
             * The full arguments for the feeds template CPT.
             *
             * @since [*next-version*]
             */
            'wpra/templates/feeds/cpt/args' => function (ContainerInterface $c) {
                return [
                    'exclude_from_search' => true,
                    'publicly_queryable' => true,
                    'show_in_nav_menus' => false,
                    'show_in_admin_bar' => false,
                    'has_archive' => false,
                    'show_ui' => false,
                    'query_var' => 'feed_template',
                    'menu_position' => 100,
                    'show_in_menu' => false,
                    'rewrite' => [
                        'slug' => 'feed-templates',
                        'with_front' => false,
                    ],
                    'capability_type' => $c->get('wpra/templates/feeds/cpt/capability'),
                    'map_meta_cap' => true,
                    'supports' => ['title'],
                    'labels' => $c->get('wpra/templates/feeds/cpt/labels'),
                ];
            },
            /*
             * The admin feeds templates page information.
             *
             * @since [*next-version*]
             */
            'wpra/templates/feeds/submenu_info' => function (ContainerInterface $c) {
                return [
                    'parent' => 'edit.php?post_type=wprss_feed',
                    'slug' => 'wpra_feed_templates',
                    'page_title' => __('Templates', 'wprss'),
                    'menu_label' => __('Templates', 'wprss'),
                    'capability' => 'edit_feed_templates',
                    'callback' => $c->get('wpra/templates/feeds/render_admin_page_handler'),
                ];
            },
            /*
             * The feeds template model structure.
             *
             * @since [*next-version*]
             */
            'wpra/templates/feeds/model_schema' => function (ContainerInterface $c) {
                return [
                    'id' => '',
                    'name' => '',
                    'slug' => '',
                    'type' => 'list',
                    'options' => [
                        'items_max_num' => 15,
                        'title_max_length' => 0,
                        'title_is_link' => true,
                        'pagination_enabled' => true,
                        'pagination_type' => 'default',
                        'source_enabled' => true,
                        'source_prefix' => __('Source:', WPRSS_TEXT_DOMAIN),
                        'source_is_link' => true,
                        'author_enabled' => false,
                        'author_prefix' => __('By', WPRSS_TEXT_DOMAIN),
                        'date_enabled' => true,
                        'date_prefix' => __('Published on:', WPRSS_TEXT_DOMAIN),
                        'date_format' => 'Y-m-d',
                        'date_use_time_ago' => false,
                        'links_behavior' => 'blank',
                        'links_nofollow' => false,
                        'links_video_embed_page' => false,
                        'bullets_enabled' => true,
                        'bullet_type' => 'default',
                        'custom_css_classname' => '',
                    ],
                ];
            },
            /*
             * Tooltips for feed template model fields.
             *
             * @since [*next-version*]
             */
            'wpra/templates/feeds/model_tooltips' => function (ContainerInterface $c) {
                return [
                    'name' => false,
                    'type' => false,
                    'options' => [
                        'items_max_num' => __('The maximum number of feed items to display when using the shortcode. This enables pagination if set to a number smaller than the number of items to be displayed.', WPRSS_TEXT_DOMAIN),
                        'title_max_length' => __('Set the maximum number of characters to show for feed item titles.<hr/><em>Leave empty for no limit.</em>', WPRSS_TEXT_DOMAIN),
                        'title_is_link' => __('Check this box to make the feed item titles link to the original article.', WPRSS_TEXT_DOMAIN),
                        'pagination_enabled' => false,
                        'pagination_type' =>  __('The type of pagination to use when showing feed items on multiple pages. The first shows two links, "Older" and "Newer", which allow you to navigate through the pages. The second shows links for all the pages, together with links for the next and previous pages.', WPRSS_TEXT_DOMAIN),
                        'source_enabled' => __('Enable this option to show the feed source name for each feed item.', WPRSS_TEXT_DOMAIN),
                        'source_prefix' => __('Enter the text that you want to show before the source name. A space is automatically added between this text and the feed source name.', WPRSS_TEXT_DOMAIN),
                        'source_is_link' => __('Enable this option to link the feed source name to the RSS feed\'s source site.', WPRSS_TEXT_DOMAIN),
                        'author_enabled' => __('Check this box to show the author for each feed item, if it is available.', WPRSS_TEXT_DOMAIN),
                        'author_prefix' => false,
                        'date_enabled' => __('Enable this to show the feed item\'s date.', WPRSS_TEXT_DOMAIN),
                        'date_prefix' => __('Enter the text that you want to show before the feed item date. A space is automatically added between this text and the date.', WPRSS_TEXT_DOMAIN),
                        'date_format' => __('The format to use for the feed item dates, as a PHP date format.', WPRSS_TEXT_DOMAIN),
                        'date_use_time_ago' => __('Enable this option to show the elapsed time from the feed item\'s date and time to the present time. <em>Eg. 2 hours ago</em>', WPRSS_TEXT_DOMAIN),
                        'links_behavior' => __('Choose how you want links to be opened. This applies to the feed item title and the source link.', WPRSS_TEXT_DOMAIN),
                        'links_nofollow' => __('Enable this option to set all links displayed as "NoFollow".<hr/>"Nofollow" provides a way to tell search engines to <em>not</em> follow certain links, such as links to feed items in this case.', WPRSS_TEXT_DOMAIN),
                        'links_video_embed_page' => __('For feed items from YouTube, Vimeo or Dailymotion, you can choose whether you want to have the items link to the original page link, or a link to the embedded video player only.', WPRSS_TEXT_DOMAIN),
                        'bullets_enabled' => false,
                        'bullet_type' => false,
                        'custom_css_classname' => false,
                    ],
                ];
            },
            /*
             * Feed template's fields options.
             *
             * @since [*next-version*]
             */
            'wpra/templates/feeds/template_options' => function (ContainerInterface $c) {
                return [
                    'type' => [
                        '__built_in' => __('List', WPRSS_TEXT_DOMAIN),
                        'list' => __('List', WPRSS_TEXT_DOMAIN),
                        // 'grid' => __('Grid', WPRSS_TEXT_DOMAIN),
                    ],
                    'links_behavior' => [
                        'self' => __('Open in same tab/window', WPRSS_TEXT_DOMAIN),
                        'blank' => __('Open in a new tab', WPRSS_TEXT_DOMAIN),
                        'lightbox' => __('Open in a lightbox', WPRSS_TEXT_DOMAIN),
                    ],
                    'pagination_type' => [
                        'default' => __('Older/Newer', WPRSS_TEXT_DOMAIN),
                        'numbered' => __('Numbered', WPRSS_TEXT_DOMAIN),
                    ],
                    'bullet_type' => [
                        'default' => __('Bullets', WPRSS_TEXT_DOMAIN),
                        'numbers' => __('Numbers', WPRSS_TEXT_DOMAIN),
                    ],
                    'links_video_embed_page' => [
                        'false' => __('Original page link', WPRSS_TEXT_DOMAIN),
                        'true' => __('Embedded video player link', WPRSS_TEXT_DOMAIN),
                    ],
                ];
            },
            /*
             * The templates GET endpoint for the REST API.
             *
             * @since [*next-version*]
             */
            'wpra/templates/feeds/rest_api/v1/get_endpoint' => function (ContainerInterface $c) {
                return new GetTemplatesEndPoint($c->get('wpra/templates/feeds/collection'));
            },
            /*
             * The templates PATCH endpoint for the REST API.
             *
             * @since [*next-version*]
             */
            'wpra/templates/feeds/rest_api/v1/patch_endpoint' => function (ContainerInterface $c) {
                return new PatchTemplateEndPoint($c->get('wpra/templates/feeds/collection'));
            },
            /*
             * The templates POST endpoint for the REST API.
             *
             * @since [*next-version*]
             */
            'wpra/templates/feeds/rest_api/v1/post_endpoint' => function (ContainerInterface $c) {
                return new CreateUpdateTemplateEndPoint($c->get('wpra/templates/feeds/collection'), false);
            },
            /*
             * The templates PUT endpoint for the REST API.
             *
             * @since [*next-version*]
             */
            'wpra/templates/feeds/rest_api/v1/put_endpoint' => function (ContainerInterface $c) {
                return new CreateUpdateTemplateEndPoint($c->get('wpra/templates/feeds/collection'));
            },
            /*
             * The templates deletion endpoint for the REST API.
             *
             * @since [*next-version*]
             */
            'wpra/templates/feeds/rest_api/v1/delete_endpoint' => function (ContainerInterface $c) {
                return new DeleteTemplateEndPoint($c->get('wpra/templates/feeds/collection'));
            },
            /*
             * The templates rendering endpoint for the REST API.
             *
             * @since [*next-version*]
             */
            'wpra/templates/feeds/rest_api/v1/render_endpoint' => function (ContainerInterface $c) {
                return new RenderTemplateEndPoint(
                    $c->get('wpra/display/feeds/template')
                );
            },

            /*
             * The handler that registers the feeds template CPT.
             *
             * @since [*next-version*]
             */
            'wpra/templates/feeds/register_cpt_handler' => function (ContainerInterface $c) {
                return new RegisterCptHandler(
                    $c->get('wpra/templates/feeds/cpt/name'),
                    $c->get('wpra/templates/feeds/cpt/args')
                );
            },
            /*
             * The handler that registers the feeds template submenu page.
             *
             * @since [*next-version*]
             */
            'wpra/templates/feeds/register_submenu_handler' => function (ContainerInterface $c) {
                return new RegisterSubMenuPageHandler($c->get('wpra/templates/feeds/submenu_info'));
            },
            /*
             * The handler that adds the feed templates CPT capabilities to the appropriate user roles.
             *
             * Resolves to a null handler if the WordPress role manager is not available.
             *
             * @since [*next-version*]
             */
            'wpra/templates/feeds/add_cpt_capabilities_handler' => function (ContainerInterface $c) {
                if (!$c->has('wp/roles')) {
                    return new NullHandler();
                }

                return new AddCptMetaCapsHandler(
                    $c->get('wp/roles'),
                    $c->get('wpra/templates/feeds/cpt/capability_roles'),
                    $c->get('wpra/templates/feeds/cpt/capability')
                );
            },
            /*
             * The handler that renders the admin feeds templates page.
             *
             * @since [*next-version*]
             */
            'wpra/templates/feeds/render_admin_page_handler' => function (ContainerInterface $c) {
                return new RenderAdminTemplatesPageHandler(
                    $c->get('wpra/templates/feeds/model_schema'),
                    $c->get('wpra/templates/feeds/model_tooltips'),
                    $c->get('wpra/templates/feeds/template_options')
                );
            },
            /*
             * The handler that renders template content.
             *
             * @since [*next-version*]
             */
            'wpra/templates/feeds/handlers/render_content' => function (ContainerInterface $c) {
                return new RenderTemplateContentHandler(
                    $c->get('wpra/templates/feeds/cpt/name'),
                    $c->get('wpra/templates/feeds/master_template')
                );
            },
            /*
             * The handler that hides template content from the public-facing side.
             *
             * @since [*next-version*]
             */
            'wpra/templates/feeds/handlers/hide_public_content' => function (ContainerInterface $c) {
                return new HidePublicTemplateContentHandler(
                    $c->get('wpra/templates/feeds/cpt/name'),
                    $c->get('wpra/templates/feeds/public_template_content_nonce')
                );
            },
            /*
             * The name of the nonce that allows template content to be shown on the public-facing side.
             *
             * @since [*next-version*]
             */
            'wpra/templates/feeds/public_template_content_nonce' => function (ContainerInterface $c) {
                return 'wpra_template_preview';
            },
            /*
             * The handler that listens to requests for previewing templates.
             *
             * @since [*next-version*]
             */
            'wpra/templates/feeds/handlers/preview_template_request' => function (ContainerInterface $c) {
                return new PreviewTemplateRedirectHandler(
                    $c->get('wpra/templates/feeds/preview_template_request_param'),
                    $c->get('wpra/templates/feeds/public_template_content_nonce'),
                    $c->get('wpra/templates/feeds/cpt/capability')
                );
            },
            /*
             * The name of the GET parameter to detect for previewing templates.
             *
             * @since [*next-version*]
             */
            'wpra/templates/feeds/preview_template_request_param' => function (ContainerInterface $c) {
                return 'wpra_preview_template';
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
        return [
            /*
             * Overrides the core display template with the master template.
             *
             * @since [*next-version*]
             */
            'wpra/display/feeds/template' => function (ContainerInterface $c) {
                return $c->get('wpra/templates/feeds/master_template');
            },
            /*
             * Extends the list of REST API endpoints with the template endpoints.
             *
             * @since [*next-version*]
             */
            'wpra/rest_api/v1/endpoints' => function (ContainerInterface $c, $endPoints) {
                $endPoints['get_templates'] = new EndPoint(
                    '/templates(?:/(?P<id>[^/]+))?',
                    ['GET'],
                    $c->get('wpra/templates/feeds/rest_api/v1/get_endpoint'),
                    $c->get('wpra/rest_api/v1/auth/user_is_admin')
                );
                $endPoints['patch_templates'] = new EndPoint(
                    '/templates/(?P<id>[^/]+)',
                    ['PATCH'],
                    $c->get('wpra/templates/feeds/rest_api/v1/patch_endpoint'),
                    $c->get('wpra/rest_api/v1/auth/user_is_admin')
                );
                $endPoints['put_templates'] = new EndPoint(
                    '/templates(?:/(?P<id>[^/]+))?',
                    ['PUT'],
                    $c->get('wpra/templates/feeds/rest_api/v1/put_endpoint'),
                    $c->get('wpra/rest_api/v1/auth/user_is_admin')
                );
                $endPoints['post_templates'] = new EndPoint(
                    '/templates(?:/(?P<id>[^/]+))?',
                    ['POST'],
                    $c->get('wpra/templates/feeds/rest_api/v1/post_endpoint'),
                    $c->get('wpra/rest_api/v1/auth/user_is_admin')
                );
                $endPoints['delete_templates'] = new EndPoint(
                    '/templates/(?P<id>[^/]+)',
                    ['DELETE'],
                    $c->get('wpra/templates/feeds/rest_api/v1/delete_endpoint'),
                    $c->get('wpra/rest_api/v1/auth/user_is_admin')
                );
                $endPoints['render_templates'] = new EndPoint(
                    '/templates/(?P<template>[^/]+)/render',
                    ['POST'],
                    $c->get('wpra/templates/feeds/rest_api/v1/render_endpoint')
                );

                return $endPoints;
            },
        ];
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function run(ContainerInterface $c)
    {
        // Register the CPT
        add_action('init', $c->get('wpra/templates/feeds/register_cpt_handler'));
        // Add the capabilities
        add_action('admin_init', $c->get('wpra/templates/feeds/add_cpt_capabilities_handler'));
        // Register the admin submenu, unless E&T is active
        add_action('plugins_loaded', function () use ($c) {
            if (!defined('WPRSS_ET_VERSION')) {
                add_action('admin_menu', $c->get('wpra/templates/feeds/register_submenu_handler'));
            }
        });

        // Hooks in the handler for server-side feed item rendering
        add_action('wp_ajax_wprss_render', [$this, 'serverSideRenderFeeds']);
        add_action('wp_ajax_nopriv_wprss_render', [$this, 'serverSideRenderFeeds']);

        // This ensures that there is always at least one template available, by constructing the core list template
        // from the old general display settings.
        add_action('init', $c->get('wpra/templates/feeds/create_default_template_handler'));

        // Filters the front-end content for templates to render them
        add_action('the_content', $c->get('wpra/templates/feeds/handlers/render_content'));

        // Hooks in the handler that hides template content from the front-end by requiring a nonce
        add_action('wp_head', $c->get('wpra/templates/feeds/handlers/hide_public_content'));

        // Hooks in the handler that listens to template preview requests
        add_action('init', $c->get('wpra/templates/feeds/handlers/preview_template_request'));
    }
}
