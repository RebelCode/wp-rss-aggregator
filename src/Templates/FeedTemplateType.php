<?php

namespace RebelCode\Wpra\Core\Templates;

use RebelCode\Wpra\Core\Data\DataSetInterface;
use RebelCode\Wpra\Core\Templates\Types\ListTemplateType;

/**
 * A fully generic WP RSS Aggregator feed template type, based on the core list template type.
 *
 * @since [*next-version*]
 */
class FeedTemplateType extends ListTemplateType
{
    /**
     * The template's ID.
     *
     * @since [*next-version*]
     *
     * @var string
     */
    protected $id;

    /**
     * The template's name.
     *
     * @since [*next-version*]
     *
     * @var string
     */
    protected $name;

    /**
     * The path to the twig template file, relative from a registered Twig directory.
     *
     * @since [*next-version*]
     *
     * @var string|null
     */
    protected $path;

    /**
     * The default options.
     *
     * @since [*next-version*]
     *
     * @var DataSetInterface
     */
    protected $defaults;

    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param string           $id       The template's ID.
     * @param string           $name     The template's name.
     * @param string           $path     The path to the twig template file.
     * @param DataSetInterface $defaults The default template options.
     */
    public function __construct($id, $name, $path, DataSetInterface $defaults)
    {
        $this->id = $id;
        $this->name = $name;
        $this->path = $path;
        $this->defaults = $defaults;
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function getKey()
    {
        return $this->id;
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function getName()
    {
        $this->name;
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function getTemplateDir()
    {
        return dirname($this->path) . DIRECTORY_SEPARATOR;
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function getTemplatePath()
    {
        return $this->path;
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function getOptions()
    {
        $schema = [];

        foreach ($this->defaults as $key => $value) {
            $schema[$key] = [
                'filter' => FILTER_DEFAULT,
            ];
            if (isset($this->defaults[$key])) {
                $schema[$key]['default'] = $this->defaults[$key];
            }
        }

        return $schema;
    }
}
