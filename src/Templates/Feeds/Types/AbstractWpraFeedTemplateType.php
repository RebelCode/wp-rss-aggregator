<?php

namespace RebelCode\Wpra\Core\Templates\Feeds\Types;

use Dhii\Output\TemplateInterface;
use RebelCode\Wpra\Core\Data\ArrayDataSet;
use RebelCode\Wpra\Core\Data\Collections\CollectionInterface;
use RebelCode\Wpra\Core\Data\DataSetInterface;
use RebelCode\Wpra\Core\Util\ParseArgsWithSchemaCapableTrait;
use RebelCode\Wpra\Core\Util\SanitizeIdCommaListCapableTrait;

/**
 * Abstract implementation of a standard WP RSS Aggregator feed template type.
 *
 * This partial implementation sets the standard for most of the templates provided by WP RSS Aggregator.
 * This template type has a set of core standard options pertaining to which feed items to render and pagination.
 *
 * By default, this implementation loads templates from a template collection using keys that follow the pattern:
 * `feeds/<key>/main.twig`. The key represents the path to a Twig template file relative to the WP RSS Aggregator
 * templates directory, and the collection is expected to return a {@link TemplateInterface} instance for that twig
 * template file.
 *
 * Twig template files will have access to an "options" variable which contains the template's options, an "items"
 * variable which contains the items to be rendered and a "self" variable containing information about the template.
 * The "self.dir" variable may be useful for requiring other template files located in the same directory as the
 * main template file.
 *
 * @since [*next-version*]
 */
abstract class AbstractWpraFeedTemplateType extends AbstractFeedTemplateType
{
    /* @since [*next-version*] */
    use ParseArgsWithSchemaCapableTrait;

    /* @since [*next-version*] */
    use SanitizeIdCommaListCapableTrait;

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
     * The templates data set.
     *
     * @since [*next-version*]
     *
     * @var DataSetInterface
     */
    protected $templates;

    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param DataSetInterface    $templates The templates data set.
     * @param CollectionInterface $feedItems The feed items collection.
     */
    public function __construct(DataSetInterface $templates, CollectionInterface $feedItems)
    {
        parent::__construct($feedItems);

        $this->templates = $templates;
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
        return $this->templates[$this->getTemplatePath()];
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
     * {@inheritdoc}
     *
     * Overrides the parent method to process the standard WPRA options, filter the feed items collection and add the
     * template info to the context.
     *
     * @since [*next-version*]
     */
    protected function prepareContext($ctx)
    {
        // Parse the standard options
        $stdOpts = $this->parseArgsWithSchema($ctx, $this->getStandardOptions());
        // Filter the items and count them
        $items = $this->feedItems->filter($stdOpts['filters']);
        $count = $items->getCount();
        // Paginate the items
        $items = $items->filter($stdOpts['pagination']);
        // Calculate the total number of pages and items per page
        $perPage = empty($stdOpts['pagination']['num_items']) ? 0 : $stdOpts['pagination']['num_items'];
        $numPages = $perPage ? ceil($count / $perPage) : 0;
        $page = empty($stdOpts['pagination']['page']) ? 1 : $stdOpts['pagination']['page'];

        // Parse the template-type's own options
        $ttOpts = $this->parseArgsWithSchema($ctx, $this->getOptions());

        return [
            'items' => $items,
            'options' => $ttOpts,
            'pagination' => [
                'page' => $page,
                'total_num_items' => $count,
                'items_per_page' => $perPage,
                'num_pages' => $numPages,
            ],
            'self' => [
                'slug' => $stdOpts['template'],
                'type' => $this->getKey(),
                'path' => $this->getTemplatePath(),
                'dir' => $this->getTemplateDir(),
            ],
            'ctx' => base64_encode(json_encode($ctx))
        ];
    }

    /**
     * Retrieves the WPRA-standard template type options.
     *
     * @since [*next-version*]
     *
     * @return array
     */
    protected function getStandardOptions()
    {
        return [
            'template' => [
                'default' => '',
                'filter' => FILTER_DEFAULT,
            ],
            'source' => [
                'key' => 'filters/sources',
                'default' => [],
                'filter' => function ($value) {
                    return $this->sanitizeIdCommaList($value);
                },
            ],
            'sources' => [
                'key' => 'filters/sources',
                'filter' => function ($value, $args) {
                    return $this->sanitizeIdCommaList($value);
                },
            ],
            'exclude' => [
                'key' => 'filters/exclude',
                'default' => [],
                'filter' => function ($value) {
                    return $this->sanitizeIdCommaList($value);
                },
            ],
            'limit' => [
                'key' => 'pagination/num_items',
                'default' => wprss_get_general_setting('feed_limit'),
                'filter' => FILTER_VALIDATE_INT,
                'options' => ['min_range' => 1],
            ],
            'page' => [
                'key' => 'pagination/page',
                'default' => 1,
                'filter' => FILTER_VALIDATE_INT,
                'options' => ['min_range' => 1],
            ],
            'pagination' => [
                'key' => 'pagination/enabled',
                'filter' => FILTER_VALIDATE_BOOLEAN,
            ],
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
