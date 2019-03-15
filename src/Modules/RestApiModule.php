<?php

namespace RebelCode\Wpra\Core\Modules;

use Psr\Container\ContainerInterface;
use RebelCode\Wpra\Core\RestApi\Auth\AuthUserIsAdmin;
use RebelCode\Wpra\Core\RestApi\EndPointManager;
use RebelCode\Wpra\Core\RestApi\EndPoints\FeedTemplates\CreateUpdateTemplateEndPoint;
use RebelCode\Wpra\Core\RestApi\EndPoints\FeedTemplates\DeleteTemplateEndPoint;
use RebelCode\Wpra\Core\RestApi\EndPoints\FeedTemplates\GetTemplatesEndPoint;
use RebelCode\Wpra\Core\RestApi\EndPoints\FeedTemplates\PatchTemplateEndPoint;

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
    public function getFactories()
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
        // Register routes with WordPress
        add_action('rest_api_init', function () use ($c) {
            /* @var $manager EndPointManager */
            $manager = $c->get('wpra/rest_api/v1/endpoint_manager');
            $manager->register();
        });
    }
}
