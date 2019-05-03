<?php

namespace RebelCode\Wpra\Core\Twig\Extensions\I18n;

use Twig\Extension\AbstractExtension;
use Twig_SimpleFilter;

/**
 * A Twig extension that adds internationalization to templates using WordPress' i18n function.
 *
 * @since [*next-version*]
 */
class WpI18nExtension extends AbstractExtension
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'i18n';
    }

    /**
     * {@inheritdoc}
     */
    public function getTokenParsers()
    {
        return array(new I18nTransTokenParser());
    }

    /**
     * {@inheritdoc}
     */
    public function getFilters()
    {
        return array(
            new Twig_SimpleFilter('trans', '__'),
        );
    }
}
