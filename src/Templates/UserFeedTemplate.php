<?php

namespace RebelCode\Wpra\Core\Templates;

use RebelCode\Wpra\Core\Data\WpOptionsDataSet;

/**
 * An extension of the list view template that allows customization of the ID, name, options and template file.
 *
 * This implementation is intended to be used for generically instantiating user-custom templates.
 *
 * @since [*next-version*]
 */
class UserFeedTemplate extends ListViewTemplate
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
    protected $template;

    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param string      $id       The template's ID.
     * @param string      $name     The template's name.
     * @param string|null $template The path to the twig template file.
     */
    public function __construct($id, $name, $template = null)
    {
        parent::__construct();

        $this->id = $id;
        $this->name = $name;
        $this->template = $template;
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
    protected function createOptions()
    {
        return AbstractFeedTemplate::createOptions();
    }
}
