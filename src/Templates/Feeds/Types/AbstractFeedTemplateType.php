<?php

namespace RebelCode\Wpra\Core\Templates\Feeds\Types;

use ArrayAccess;
use Dhii\Output\Exception\TemplateRenderException;
use Dhii\Output\TemplateInterface;
use Exception;
use InvalidArgumentException;
use RebelCode\Wpra\Core\Data\Collections\CollectionInterface;
use RebelCode\Wpra\Core\Util\Normalize;
use RebelCode\Wpra\Core\Util\ParseArgsWithSchemaCapableTrait;

/**
 * Abstract implementation of a feed template type.
 *
 * This partial implementation lacks two main components:
 * - Asset enqueueing
 * - The actual {@link TemplateInterface} to render.
 *
 * @since 4.13
 */
abstract class AbstractFeedTemplateType implements FeedTemplateTypeInterface
{
    /* @since 4.13 */
    use ParseArgsWithSchemaCapableTrait;

    /**
     * {@inheritdoc}
     *
     * @since 4.13
     */
    public function render($ctx = null)
    {
        $argCtx = ($ctx === null) ? [] : Normalize::toArray($ctx);
        $prepCtx = $this->prepareContext($argCtx);

        $this->enqueueAssets();

        try {
            return $this->getTemplate()->render($prepCtx);
        } catch (Exception $ex) {
            throw new TemplateRenderException($ex->getMessage(), null, $ex, $this, $prepCtx);
        }
    }

    /**
     * Prepares a render context before passing it to the template.
     *
     * @since 4.13
     *
     * @param array|ArrayAccess $ctx The render context.
     *
     * @return array The prepared the context.
     */
    protected function prepareContext($ctx)
    {
        return $this->parseArgsWithSchema($ctx, [
            'options' => [
                'default' => [],
            ],
            'items' => [
                'default' => [],
                'filter' => function ($items) {
                    if ($items instanceof CollectionInterface) {
                        return $items;
                    }

                    throw new InvalidArgumentException(__('Items is not a collection instance'));
                },
            ],
        ]);
    }

    /**
     * Retrieves the template to render.
     *
     * @since 4.13
     *
     * @return TemplateInterface The template instance.
     */
    abstract protected function getTemplate();

    /**
     * Enqueues the assets required by this template type.
     *
     * @since 4.13
     */
    abstract protected function enqueueAssets();
}
