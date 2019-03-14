<?php

namespace RebelCode\Wpra\Core\Modules;

use Psr\Container\ContainerInterface;
use RebelCode\Wpra\Core\RestApi\Auth\AuthUserIsAdmin;
use RebelCode\Wpra\Core\RestApi\EndPointManager;
use RebelCode\Wpra\Core\RestApi\EndPoints\Templates\CreateUpdateTemplateEndPoint;
use RebelCode\Wpra\Core\RestApi\EndPoints\Templates\DeleteTemplateEndPoint;
use RebelCode\Wpra\Core\RestApi\EndPoints\Templates\GetTemplatesEndPoint;
use RebelCode\Wpra\Core\RestApi\EndPoints\Templates\PatchTemplateEndPoint;

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
            /*
             * The WP RSS Aggregator REST API namespace.
             *
             * @since [*next-version*]
             */
            'wpra/rest_api/v1/namespace' => function () {
                return 'wpra/v1';
            },
            /*
             * The endpoint manager.
             *
             * @since [*next-version*]
             */
            'wpra/rest_api/v1/endpoint_manager' => function (ContainerInterface $c) {
                return new EndPointManager($c->get('wpra/rest_api/v1/namespace'));
            },
            /*
             * The authorization callback function to checking if the request user is a logged-in admin.
             *
             * @since [*next-version*]
             */
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
            /*
             * The templates PATCH endpoint for the REST API.
             *
             * @since [*next-version*]
             */
            'wpra/rest_api/v1/templates/patch_endpoint' => function (ContainerInterface $c) {
                return new PatchTemplateEndPoint($c->get('wpra/templates/feeds/collection'));
            },
            /*
             * The templates POST endpoint for the REST API.
             *
             * @since [*next-version*]
             */
            'wpra/rest_api/v1/templates/post_endpoint' => function (ContainerInterface $c) {
                return new CreateUpdateTemplateEndPoint($c->get('wpra/templates/feeds/collection'), false);
            },
            /*
             * The templates PUT endpoint for the REST API.
             *
             * @since [*next-version*]
             */
            'wpra/rest_api/v1/templates/put_endpoint' => function (ContainerInterface $c) {
                return new CreateUpdateTemplateEndPoint($c->get('wpra/templates/feeds/collection'));
            },
            /*
             * The templates deletion endpoint for the REST API.
             *
             * @since [*next-version*]
             */
            'wpra/rest_api/v1/templates/delete_endpoint' => function (ContainerInterface $c) {
                return new DeleteTemplateEndPoint($c->get('wpra/templates/feeds/collection'));
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
            $manager = $c->get('wpra/rest_api/v1/endpoint_manager');
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
        $manager = $c->get('wpra/rest_api/v1/endpoint_manager');

        $this->addTemplateRestApiEndPoints($manager, $c);
    }

    /**
     * Adds the template REST API endpoints to the endpoint manager.
     *
     * @since [*next-version*]
     *
     * @param EndPointManager    $manager The endpoint manager.
     * @param ContainerInterface $c The container.
     */
    protected function addTemplateRestApiEndPoints(EndPointManager $manager, ContainerInterface $c)
    {
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
    }
}
