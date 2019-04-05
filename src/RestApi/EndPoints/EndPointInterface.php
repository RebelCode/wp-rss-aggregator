<?php

namespace RebelCode\Wpra\Core\RestApi\EndPoints;

use Dhii\Validation\ValidatorInterface;

/**
 * An interface that represents a REST API endpoint.
 *
 * @since [*next-version*]
 */
interface EndPointInterface
{
    /**
     * Retrieves the endpoint's route.
     *
     * @since [*next-version*]
     *
     * @return string
     */
    public function getRoute();

    /**
     * Retrieves the endpoint's accepted HTTP methods.
     *
     * @since [*next-version*]
     *
     * @return string[]
     */
    public function getMethods();

    /**
     * Retrieves the endpoint's handler.
     *
     * @since [*next-version*]
     *
     * @return callable
     */
    public function getHandler();

    /**
     * Retrieves the endpoint's authorization validator, if any.
     *
     * @since [*next-version*]
     *
     * @return ValidatorInterface|null
     */
    public function getAuthHandler();
}
