<?php

namespace RebelCode\Wpra\Core\Templates\Feeds;

use Dhii\Exception\CreateInvalidArgumentExceptionCapableTrait;
use Dhii\I18n\StringTranslatingTrait;
use Dhii\Output\CreateTemplateRenderExceptionCapableTrait;
use Dhii\Output\TemplateInterface;
use Dhii\Util\Normalization\NormalizeArrayCapableTrait;
use Exception;
use InvalidArgumentException;
use Psr\Log\LoggerInterface;
use RebelCode\Wpra\Core\Data\Collections\CollectionInterface;
use RebelCode\Wpra\Core\Templates\Feeds\Types\FeedTemplateTypeInterface;
use RebelCode\Wpra\Core\Util\ParseArgsWithSchemaCapableTrait;
use RebelCode\Wpra\Core\Util\SanitizeIdCommaListCapableTrait;

/**
 * An implementation of a standard Dhii template that, depending on context, delegates rendering to a WP RSS
 * Aggregator feeds template.
 *
 * This template is responsible for generating the feed items output for all the constructs (such as the shortcode,
 * Gutenberg block and previews). This implementation will create a standard WP RSS Aggregator feed item query iterator
 * (as an instance of {@link FeedItemsQueryIterator}) and pass it along to the delegate template as part of the context
 * under the "items" key. The iterator's constructor arguments may be included in the render context for this instance
 * to specify which items to render, as "query_sources", "query_exclude", "query_max_num", "query_page" and
 * "query_factory".
 *
 * @since [*next-version*]
 */
class MasterFeedsTemplate implements TemplateInterface
{
    /* @since [*next-version*] */
    use ParseArgsWithSchemaCapableTrait;

    /* @since [*next-version*] */
    use SanitizeIdCommaListCapableTrait;

    /* @since [*next-version*] */
    use NormalizeArrayCapableTrait;

    /* @since [*next-version*] */
    use CreateInvalidArgumentExceptionCapableTrait;

    /* @since [*next-version*] */
    use CreateTemplateRenderExceptionCapableTrait;

    /* @since [*next-version*] */
    use StringTranslatingTrait;

    /**
     * The key from where to read template options.
     *
     * @since [*next-version*]
     */
    const TEMPLATE_OPTIONS_KEY = 'options';

    /**
     * The key to which to write non-schema context options.
     *
     * @since [*next-version*]
     */
    const CTX_OPTIONS_KEY = 'options';

    /**
     * The ID of the template to use by default.
     *
     * @since [*next-version*]
     *
     * @var string
     */
    protected $default;

    /**
     * An associative array of template type instances.
     *
     * @since [*next-version*]
     *
     * @var FeedTemplateTypeInterface[]
     */
    protected $types;

    /**
     * The collection of templates.
     *
     * @since [*next-version*]
     *
     * @var CollectionInterface
     */
    protected $templateCollection;

    /**
     * The collection of templates.
     *
     * @since [*next-version*]
     *
     * @var CollectionInterface
     */
    protected $feedItemCollection;

    /**
     * The logger instance to use for recording errors.
     *
     * @since [*next-version*]
     *
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param string              $default            The name of the template to use by default.
     * @param array               $templateTypes      The available template types.
     * @param CollectionInterface $templateCollection The collection of templates.
     * @param CollectionInterface $feedItemCollection The collection of feed items.
     * @param LoggerInterface     $logger             The logger instance to use for recording errors.
     */
    public function __construct(
        $default,
        $templateTypes,
        CollectionInterface $templateCollection,
        CollectionInterface $feedItemCollection,
        LoggerInterface $logger
    ) {
        $this->types = $templateTypes;
        $this->default = $default;
        $this->templateCollection = $templateCollection;
        $this->feedItemCollection = $feedItemCollection;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function render($ctx = null)
    {
        try {
            $normCtx = $this->_normalizeArray($ctx);
        } catch (InvalidArgumentException $exception) {
            $normCtx = [];
        }

        // Parse the context, putting all non-schema data in an "options" key
        $pCtx = $this->parseArgsWithSchema($normCtx, $this->getContextSchema(), '/', static::CTX_OPTIONS_KEY);

        // If explicitly using legacy rendering, or no template was given and should fallback to legacy rendering,
        // call the old WPRA render function
        if ($pCtx['legacy'] || (!isset($pCtx['template']) && $this->fallBackToLegacySystem())) {
            ob_start();
            wprss_display_feed_items($normCtx);

            return ob_get_clean();
        }

        $key = $pCtx['template'];

        try {
            // Get the template model
            $model = $this->templateCollection[$key];
        } catch (Exception $exception) {
            $model = $this->templateCollection[$this->default];

            $this->logger->warning(
                __('Template "{0}" does not exist or could not be loaded. The "{1}" template was used is instead.'),
                [$key, $this->default]
            );
        }

        // Get the model's template type and its instance
        $type = isset($model['type']) ? $model['type'] : '';
        $template = $this->getTemplateType($type);

        // Prepare the template model and the ctx options
        $mdlOptions = $model[static::TEMPLATE_OPTIONS_KEY];
        $ctxOptions = $pCtx[static::CTX_OPTIONS_KEY];
        // Merge them such that ctx options may override the template's options
        $options = array_merge_recursive(
            $this->_normalizeArray($mdlOptions),
            $this->_normalizeArray($ctxOptions)
        );

        return $template->render($options);
    }

    /**
     * Retrieves the standard WP RSS Aggregator template context schema.
     *
     * @see   ParseArgsWithSchemaCapableTrait::parseArgsWithSchema()
     *
     * @since [*next-version*]
     *
     * @return array
     */
    protected function getContextSchema()
    {
        return [
            'template' => [
                'default' => $this->default,
                'filter' => FILTER_SANITIZE_STRING,
            ],
            'legacy' => [
                'default' => false,
                'filter' => FILTER_VALIDATE_BOOLEAN,
            ],
        ];
    }

    /**
     * Retrieves a template type by key.
     *
     * @since [*next-version*]
     *
     * @param string $key The template type key.
     *
     * @return FeedTemplateTypeInterface The template type instance.
     */
    protected function getTemplateType($key)
    {
        return isset($this->types[$key])
            ? $this->types[$key]
            : $this->types['list'];
    }

    /**
     * Checks whether or not the master feeds template should fall back to the legacy rendering method when no
     * template is explicitly specified in the render context.
     *
     * @since [*next-version*]
     *
     * @return bool True to fall back to the legacy rendering system, false to use the default template.
     */
    protected function fallBackToLegacySystem()
    {
        return apply_filters('wpra/templates/fallback_to_legacy_system', false);
    }
}
