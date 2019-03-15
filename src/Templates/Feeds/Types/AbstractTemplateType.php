<?php

namespace RebelCode\Wpra\Core\Templates\Feeds\Types;

use Dhii\Exception\CreateInvalidArgumentExceptionCapableTrait;
use Dhii\I18n\StringTranslatingTrait;
use Dhii\Output\CreateTemplateRenderExceptionCapableTrait;
use Dhii\Util\Normalization\NormalizeArrayCapableTrait;
use Exception;
use InvalidArgumentException;
use RebelCode\Wpra\Core\Data\ArrayDataSet;
use RebelCode\Wpra\Core\Data\DataSetInterface;
use RebelCode\Wpra\Core\Query\FeedItemsQueryIterator;
use RebelCode\Wpra\Core\Util\ParseArgsWithSchemaCapableTrait;
use RebelCode\Wpra\Core\Util\SanitizeIdCommaListCapableTrait;
use stdClass;
use Traversable;
use Twig\Error\LoaderError;
use Twig\Error\SyntaxError;

/**
 * Abstract implementation of a standard WP RSS Aggregator template.
 *
 * By default, this implementation loads twig template files according to the pattern `feeds/<key>/main.twig`, where
 * `<key>` is the key of the template. This can be changed by overriding the {@link getTemplatePath} method.
 *
 * For rendering, this implementation will construct a standard WP RSS Aggregator feed item query iterator, as an
 * instance of {@link FeedItemsQueryIterator}. This iterator's constructor arguments may be included in the render
 * context to specify which items to render, as "query_sources", "query_exclude", "query_max_num", "query_page" and
 * "query_factory".
 *
 * Twig template files will have access to an "options" variable which contains the template's options, an "items"
 * variable which contains the items to be rendered and a "template" variable containing information about the template.
 * The "template.dir" variable may be useful for requiring other template files located in the same directory as the
 * main template file.
 *
 * @since [*next-version*]
 */
abstract class AbstractTemplateType implements TemplateTypeInterface
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
        } catch (LoaderError $loaderEx) {
            throw $this->_createTemplateRenderException(
                __('Could not load template', WPRSS_TEXT_DOMAIN), null, $loaderEx, $this, $argCtx
            );
        } catch (SyntaxError $synEx) {
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
     * Retrieves the path to the template directory.
     *
     * @since [*next-version*]
     *
     * @return string The path to the template directory, relative to a registered WPRA template path.
     */
    protected function getTemplateDir()
    {
        return static::ROOT_DIR_NAME . DIRECTORY_SEPARATOR . $this->getKey() . DIRECTORY_SEPARATOR;
    }

    /**
     * Retrieves the path to the template main file.
     *
     * @since [*next-version*]
     *
     * @return string The path to the template main file, relative to a registered WPRA template path.
     */
    protected function getTemplatePath()
    {
        return $this->getTemplateDir() . static::MAIN_FILE_NAME;
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
                'dir'  => $this->getTemplateDir(),
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
        return new ArrayDataSet($ctx);
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
        $extension = $this->getOptions();

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
            'template' => [
                'default' => '',
                'filter' => FILTER_SANITIZE_STRING,
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
     * Retrieves the schema for template-specific options.
     *
     * @see   ParseArgsWithSchemaCapableTrait::parseArgsWithSchema()
     *
     * @since [*next-version*]
     *
     * @return array
     */
    abstract protected function getOptions();
}
