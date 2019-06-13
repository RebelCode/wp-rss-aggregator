<?php

namespace RebelCode\Wpra\Core\Twig\Extensions;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

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
            $this->getBase64EncodeFilter(),
            $this->getCloseTagsFilter()
        ];
    }

    /**
     * @inheritdoc
     *
     * @since [*next-version*]
     */
    public function getFunctions()
    {
        return [
            $this->getWpNonceFieldFunction(),
        ];
    }

    /**
     * Retrieves the wp_nonce_field twig function.
     *
     * @since [*next-version*]
     *
     * @return TwigFunction
     */
    protected function getWpNonceFieldFunction()
    {
        return new TwigFunction('wp_nonce_field', 'wp_nonce_field', [
            'is_safe' => ['html']
        ]);
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

    /**
     * Retrieves the "close_tags" Twig filter.
     *
     * @since [*next-version*]
     *
     * @return TwigFilter
     */
    public function getCloseTagsFilter()
    {
        $name = 'close_tags';

        $callback = function ($input) {
            return preg_replace_callback('#<\s*(img|br|hr)\s*([^>]+\s*)>#', function ($matches) {
                return sprintf('<%s %s/>', $matches[1], $matches[2]);
            }, $input);
        };

        return new TwigFilter($name, $callback, [
            'is_safe' => ['html'],
        ]);
    }
}
