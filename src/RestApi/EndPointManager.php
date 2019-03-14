<?php

namespace RebelCode\Wpra\Core\RestApi;

use Dhii\Validation\Exception\ValidationFailedExceptionInterface;
use Dhii\Validation\ValidatorInterface;
use WP_Error;
use WP_REST_Request;

/**
 * A REST API route manager.
 *
 * @since [*next-version*]
 */
class EndPointManager
{
    /**
     * The config for the routes to register with WordPress.
     *
     * @since [*next-version*]
     *
     * @var array
     */
    protected $endPoints;

    /**
     * The namespace to use for the routes.
     *
     * @since [*next-version*]
     *
     * @var string
     */
    protected $namespace;

    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param string $namespace The namespace to use for the routes.
     */
    public function __construct($namespace)
    {
        $this->endPoints = [];
        $this->namespace = $namespace;
    }

    /**
     * Adds a new REST API route.
     *
     * @since [*next-version*]
     *
     * @param string        $pattern  The route regex pattern.
     * @param string[]      $methods  The supported HTTP methods.
     * @param callable      $endpoint The endpoint callback function.
     * @param callable|null $authFn   Optional authorization callback that returns a list of auth errors.
     */
    public function addEndPoint($pattern, $methods, $endpoint, $authFn = null)
    {
        $this->endPoints[] = [
            'pattern' => $pattern,
            'methods' => $methods,
            'endpoint' => $endpoint,
            'authFn' => $authFn,
        ];
    }

    /**
     * Registers the routes and endpoints with WordPress.
     *
     * @since [*next-version*]
     */
    public function register()
    {
        foreach ($this->endPoints as $route) {
            $pattern = $route['pattern'];
            $methods = $route['methods'];
            $endpoint = $route['endpoint'];
            $authFn = $route['authFn'];

            register_rest_route($this->namespace, $pattern, [
                'methods' => $methods,
                'callback' => $endpoint,
                'permission_callback' => $this->getPermissionCallback($authFn),
            ]);
        }
    }

    /**
     * Retrieves the permissions callback for an auth validator.
     *
     * @since [*next-version*]
     *
     * @param ValidatorInterface $authValidator The validator instance.
     *
     * @return callable The callback.
     */
    protected function getPermissionCallback(ValidatorInterface $authValidator)
    {
        return function (WP_REST_Request $request) use ($authValidator) {
            try {
                return $authValidator->validate($request);
            } catch (ValidationFailedExceptionInterface $exception) {
                return new WP_Error('wprss_not_authorized', __('Unauthorized', 'wprss'), [
                    'status' => 401,
                    'reasons' => $exception->getValidationErrors(),
                ]);
            }
        };
    }
}
