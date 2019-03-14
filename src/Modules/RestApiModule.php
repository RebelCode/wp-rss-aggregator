<?php

namespace RebelCode\Wpra\Core\Modules;

use Psr\Container\ContainerInterface;
use RebelCode\Wpra\Core\RestApi\Auth\AuthUserIsAdmin;
use RebelCode\Wpra\Core\RestApi\EndPointManager;
use RebelCode\Wpra\Core\RestApi\EndPoints\Templates\GetTemplatesEndPoint;

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
                return new EndPointManager($c->get('wpra/rest_api/v1/namespace'));
            },

            'wpra/rest_api/v1/auth/user_is_admin' => function (ContainerInterface $c) {
                return new AuthUserIsAdmin();
            },

            /*
             * The templates GET endpoint for the REST API.
             *
             * @since [*next-version*]
             */
            'wpra/rest_api/v1/templates/get_endpoint' => function (ContainerInterface $c) {
                return new GetTemplatesEndPoint($c->get('wpra/templates/feeds/collection'));
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
        $this->addRestApiEndpoints($c);

        // Register routes with WordPress
        add_action('rest_api_init', function () use ($c) {
            /* @var $manager EndPointManager */
            $manager = $c->get('wpra/rest_api/v1/route_manager');
            $manager->register();
        });
    }

    /**
     * Adds the REST API endpoints to the route manager.
     *
     * @since [*next-version*]
     *
     * @param ContainerInterface $c The container.
     */
    protected function addRestApiEndpoints(ContainerInterface $c)
    {
        /* @var $manager EndPointManager */
        $manager = $c->get('wpra/rest_api/v1/route_manager');

        $manager->addEndPoint(
            '/templates',
            ['GET'],
            $c->get('wpra/rest_api/v1/templates/get_endpoint'),
            $c->get('wpra/rest_api/v1/auth/user_is_admin')
        );
        $manager->addEndPoint(
            '/templates/(?P<id>[^/]+)',
            ['GET'],
            $c->get('wpra/rest_api/v1/templates/get_endpoint'),
            $c->get('wpra/rest_api/v1/auth/user_is_admin')
        );
    }
}
