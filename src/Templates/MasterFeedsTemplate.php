<?php

namespace RebelCode\Wpra\Core\Templates;

use Dhii\Exception\CreateInvalidArgumentExceptionCapableTrait;
use Dhii\I18n\StringTranslatingTrait;
use Dhii\Output\CreateTemplateRenderExceptionCapableTrait;
use Dhii\Output\TemplateInterface as DhiiTemplateInterface;
use Dhii\Util\Normalization\NormalizeArrayCapableTrait;
use Psr\Container\ContainerInterface;
use RebelCode\Wpra\Core\Data\ArrayDataSet;
use RebelCode\Wpra\Core\Data\DataSetInterface;
use RuntimeException;

/**
 * An implementation of a standard Dhii template that, depending on context, delegates rendering to a WP RSS
 * Aggregator feeds template.
 *
 * @since [*next-version*]
 */
class MasterFeedsTemplate implements DhiiTemplateInterface
{
    /* @since [*next-version*] */
    use NormalizeArrayCapableTrait;

    /* @since [*next-version*] */
    use CreateInvalidArgumentExceptionCapableTrait;

    /* @since [*next-version*] */
    use CreateTemplateRenderExceptionCapableTrait;

    /* @since [*next-version*] */
    use StringTranslatingTrait;

    /**
     * The available feed templates.
     *
     * @since [*next-version*]
     *
     * @var ContainerInterface
     */
    protected $templates;

    /**
     * The ID of the template to use by default.
     *
     * @since [*next-version*]
     *
     * @var string
     */
    protected $default;

    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param array|DataSetInterface $templates The available feed templates.
     * @param string                 $default   The ID of the template to use by default.
     */
    public function __construct($templates, $default)
    {
        $this->templates = is_array($templates)
            ? new ArrayDataSet($templates)
            : $templates;

        if (!isset($this->templates[$default])) {
            throw new RuntimeException(__('The given default template does not exist in the list'));
        }

        $this->default = $default;
    }

    /**
     * Registers a new child template.
     *
     * @since [*next-version*]
     *
     * @param FeedTemplateInterface $template The template instance.
     */
    public function addTemplate(FeedTemplateInterface $template)
    {
        $this->templates[$template->getId()] = $template;
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function render($ctx = null)
    {
        $arrCtx = $this->_normalizeArray($ctx);

        $templateId = array_key_exists('template', $arrCtx)
            ? $arrCtx['template']
            : $this->default;

        unset($arrCtx['template']);

        if (!isset($this->templates[$templateId])) {
            throw $this->_createTemplateRenderException(
                __('Template "%s" does not exist', WPRSS_TEXT_DOMAIN), null, null, $this, $ctx
            );
        }

        /* @var $template FeedTemplateInterface */
        $template = $this->templates[$templateId];

        return $template->render($arrCtx);
    }
}
