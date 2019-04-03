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
        $callback = function ($text, $url, $flag, $options) {
            if (!$flag) {
                return $text;
            }

            $openBehavior = isset($options['links_open_behavior'])
                ? $options['links_open_behavior']
                : '';
            $relNoFollow = isset($options['links_rel_nofollow'])
                ? $options['links_rel_nofollow']
                : '';

            $hrefAttr = sprintf('href="%s"', esc_attr($url));
            $relAttr = ($relNoFollow == 'no_follow')
                ? 'rel="nofollow"'
                : '';

            $targetAttr = '';
            if ($openBehavior === 'blank') {
                $targetAttr = 'target="_blank"';
            } elseif ($openBehavior === 'lightbox') {
                $targetAttr = 'class="colorbox"';
            }

            return sprintf('<a %s %s %s>%s</a>', $hrefAttr, $targetAttr, $relAttr, $text);
        };
        $options = [
            'is_safe' => ['html'],
        ];

        return new TwigFilter($name, $callback, $options);
    }
}
