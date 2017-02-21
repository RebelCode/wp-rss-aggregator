<?php

namespace Aventura\Wprss\Core\Model;
use Aventura\Wprss\Core\Plugin\Di\AbstractServiceProvider;

/**
 * A generic service provider.
 *
 * Many instances can be created, as needed, to group service definitions,
 * which can be injected in the constructor.
 *
 * @since [*next-version*]
 */
class GenericServiceProvider extends AbstractServiceProvider
{
    /**
     * @since [*next-version*]
     *
     * @param array $services The services to set for this instance, if any.
     */
    public function __construct($data = null, array $services = null)
    {
        parent::__construct($data);

        $this->_setServices($services);
    }
}