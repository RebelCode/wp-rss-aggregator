<?php

namespace RebelCode\Wpra\Core\Templates\Feeds;

use Dhii\Output\TemplateInterface;
use InvalidArgumentException;
use RebelCode\Wpra\Core\Util\Normalize;

/**
 * A standard template wrapper for the legacy WP RSS Aggregator display template.
 *
 * @since 4.13
 */
class LegacyDisplayTemplate implements TemplateInterface
{
    /**
     * {@inheritdoc}
     *
     * @since 4.13
     */
    public function render($context = null)
    {
        try {
            $arrCtx = Normalize::toArray($context);
        } catch (InvalidArgumentException $exception) {
            $arrCtx = [];
        }

        wp_enqueue_style('wpra-legacy-styles', WPRSS_CSS . 'legacy-styles.css', [], WPRSS_VERSION);

        ob_start();

        wprss_display_feed_items($arrCtx);

        return ob_get_clean();
    }
}
