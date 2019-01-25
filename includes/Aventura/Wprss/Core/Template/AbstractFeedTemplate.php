<?php

namespace Aventura\Wprss\Core\Template;

use Aventura\Wprss\Core\Template\Api\FeedTemplateInterface;
use Dhii\Exception\CreateInvalidArgumentExceptionCapableTrait;
use Dhii\I18n\StringTranslatingTrait;
use Dhii\Util\Normalization\NormalizeArrayCapableTrait;
use RuntimeException;

/**
 * Abstract functionality for feed templates.
 *
 * This abstract handles the processing and parsing of the render context, as well as the querying of feed items based
 * on the render context. Extending consumers are only required to provide a rendering method by implementing the
 * {@link _renderTemplate} abstract method.
 *
 * @since [*next-version*]
 */
abstract class AbstractFeedTemplate implements FeedTemplateInterface
{
    /* @since [*next-version*] */
    use NormalizeArrayCapableTrait;

    /* @since [*next-version*] */
    use CreateInvalidArgumentExceptionCapableTrait;

    /* @since [*next-version*] */
    use StringTranslatingTrait;

    /**
     * Renders the template with the full prepared context.
     *
     * @since [*next-version*]
     *
     * @param array $context The full prepared context.
     *
     * @return string The render result.
     *
     * @throws RuntimeException If an error occurred while rendering.
     */
    abstract protected function _renderTemplate($context);

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function render($context = null)
    {
        // Get the array version of the context
        $arrayCtx = $this->_normalizeArray($context);
        // Actual "modern" filter. Use this instead of the old `wprss_shortcode_args` filter
        $fullCtx = apply_filters('wprss_feed_template_args', $arrayCtx);

        return $this->_renderTemplate($fullCtx);
    }
}
