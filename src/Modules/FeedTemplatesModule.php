<?php

namespace RebelCode\Wpra\Core\Modules;

use Psr\Container\ContainerInterface;
use RebelCode\Wpra\Core\Data\Collections\FeedTemplateCollection;
use RebelCode\Wpra\Core\Modules\FeedTemplates\Handlers\AjaxRenderFeedsTemplateHandler;
use RebelCode\Wpra\Core\Modules\FeedTemplates\Handlers\CreateDefaultFeedTemplateHandler;
use RebelCode\Wpra\Core\Modules\FeedTemplates\Handlers\RenderAdminTemplatesPageHandler;
use RebelCode\Wpra\Core\Modules\Handlers\RegisterCptHandler;
use RebelCode\Wpra\Core\Modules\Handlers\RegisterSubMenuPageHandler;
use RebelCode\Wpra\Core\RestApi\EndPointManager;
use RebelCode\Wpra\Core\Templates\MasterFeedsTemplate;
use RebelCode\Wpra\Core\Templates\Types\ListTemplateType;

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
                    $c->get('wpra/templates/feeds/collection')
                );
            },
            /*
             * The list template type.
             *
             * @since [*next-version*]
             */
            'wpra/templates/feeds/list_template_type' => function (ContainerInterface $c) {
                return new ListTemplateType();
            },
            /*
             * The collection of feed templates.
             *
             * @since [*next-version*]
             */
            'wpra/templates/feeds/collection' => function (ContainerInterface $c) {
                return new FeedTemplateCollection(
                    $c->get('wpra/templates/feeds/cpt_name'),
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
            'wpra/templates/feeds/cpt_name' => function (ContainerInterface $c) {
                return 'wprss_feed_template';
            },
            /*
             * The labels for the feeds template CPT.
             *
             * @since [*next-version*]
             */
            'wpra/templates/feeds/cpt_labels' => function (ContainerInterface $c) {
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
             * The full arguments for the feeds template CPT.
             *
             * @since [*next-version*]
             */
            'wpra/templates/feeds/cpt_args' => function (ContainerInterface $c) {
                return [
                    'exclude_from_search' => true,
                    'publicly_queryable' => false,
                    'show_in_nav_menus' => false,
                    'show_in_admin_bar' => false,
                    'public' => true,
                    'show_ui' => false,
                    'query_var' => 'feed_template',
                    'menu_position' => 100,
                    'show_in_menu' => false,
                    'rewrite' => [
                        'slug' => 'feed-templates',
                        'with_front' => false,
                    ],
                    'capability_type' => 'feed_template',
                    'map_meta_cap' => true,
                    'supports' => ['title'],
                    'labels' => $c->get('wpra/templates/feeds/cpt_labels'),
                ];
            },
            /*
             * The handler that registers the feeds template CPT.
             *
             * @since [*next-version*]
             */
            'wpra/templates/feeds/register_cpt_handler' => function (ContainerInterface $c) {
                return new RegisterCptHandler(
                    $c->get('wpra/templates/feeds/cpt_name'),
                    $c->get('wpra/templates/feeds/cpt_args')
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
             * The handler that renders the admin feeds templates page.
             *
             * @since [*next-version*]
             */
            'wpra/templates/feeds/render_admin_page_handler' => function (ContainerInterface $c) {
                return new RenderAdminTemplatesPageHandler();
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
             * Registers the core template types.
             *
             * @since [*next-version*]
             */
            'wpra/templates/feeds/master_template' => function (ContainerInterface $c, MasterFeedsTemplate $master) {
                $master->addTemplateType($c->get('wpra/templates/feeds/list_template_type'));

                return $master;
            },
            /*
             * Extends the REST API by adding the template endpoints to the endpoint manager.
             *
             * @since [*next-version*]
             */
            'wpra/rest_api/v1/endpoint_manager' => function (ContainerInterface $c, EndPointManager $manager) {
                $manager->addEndPoint(
                    '/templates(?:/(?P<id>[^/]+))?',
                    ['GET'],
                    $c->get('wpra/rest_api/v1/templates/get_endpoint'),
                    $c->get('wpra/rest_api/v1/auth/user_is_admin')
                );
                $manager->addEndPoint(
                    '/templates(?:/(?P<id>[^/]+))?',
                    ['PATCH'],
                    $c->get('wpra/rest_api/v1/templates/patch_endpoint'),
                    $c->get('wpra/rest_api/v1/auth/user_is_admin')
                );
                $manager->addEndPoint(
                    '/templates(?:/(?P<id>[^/]+))?',
                    ['PUT'],
                    $c->get('wpra/rest_api/v1/templates/put_endpoint'),
                    $c->get('wpra/rest_api/v1/auth/user_is_admin')
                );
                $manager->addEndPoint(
                    '/templates(?:/(?P<id>[^/]+))?',
                    ['POST'],
                    $c->get('wpra/rest_api/v1/templates/post_endpoint'),
                    $c->get('wpra/rest_api/v1/auth/user_is_admin')
                );
                $manager->addEndPoint(
                    '/templates(?:/(?P<id>[^/]+))?',
                    ['DELETE'],
                    $c->get('wpra/rest_api/v1/templates/delete_endpoint'),
                    $c->get('wpra/rest_api/v1/auth/user_is_admin')
                );

                return $manager;
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
        // Register the admin submenu
        add_action('admin_menu', $c->get('wpra/templates/feeds/register_submenu_handler'), 9);

        // Hooks in the handler for server-side feed item rendering
        add_action('wp_ajax_wprss_render', [$this, 'serverSideRenderFeeds']);
        add_action('wp_ajax_nopriv_wprss_render', [$this, 'serverSideRenderFeeds']);

        // This ensures that there is always at least one template available, by constructing the core list template
        // from the old general display settings.
        add_action('init', $c->get('wpra/templates/feeds/create_default_template_handler'));
    }
}
