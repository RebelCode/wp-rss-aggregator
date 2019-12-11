<?php

namespace RebelCode\Wpra\Core\Handlers;

use Dhii\Output\TemplateInterface;
use RebelCode\Wpra\Core\Data\Collections\CollectionInterface;

/**
 * A generic handler for rendering the contents of a WordPress meta box.
 *
 * @since 4.17
 */
class RenderMetaBoxTemplateHandler
{
    /**
     * @since 4.17
     *
     * @var TemplateInterface
     */
    protected $template;

    /**
     * @since 4.17
     *
     * @var CollectionInterface
     */
    protected $collection;

    /**
     * @since 4.17
     *
     * @var string
     */
    protected $entityKey;

    /**
     * Constructor.
     *
     * @since 4.17
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
     * @since 4.17
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
