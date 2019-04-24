<?php

namespace RebelCode\Wpra\Core\Modules\Handlers\FeedTemplates;

use RebelCode\Wpra\Core\Data\Collections\CollectionInterface;
use RebelCode\Wpra\Core\Data\DataSetInterface;

/**
 * A handler that re-saves a template by iterating its data and re-setting it.
 *
 * @since 4.13
 */
class ReSaveTemplateHandler
{
    /**
     * The template collection.
     *
     * @since 4.13
     *
     * @var DataSetInterface
     */
    protected $collection;

    /**
     * The slug name of the template.
     *
     * @since 4.13
     *
     * @var string
     */
    protected $slug;

    /**
     * Constructor.
     *
     * @since 4.13
     *
     * @param DataSetInterface $collection The template collection.
     * @param string           $slug       The slug name of the template.
     */
    public function __construct(DataSetInterface $collection, $slug)
    {
        $this->collection = $collection;
        $this->slug = $slug;
    }

    /**
     * {@inheritdoc}
     *
     * @since 4.13
     */
    public function __invoke()
    {
        $template = $this->collection[$this->slug];

        foreach ($template as $key => $value) {
            $template[$key] = $value;
        }
    }
}
