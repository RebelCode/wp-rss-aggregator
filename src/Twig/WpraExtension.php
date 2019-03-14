<?php

namespace RebelCode\Wpra\Core\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

/**
 * Twig extension for custom WP RSS Aggregator filters.
 *
 * @since [*next-version*]
 */
class WpraExtension extends AbstractExtension
{
    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function getFilters()
    {
        return [
            $this->getWpraLinkFilter(),
        ];
    }

    /**
     * Retrieves the "wpralink" filter.
     *
     * @since [*next-version*]
     *
     * @return TwigFilter The filter instance.
     */
    protected function getWpraLinkFilter()
    {
        $name = 'wpralink';
        $callback = function ($text, $url, $flag) {
            return wprss_link_display($url, $text, $flag);
        };
        $options = [
            'is_safe' => ['html'],
        ];

        return new TwigFilter($name, $callback, $options);
    }
}
