<?php

namespace RebelCode\Wpra\Core\Templates;

use Dhii\Output\TemplateInterface;

/**
 * A template implementation that does nothing.
 *
 * @since [*next-version*]
 */
class NullTemplate implements TemplateInterface
{
    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function render($context = null)
    {
    }
}
