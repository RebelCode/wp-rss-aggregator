<?php

namespace RebelCode\Wpra\Core\Modules;

use Psr\Container\ContainerInterface;
use RebelCode\Wpra\Core\RestApi\Auth\AuthUserIsAdmin;
use RebelCode\Wpra\Core\RestApi\RouteManager;
use RebelCode\Wpra\Core\RestApi\Templates\GetTemplatesHandler;

/**
 * The REST API module for WP RSS Aggregator.
 *
 * @since [*next-version*]
 */
class RestApiModule implements ModuleInterface
{
    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function getServices()
    {
        return [
            'wpra/rest_api/v1/namespace' => function () {
                return 'wpra/v1';
            },

            'wpra/rest_api/v1/route_manager' => function (ContainerInterface $c) {
                return new RouteManager($c->get('wpra/rest_api/v1/namespace'));
            },

            'wpra/rest_api/v1/auth/user_is_admin' => function (ContainerInterface $c) {
                return new AuthUserIsAdmin();
            },

            /*
             * The templates GET handler for the REST API.
             *
             * @since [*next-version*]
             */
            'wpra/rest_api/v1/templates/get_handler' => function (ContainerInterface $c) {
                return new GetTemplatesHandler($c->get('wpra/templates/feeds/collection'));
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
        // Add known routes to the route manager
        $this->addRestApiRoutes($c);

        // Register routes with WordPress
        add_action('rest_api_init', function () use ($c) {
            $c->get('wpra/rest_api/v1/route_manager')->registerRoutes();
        });
    }

    /**
     * Adds the REST API routes to the route manager.
     *
     * @since [*next-version*]
     *
     * @param ContainerInterface $c The container.
     */
    protected function addRestApiRoutes(ContainerInterface $c)
    {
        $manager = $c->get('wpra/rest_api/v1/route_manager');

        $manager->addRoute(
            '/templates',
            ['GET'],
            $c->get('wpra/rest_api/v1/templates/get_handler'),
            $c->get('wpra/rest_api/v1/auth/user_is_admin')
        );
        $manager->addRoute(
            '/templates/(?P<id>[^/]+)',
            ['GET'],
            $c->get('wpra/rest_api/v1/templates/get_handler'),
            $c->get('wpra/rest_api/v1/auth/user_is_admin')
        );
    }
}
