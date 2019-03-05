<?php

namespace RebelCode\Wpra\Core\Templates;

use Dhii\Exception\CreateInvalidArgumentExceptionCapableTrait;
use Dhii\I18n\StringTranslatingTrait;
use Dhii\Output\CreateTemplateRenderExceptionCapableTrait;
use Dhii\Util\Normalization\NormalizeArrayCapableTrait;
use Exception;
use InvalidArgumentException;
use RebelCode\Wpra\Core\Data\ArrayDataSet;
use RebelCode\Wpra\Core\Data\DataSetInterface;
use RebelCode\Wpra\Core\Data\WpOptionsDataSet;
use RebelCode\Wpra\Core\Query\FeedItemsQueryIterator;
use RebelCode\Wpra\Core\Util\ParseArgsWithSchemaCapableTrait;
use RebelCode\Wpra\Core\Util\SanitizeIdCommaListCapableTrait;
use stdClass;
use Traversable;
use Twig_Error_Loader;
use Twig_Error_Syntax;

/**
 * Abstract implementation of a standard WP RSS Aggregator template.
 *
 * By default, this implementation loads twig template files according to the pattern `feeds/<id>/main.twig`, where
 * `<id>` is the ID of the template. This can be changed by overriding the {@link getTemplatePath} method.
 * Each instance also stores its options in separate records in the `wp_options` table. Each record follows the
 * naming scheme `wprss_templates/<id>` where `<id>` is the ID of the template.
 *
 * For rendering, this implementation requires a render context that contains the list of feed items to render mapped
 * to the `items` key and any additional template options to be mapped to the `options` key. Only the `items` key is
 * required. Extensions may impose different requirements.
 *
 * Twig template files will have access to both of those keys, together with a `template` key that will map to an array
 * of information about the template. The `options` data will also be wrapped in a {@link DataSetInterface} instance
 * but since such instances may be interfaced like arrays, this wrapping should affect the twig template.
 *
 * This implementation uses a 3-layered approach for its options. Options given in a render context are optional. Any
 * options not specified in the render context are retrieved from the template's own internal options data set. This
 * data set is populated with options from the database as previously mentioned. On top of this, a default data set is
 * used to provide default option values for those options that are not present in storage.
 *
 * @since [*next-version*]
 */
abstract class AbstractFeedTemplate implements FeedTemplateInterface
{
    /* @since [*next-version*] */
    use NormalizeArrayCapableTrait;

    /* @since [*next-version*] */
    use ParseArgsWithSchemaCapableTrait;

    /* @since [*next-version*] */
    use SanitizeIdCommaListCapableTrait;

    /* @since [*next-version*] */
    use CreateInvalidArgumentExceptionCapableTrait;

    /* @since [*next-version*] */
    use CreateTemplateRenderExceptionCapableTrait;

    /* @since [*next-version*] */
    use StringTranslatingTrait;

    /**
     * The name of the root templates directory.
     *
     * @since [*next-version*]
     */
    const ROOT_DIR_NAME = 'feeds';

    /**
     * The name of the main template file to load.
     *
     * @since [*next-version*]
     */
    const MAIN_FILE_NAME = 'main.twig';

    /**
     * The pattern for the DB option key where template option values are stored.
     *
     * @since [*next-version*]
     */
    const DB_OPTIONS_KEY = 'wprss_templates/%s';

    /**
     * The template options.
     *
     * @since [*next-version*]
     *
     * @var DataSetInterface
     */
    protected $options;

    /**
     * Constructor.
     *
     * @since [*next-version*]
     */
    public function __construct()
    {
        $this->options = $this->createOptions();
    }

    /**
     * Retrieves the key for the database option that stores this template's option values.
     *
     * @since [*next-version*]
     *
     * @return string
     */
    protected function getDbOptionKey()
    {
        return sprintf(static::DB_OPTIONS_KEY, $this->getId());
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function createOptions()
    {
        return new WpOptionsDataSet($this->getDbOptionKey(), [], $this->getDefaultOptions());
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function render($ctx = null)
    {
        $argCtx = ($ctx === null) ? [] : $this->_normalizeArray($ctx);
        $prepCtx = $this->prepareContext($argCtx);

        try {
            return wprss_render_template($this->getTemplatePath(), $prepCtx);
        } catch (Twig_Error_Loader $loaderEx) {
            throw $this->_createTemplateRenderException(
                __('Could not load template', WPRSS_TEXT_DOMAIN), null, $loaderEx, $this, $argCtx
            );
        } catch (Twig_Error_Syntax $synEx) {
            throw $this->_createTemplateRenderException(
                sprintf(
                    __('Syntax error in template at line %d: %s', WPRSS_TEXT_DOMAIN),
                    $synEx->getTemplateLine(),
                    $synEx->getMessage()
                ),
                null, $synEx, $this, $argCtx
            );
        } catch (Exception $ex) {
            throw $this->_createTemplateRenderException(
                __('An error occurred while rendering the twig template', WPRSS_TEXT_DOMAIN), null, $ex, $this, $prepCtx
            );
        }
    }

    /**
     * Retrieves the path to the template file.
     *
     * @since [*next-version*]
     *
     * @return string The path to the template file, relative to a registered WPRA template path.
     */
    protected function getTemplatePath()
    {
        return implode(DIRECTORY_SEPARATOR, [static::ROOT_DIR_NAME, $this->getKey(), static::MAIN_FILE_NAME]);
    }

    /**
     * Prepares a render context before passing it to the template.
     *
     * @since [*next-version*]
     *
     * @param array $ctx The render context.
     *
     * @return array The prepared the context.
     */
    protected function prepareContext(array $ctx)
    {
        $pCtx = $this->parseArgsWithSchema($ctx, $this->getFullContextSchema());

        return [
            'template' => [
                'type' => $this->getKey(),
                'path' => $this->getTemplatePath(),
            ],
            'items' => $this->getFeedItemsToRender($pCtx),
            'options' => $this->createContextDataSet($pCtx),
        ];
    }

    /**
     * Creates the data set instance for a given template render context.
     *
     * @since [*next-version*]
     *
     * @param array $ctx The render context.
     *
     * @return DataSetInterface The created data set instance.
     */
    protected function createContextDataSet(array $ctx)
    {
        return new ArrayDataSet($ctx, [], $this->getOptions());
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
     * Retrieves the full schema for the template context.
     *
     * @see   ParseArgsWithSchemaCapableTrait::parseArgsWithSchema()
     *
     * @since [*next-version*]
     *
     * @return array
     */
    protected function getFullContextSchema()
    {
        $standard = $this->getStandardContextSchema();
        $extension = $this->getContextSchema();

        return array_merge($extension, $standard);
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
    protected function getStandardContextSchema()
    {
        // The standard schema for all WP RSS Aggregator templates
        return [
            'limit' => [
                'key' => 'query_max_num',
                'default' => wprss_get_general_setting('feed_limit'),
                'filter' => FILTER_VALIDATE_INT,
                'options' => ['min_range' => 1],
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
     * Retrieves the schema for template-specific context options.
     *
     * @see   ParseArgsWithSchemaCapableTrait::parseArgsWithSchema()
     *
     * @since [*next-version*]
     *
     * @return array
     */
    abstract protected function getContextSchema();

    /**
     * The data set for the default template options.
     *
     * @since [*next-version*]
     *
     * @return DataSetInterface|null
     */
    abstract protected function getDefaultOptions();
}
