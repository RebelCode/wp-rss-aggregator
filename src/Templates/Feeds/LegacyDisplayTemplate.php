<?php

namespace RebelCode\Wpra\Core\Templates\Feeds;

use Dhii\Exception\CreateInvalidArgumentExceptionCapableTrait;
use Dhii\I18n\StringTranslatingTrait;
use Dhii\Output\TemplateInterface;
use Dhii\Util\Normalization\NormalizeArrayCapableTrait;
use InvalidArgumentException;

/**
 * A standard template wrapper for the legacy WP RSS Aggregator display template.
 *
 * @since [*next-version*]
 */
class LegacyDisplayTemplate implements TemplateInterface
{
    /* @since [*next-version*] */
    use NormalizeArrayCapableTrait;

    /* @since [*next-version*] */
    use CreateInvalidArgumentExceptionCapableTrait;

    /* @since [*next-version*] */
    use StringTranslatingTrait;

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function render($context = null)
    {
        try {
            $arrCtx = $this->_normalizeArray($context);
        } catch (InvalidArgumentException $exception) {
            $arrCtx = [];
        }

        wp_enqueue_style('wpra-legacy-styles', WPRSS_CSS . 'legacy-styles.css', [], WPRSS_VERSION);

        ob_start();

        wprss_display_feed_items($arrCtx);

        return ob_get_clean();
    }
}
