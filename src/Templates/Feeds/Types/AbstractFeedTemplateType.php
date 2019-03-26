<?php

namespace RebelCode\Wpra\Core\Templates\Feeds\Types;

use ArrayAccess;
use Dhii\Exception\CreateInvalidArgumentExceptionCapableTrait;
use Dhii\I18n\StringTranslatingTrait;
use Dhii\Output\CreateTemplateRenderExceptionCapableTrait;
use Dhii\Output\TemplateInterface;
use Dhii\Util\Normalization\NormalizeArrayCapableTrait;
use Exception;
use RebelCode\Wpra\Core\Data\ArrayDataSet;
use RebelCode\Wpra\Core\Data\DataSetInterface;
use RebelCode\Wpra\Core\Util\ParseArgsWithSchemaCapableTrait;

/**
 * Abstract implementation of a standard WP RSS Aggregator feed template type.
 *
 * By default, this implementation loads twig template files according to the pattern `feeds/<key>/main.twig`, where
 * `<key>` is the key of the template. This can be changed by overriding the {@link getTemplatePath} method.
 *
 * Twig template files will have access to an "options" variable which contains the template's options, an "items"
 * variable which contains the items to be rendered and a "template" variable containing information about the template.
 * The "template.dir" variable may be useful for requiring other template files located in the same directory as the
 * main template file.
 *
 * @since [*next-version*]
 */
abstract class AbstractFeedTemplateType implements FeedTemplateTypeInterface
{
    /* @since [*next-version*] */
    use NormalizeArrayCapableTrait;

    /* @since [*next-version*] */
    use ParseArgsWithSchemaCapableTrait;

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
     * The templates collection.
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
     * @param DataSetInterface $collection The templates collection.
     */
    public function __construct(DataSetInterface $collection)
    {
        $this->collection = $collection;
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function render($ctx = null)
    {
        $argCtx = ($ctx === null) ? [] : $ctx;
        $prepCtx = $this->prepareContext($argCtx);

        $this->enqueueAssets();

        try {
            return $this->getTemplate()->render($prepCtx);
        } catch (Exception $ex) {
            throw $this->_createTemplateRenderException(
                __('An error occurred while rendering the twig template', WPRSS_TEXT_DOMAIN), null, $ex, $this, $prepCtx
            );
        }
    }

    /**
     * Retrieves the template to render.
     *
     * @since [*next-version*]
     *
     * @return TemplateInterface The template instance.
     */
    protected function getTemplate()
    {
        return $this->collection[$this->getTemplatePath()];
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
     * @param array|ArrayAccess $ctx The render context.
     *
     * @return array The prepared the context.
     */
    protected function prepareContext($ctx)
    {
        $model = $ctx['model'];
        $optCtx = isset($model['options']) ? $model['options'] : [];
        $opts = $this->parseArgsWithSchema($optCtx, $this->getOptions());

        $ctx['options'] = $this->createContextDataSet($opts);
        $ctx['self'] = [
            'type' => $this->getKey(),
            'path' => $this->getTemplatePath(),
            'dir' => $this->getTemplateDir(),
        ];

        return $ctx;
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
     * Retrieves the schema for template-specific options.
     *
     * @see   ParseArgsWithSchemaCapableTrait::parseArgsWithSchema()
     *
     * @since [*next-version*]
     *
     * @return array
     */
    abstract protected function getOptions();

    /**
     * Enqueues the assets required by this template type.
     *
     * @since [*next-version*]
     */
    abstract protected function enqueueAssets();
}
