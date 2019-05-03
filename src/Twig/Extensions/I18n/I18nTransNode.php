<?php

namespace RebelCode\Wpra\Core\Twig\Extensions\I18n;

use Twig_Extensions_Node_Trans;

/**
 * This extension overrides the translation function usage to use WordPress' {@link __()} and {@link _n()} functions.
 *
 * @since [*next-version*]
 */
class I18nTransNode extends Twig_Extensions_Node_Trans
{
    /**
     * @inheritdoc
     *
     * @since [*next-version*]
     */
    protected function getTransFunction($plural)
    {
        return $plural ? '_n' : '__';
    }
}
