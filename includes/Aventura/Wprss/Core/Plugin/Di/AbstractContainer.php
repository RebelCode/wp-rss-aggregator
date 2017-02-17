<?php

namespace Aventura\Wprss\Core\Plugin\Di;

use Dhii\Di\AbstractParentAwareContainer as BaseParentAwareContainer;
use Dhii\Di\ParentAwareContainerInterface;

/**
 * Common functionality for containers.
 *
 * @since [*next-version*]
 */
abstract class AbstractContainer extends BaseParentAwareContainer implements
    ContainerInterface,
    ParentAwareContainerInterface
{
    /**
     * Parameter-less constructor.
     *
     * @since [*next-version*]
     */
    protected function _construct()
    {
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     *
     * @param string $id The ID of the service to check for.
     *
     * @return bool True if a service exists with the given ID; false otherwise.
     */
    public function has($id)
    {
        return $this->_has($id);
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     *
     * @param string $id The ID of the service to retrieve.
     *
     * @throws NotFoundException If no service with the given ID exists in the container.
     *
     * @return mixed The service with the matching ID.
     */
    public function get($id)
    {
        return $this->_get($id);
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function getParentContainer()
    {
        return $this->_getParentContainer();
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     *
     * @return NotFoundException The new exception instance.
     */
    protected function _createNotFoundException($message, $code = 0, Exception $innerException = null)
    {
        return new NotFoundException($message, $code, $innerException);
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     *
     * @return ContainerException The new exception instance.
     */
    protected function _createContainerException($message, $code = 0, Exception $innerException = null)
    {
        return new ContainerException($message, $code, $innerException);
    }
}
