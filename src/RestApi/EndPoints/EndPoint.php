<?php

namespace RebelCode\Wpra\Core\RestApi\EndPoints;

use Dhii\Validation\ValidatorInterface;

/**
 * A simple implementation of a REST API endpoint.
 *
 * @since [*next-version*]
 */
class EndPoint implements EndPointInterface
{
    /**
     * The endpoint's route.
     *
     * @since [*next-version*]
     *
     * @var string
     */
    protected $route;

    /**
     * The endpoint's accepted HTTP methods.
     *
     * @since [*next-version*]
     *
     * @var string[]
     */
    protected $methods;

    /**
     * The endpoint's handler.
     *
     * @since [*next-version*]
     *
     * @var callable
     */
    protected $handler;

    /**
     * The endpoint's authorization handler, if any.
     *
     * @since [*next-version*]
     *
     * @var ValidatorInterface|null
     */
    protected $authHandler;

    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param string                  $route       The route.
     * @param string[]                $methods     The accepted HTTP methods.
     * @param callable                $handler     The handler.
     * @param ValidatorInterface|null $authHandler Optional authorization handler.
     */
    public function __construct($route, array $methods, callable $handler, ValidatorInterface $authHandler = null)
    {
        $this->route = $route;
        $this->methods = $methods;
        $this->handler = $handler;
        $this->authHandler = $authHandler;
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function getRoute()
    {
        return $this->route;
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function getMethods()
    {
        return $this->methods;
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function getHandler()
    {
        return $this->handler;
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function getAuthHandler()
    {
        return $this->authHandler;
    }
}
