<?php

namespace RebelCode\Wpra\Core\Modules\Handlers;

use stdClass;
use Traversable;

/**
 * A generic handler implementation that invokes a list of children handlers in sequence.
 *
 * @since [*next-version*]
 */
class MultiHandler
{
    /**
     * The list of handlers to invoke.
     *
     * @since [*next-version*]
     *
     * @var callable[]|stdClass|Traversable
     */
    protected $handlers;

    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param callable[]|stdClass|Traversable $handlers The list of handlers to invoke.
     */
    public function __construct($handlers)
    {
        $this->handlers = $handlers;
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function __invoke()
    {
        $args = func_get_args();

        foreach ($this->handlers as $handler) {
            call_user_func_array($handler, $args);
        }
    }
}
