<?php

namespace RebelCode\Wpra\Core\Modules;

use Psr\Container\ContainerInterface;
use RebelCode\Wpra\Core\Collections\FeedTemplateCollection;
use RebelCode\Wpra\Core\Handlers\Templates\AjaxRenderFeedsTemplateHandler;
use RebelCode\Wpra\Core\Handlers\Templates\CreateDefaultFeedTemplateHandler;
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
    public function getServices()
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
                return new FeedTemplateCollection();
            },
            /**
             * The handler that creates the default template if there are no user templates.
             *
             * @since [*next-version*]
             */
            'wpra/templates/feeds/create_default_template_handler' => function (ContainerInterface $c) {
                return new CreateDefaultFeedTemplateHandler($c->get('wpra/templates/feeds/collection'));
            },
            /**
             * The handler that responds to AJAX requests with rendered feed items.
             *
             * @since [*next-version*]
             */
            'wpra/templates/feeds/ajax_render_handler' => function (ContainerInterface $c) {
                return new AjaxRenderFeedsTemplateHandler($c->get('wpra/templates/feeds/master_template'));
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
        // Register the template types
        $this->registerTemplateTypes($c);

        // Hooks in the handler for server-side feed item rendering
        add_action('wp_ajax_wprss_render', [$this, 'serverSideRenderFeeds']);
        add_action('wp_ajax_nopriv_wprss_render', [$this, 'serverSideRenderFeeds']);

        // This ensures that there is always at least one template available, by constructing the core list template
        // from the old general display settings.
        add_action('init', $c->get('wpra/templates/feeds/create_default_template_handler'));
    }

    /**
     * Registers the template types with the master template.
     *
     * @since [*next-version*]
     *
     * @param ContainerInterface $c The container.
     */
    protected function registerTemplateTypes(ContainerInterface $c)
    {
        // Get the master feeds template
        $master = $c->get('wpra/templates/feeds/master_template');

        // Add the "list" template type
        $master->addTemplateType($c->get('wpra/templates/feeds/list_template_type'));
    }

    /**
     * The handler for server-side feed item rendering.
     *
     * @since [*next-version*]
     */
    public function serverSideRenderFeeds()
    {
        $args = filter_input(
            INPUT_GET,
            'wprss_render_args',
            FILTER_DEFAULT,
            FILTER_REQUIRE_ARRAY | FILTER_NULL_ON_FAILURE
        );
        $args = is_array($args) ? $args : [];

        echo json_encode(['render' => wprss_render_feeds($args), 'page' => $args['page']]);
        die;
    }
}
