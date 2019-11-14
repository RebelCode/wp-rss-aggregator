<?php

namespace RebelCode\Wpra\Core\Handlers;

use Dhii\Output\TemplateInterface;
use RebelCode\Wpra\Core\Data\Collections\CollectionInterface;

/**
 * A generic handler for rendering the contents of a WordPress meta box.
 *
 * @since [*next-version*]
 */
class RenderMetaBoxTemplateHandler
{
    /**
     * @since [*next-version*]
     *
     * @var TemplateInterface
     */
    protected $template;

    /**
     * @since [*next-version*]
     *
     * @var CollectionInterface
     */
    protected $collection;

    /**
     * @since [*next-version*]
     *
     * @var string
     */
    protected $entityKey;

    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param TemplateInterface   $template
     * @param CollectionInterface $collection
     * @param string              $entityKey
     */
    public function __construct(TemplateInterface $template, CollectionInterface $collection, $entityKey = 'entity')
    {
        $this->template = $template;
        $this->collection = $collection;
        $this->entityKey = $entityKey;
    }

    /**
     * @inheritdoc
     *
     * @since [*next-version*]
     */
    public function __invoke($post, $args = [])
    {
        $entity = isset($this->collection[$post->ID])
            ? $this->collection[$post->ID]
            : [];

        echo $this->template->render([
            'args' => [],
            'post' => $post,
            $this->entityKey => $entity,
        ]);
    }
}
