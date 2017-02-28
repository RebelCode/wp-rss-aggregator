<?php

namespace Aventura\Wprss\Core\Block;

/**
 * A block that renders using a callback function.
 *
 * @since [*next-version*]
 */
class CallbackBlock extends AbstractBlock
{
    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     *
     * @param array $data
     * @param \callable $callback
     */
    public function __construct(array $data = array(), $callback = null)
    {
        parent::__construct($data);

        $this->setCallback($callback);
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function getOutput()
    {
        $callback = $this->getCallback();

        return is_callable($callback)
            ? call_user_func($callback)
            : '';
    }
}
