<?php

namespace Aventura\Wprss\Core\Template;

use Exception;
use RuntimeException;
use Twig_Error_Loader;
use Twig_Error_Syntax;

/**
 * A generic feed template that uses the twig templating engine for rendering.
 *
 * @since [*next-version*]
 */
class TwigFeedTemplate extends AbstractFeedTemplate
{
    /**
     * The twig-path for the twig template to use.
     *
     * @since [*next-version*]
     *
     * @var string
     */
    protected $template;

    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param string|null $template The twig-path for the twig template to use.
     */
    public function __construct($template)
    {
        $this->template = $template;
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     *
     * @throws Exception
     */
    protected function _renderTemplate($context)
    {
        try {
            return wprss_render_template($this->template, $context);
        } catch (Twig_Error_Loader $loaderEx) {
            throw new RuntimeException(__('Could not load template', WPRSS_TEXT_DOMAIN));
        } catch (Twig_Error_Syntax $e) {
            throw new RuntimeException(
                sprintf(
                    __('Syntax error in template at line %d: %s', WPRSS_TEXT_DOMAIN),
                    $e->getTemplateLine(),
                    $e->getMessage()
                )
            );
        }
    }
}
