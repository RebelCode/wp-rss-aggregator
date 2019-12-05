<?php

namespace RebelCode\Wpra\Core\Modules;

use Aventura\Wprss\Core\Component\BulkSourceImport;
use Aventura\Wprss\Core\Model\BulkSourceImport\ServiceProvider;
use Dhii\Di\WritableContainerInterface;
use Psr\Container\ContainerInterface;
use RebelCode\Wpra\Core\Templates\NullTemplate;

/**
 * The module that adds the "Bulk Add" tool to WP RSS Aggregator.
 *
 * @since [*next-version*]
 */
class BulkAddToolModule implements ModuleInterface
{
    /**
     * @inheritdoc
     *
     * @since [*next-version*]
     */
    public function getFactories()
    {
        return [
            /*
             * Information about the "Bulk Add" tool.
             *
             * @since [*next-version*]
             */
            'wpra/admin/tools/bulk_add/info' => function (ContainerInterface $c) {
                return [
                    'name' => __('Add Bulk', 'wprss'),
                    'template' => $c->has('wpra/twig/collection')
                        ? $c->get('wpra/twig/collection')['admin/tools/bulk_add.twig']
                        : new NullTemplate(),
                ];
            },
            /*
             * The handler that listens to the bulk add request and creates the feed sources.
             *
             * @since [*next-version*]
             */
            'wpra/admin/tools/bulk_add/handler' => function (ContainerInterface $c) {
                return function () {
                    $feeds = filter_input(INPUT_POST, 'wpra_bulk_feeds', FILTER_DEFAULT);
                    if (empty($feeds)) {
                        return;
                    }

                    // Check nonce
                    check_admin_referer('wpra_bulk_add', 'wpra_bulk_nonce');

                    /* @var $importer BulkSourceImport */
                    $importer = wprss_wp_container()->get(WPRSS_SERVICE_ID_PREFIX . 'bulk_source_import');

                    $results = $importer->import($feeds);
                    wprss()->getAdminAjaxNotices()->addNotice('bulk_feed_import');

                    exit;
                };
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
             * Registers the "Bulk Add" tool.
             *
             * @since [*next-version*]
             */
            'wpra/admin/tools' => function (ContainerInterface $c, $tools) {
                return $tools + ['bulk_add' => $c->get('wpra/admin/tools/bulk_add/info')];
            },
        ];
    }

    /**
     * @inheritdoc
     *
     * @since [*next-version*]
     */
    public function run(ContainerInterface $c)
    {
        // Register the Bulk Add handler
        add_action('admin_init', $c->get('wpra/admin/tools/bulk_add/handler'));

        // Adds the bulk import service provider to the old Aventura container
        add_filter('wprss_core_container_init', function (WritableContainerInterface $container) {
            $serviceProvider = new ServiceProvider(array(
                'notice_service_id_prefix' => \WPRSS_NOTICE_SERVICE_ID_PREFIX,
                'service_id_prefix' => \WPRSS_SERVICE_ID_PREFIX,
                'event_prefix' => \WPRSS_EVENT_PREFIX,
            ));
            $container->register($serviceProvider);
        });
    }
}
