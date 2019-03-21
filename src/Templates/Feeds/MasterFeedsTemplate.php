<?php

namespace RebelCode\Wpra\Core\Templates\Feeds;

use Dhii\Exception\CreateInvalidArgumentExceptionCapableTrait;
use Dhii\I18n\StringTranslatingTrait;
use Dhii\Output\CreateTemplateRenderExceptionCapableTrait;
use Dhii\Output\TemplateInterface;
use Dhii\Util\Normalization\NormalizeArrayCapableTrait;
use Exception;
use InvalidArgumentException;
use RebelCode\Wpra\Core\Data\ArrayDataSet;
use RebelCode\Wpra\Core\Data\DataSetInterface;
use RebelCode\Wpra\Core\Data\MergedDataSet;
use RebelCode\Wpra\Core\Query\FeedItemsQueryIterator;
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
     * @var DataSetInterface
     */
    protected $collection;

    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param string           $default    The name of the template to use by default.
     * @param DataSetInterface $collection The collection of templates.
     */
    public function __construct($default, DataSetInterface $collection)
    {
        $this->types = [];
        $this->default = $default;
        $this->collection = $collection;
    }

    /**
     * Registers a new feed template type.
     *
     * @since [*next-version*]
     *
     * @param FeedTemplateTypeInterface $templateType The feed template type instance.
     */
    public function addTemplateType(FeedTemplateTypeInterface $templateType)
    {
        $this->types[$templateType->getKey()] = $templateType;
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function render($ctx = null)
    {
        try {
            $argCtx = $this->_normalizeArray($ctx);
        } catch (InvalidArgumentException $exception) {
            $argCtx = [];
        }

        // Parse the context
        $arrCtx = $this->parseArgsWithSchema($argCtx, $this->getContextSchema());

        // If using legacy rendering, simply call the old render function
        if ($arrCtx['legacy'] || (!isset($args['templates']) && $this->fallBackToLegacySystem())) {
            ob_start();
            wprss_display_feed_items();
            return ob_get_clean();
        }

        try {
            // Get the template model
            $tKey = $arrCtx['template'];
            $model = $this->collection[$tKey];
        } catch (Exception $exception) {
            $model = null;
        }

        if ($model === null) {
            throw $this->_createTemplateRenderException(
                sprintf(__('Template "%s" does not exist', WPRSS_TEXT_DOMAIN), $tKey),
                null, null, $this, $ctx
            );
        }

        // Get the model's template type and its instance
        $type = isset($model['type']) ? $model['type'] : '';
        $template = $this->getTemplateType($type);

        // Add the items to be rendered to the context
        $arrCtx['items'] = $this->getFeedItemsToRender($arrCtx);

        // Prepare the full context dataset
        $fullCtx = new MergedDataSet(new ArrayDataSet($arrCtx), $model);

        return $template->render($fullCtx);
    }

    /**
     * Retrieves the list of feed items to render.
     *
     * @see   DataSetInterface
     *
     * @since [*next-version*]
     *
     * @param array $ctx The render context.
     *
     * @return DataSetInterface[]|stdClass|Traversable A list of feed item instances.
     */
    protected function getFeedItemsToRender(array $ctx)
    {
        return new FeedItemsQueryIterator(
            $ctx['query_sources'],
            $ctx['query_exclude'],
            $ctx['query_max_num'],
            $ctx['query_page'],
            $ctx['query_factory']
        );
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
            'limit' => [
                'key' => 'query_max_num',
                'default' => wprss_get_general_setting('feed_limit'),
                'filter' => FILTER_VALIDATE_INT,
                'options' => ['min_range' => 1],
            ],
            'source' => [
                'key' => 'query_sources',
                'default' => [],
                'filter' => function ($value) {
                    return $this->sanitizeIdCommaList($value);
                },
            ],
            'sources' => [
                'key' => 'query_sources',
                'default' => [],
                'filter' => function ($value) {
                    return $this->sanitizeIdCommaList($value);
                },
            ],
            'exclude' => [
                'key' => 'query_exclude',
                'default' => [],
                'filter' => function ($value) {
                    return $this->sanitizeIdCommaList($value);
                },
            ],
            'page' => [
                'key' => 'query_page',
                'default' => 1,
                'filter' => FILTER_VALIDATE_INT,
                'options' => ['min_range' => 1],
            ],
            'factory' => [
                'key' => 'query_factory',
                'default' => null,
                'filter' => function ($value) {
                    if (!is_callable($value)) {
                        throw new InvalidArgumentException();
                    }

                    return $value;
                },
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
