<?php

namespace RebelCode\Wpra\Core\Twig\Extensions;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

/**
 * Twig extension for custom WP RSS Aggregator filters.
 *
 * @since 4.13
 */
class WpraExtension extends AbstractExtension
{
    /**
     * {@inheritdoc}
     *
     * @since 4.13
     */
    public function getFilters()
    {
        return [
            $this->getWpraLinkFilter(),
            $this->getBase64EncodeFilter()
        ];
    }

    /**
     * Retrieves the "wpralink" filter.
     *
     * @since 4.13
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

    /**
     * Retrieves the "base64_encode" filter.
     *
     * @since 4.13
     *
     * @return TwigFilter The filter instance.
     */
    public function getBase64EncodeFilter()
    {
        $name = 'base64_encode';

        $callback = function ($input) {
            return base64_encode($input);
        };

        return new TwigFilter($name, $callback);
    }
}
