<?php

namespace RebelCode\Wpra\Core\RestApi;

/**
 * A REST API route manager.
 *
 * @since [*next-version*]
 */
class RouteManager
{
    /**
     * The config for the routes to register with WordPress.
     *
     * @since [*next-version*]
     *
     * @var array
     */
    protected $routes;

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
        $this->routes = [];
        $this->namespace = $namespace;
    }

    /**
     * Adds a new REST API route.
     *
     * @since [*next-version*]
     *
     * @param      $pattern
     * @param      $methods
     * @param      $handler
     * @param null $authFn
     */
    public function addRoute($pattern, $methods, $handler, $authFn = null)
    {
        $this->routes[] = [
            'pattern' => $pattern,
            'methods' => $methods,
            'handler' => $handler,
            'authFn' => $authFn,
        ];
    }

    /**
     * Registers the routes with WordPress.
     *
     * @since [*next-version*]
     */
    public function registerRoutes()
    {
        foreach ($this->routes as $route) {
            $pattern = $route['pattern'];
            $methods = $route['methods'];
            $handler = $route['handler'];
            $authFn = $route['authFn'];

            register_rest_route($this->namespace, $pattern, [
                'methods' => $methods,
                'callback' => $handler,
                'permission_callback' => $authFn,
            ]);
        }
    }
}
