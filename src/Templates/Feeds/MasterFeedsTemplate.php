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
use RebelCode\Wpra\Core\Data\DataSetInterface;
use RebelCode\Wpra\Core\Templates\Feeds\Types\FeedTemplateTypeInterface;
use RebelCode\Wpra\Core\Util\ParseArgsWithSchemaCapableTrait;
use RebelCode\Wpra\Core\Util\SanitizeIdCommaListCapableTrait;
use stdClass;
use Traversable;

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
    public function render($argCtx = null)
    {
        // Parse the context
        $ctx = $this->parseContext($argCtx);
        // Retrieve the template slug from the context
        $tSlug = $ctx['template'];

        // Render using the legacy system if legacy ctx arg is given or no template was specified and the legacy
        // system should be used as a fallback
        if ($ctx['legacy'] || (empty($tSlug) && $this->fallBackToLegacySystem())) {
            return $this->renderLegacy($argCtx);
        }

        // Get the template model instance
        $model = $this->getTemplateModel($tSlug);

        // Merge the model options with the non-schema ctx args
        $options = array_merge_recursive(
            $this->_normalizeArray($model[static::TEMPLATE_OPTIONS_KEY]),
            $this->_normalizeArray($ctx[static::CTX_OPTIONS_KEY])
        );
        // Include the template slug in the context
        $options['slug'] = $tSlug;

        // Get the template type instance and render it
        $tTypeInst = $this->getTemplateType($model);
        $rendered = $tTypeInst->render($options);

        return $rendered;
    }

    /**
     * Parses the render context, normalizing it to an array and filtering it against the schema.
     *
     * @since [*next-version*]
     *
     * @param array|stdClass|Traversable $ctx The render context.
     *
     * @return array The parsed context.
     */
    protected function parseContext($ctx)
    {
        try {
            $normCtx = $this->_normalizeArray($ctx);
        } catch (InvalidArgumentException $exception) {
            $normCtx = [];
        }

        // Parse the context, putting all non-schema data in an "options" key
        $schema = $this->getContextSchema();
        $pCtx = $this->parseArgsWithSchema($normCtx, $schema, '/', static::CTX_OPTIONS_KEY);

        return $pCtx;
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
                'default' => '',
                'filter' => FILTER_SANITIZE_STRING,
            ],
            'legacy' => [
                'default' => false,
                'filter' => FILTER_VALIDATE_BOOLEAN,
            ],
        ];
    }

    /**
     * Retrieves the template model instance for a given post slug.
     *
     * @since [*next-version*]
     *
     * @param string $slug The slug name of the template post.
     *
     * @return DataSetInterface The model instance.
     */
    protected function getTemplateModel($slug)
    {
        // If the template slug is empty, use the default slug
        $slug = empty($slug) ? $this->default : $slug;

        try {
            // Get the template model instance
            $model = $this->templateCollection[$slug];
        } catch (Exception $exception) {
            // Fetch the default template
            $model = $this->templateCollection[$this->default];
            // Include warning in log that the template was not found
            $this->logger->warning(
                __('Template "{0}" does not exist or could not be loaded. The "{1}" template was used is instead.'),
                [$slug, $this->default]
            );
        }

        return $model;
    }

    /**
     * Retrieves the template type instance for a template model.
     *
     * @since [*next-version*]
     *
     * @param DataSetInterface $model The template model.
     *
     * @return FeedTemplateTypeInterface The template type instance.
     */
    protected function getTemplateType(DataSetInterface $model)
    {
        $type = isset($model['type']) ? $model['type'] : '';

        return isset($this->types[$type])
            ? $this->types[$type]
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

    /**
     * Renders using the legacy WP RSS Aggregator display function.
     *
     * @since [*next-version*]
     *
     * @param array|stdClass|Traversable $ctx The context.
     *
     * @return string
     */
    protected function renderLegacy($ctx)
    {
        ob_start();
        wprss_display_feed_items($this->_normalizeArray($ctx));

        return ob_get_clean();
    }
}
