<?php

namespace RebelCode\Wpra\Core\Handlers\Images;

use RebelCode\Wpra\Core\Data\DataSetInterface;

/**
 * The handler that renders the contents of the images column in the feed items page.
 *
 * @since [*next-version*]
 */
class RenderItemsImageColumnHandler
{
    /**
     * @since [*next-version*]
     *
     * @var DataSetInterface
     */
    protected $feedItems;

    /**
     * @since [*next-version*]
     *
     * @var string
     */
    protected $column;

    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param DataSetInterface $feedItems
     * @param string           $column
     */
    public function __construct(DataSetInterface $feedItems, $column)
    {
        $this->feedItems = $feedItems;
        $this->column = $column;
    }

    /**
     * @inheritdoc
     *
     * @since [*next-version*]
     */
    public function __invoke($column, $postId)
    {
        if (!isset($this->feedItems[$postId]) || $column !== $this->column) {
            return;
        }

        $feedItem = $this->feedItems[$postId];
        $image = isset($feedItem['ft_image_url'])
            ? $feedItem['ft_image_url']
            : null;

        if (empty($image)) {
            return;
        }

        printf(
            '<div><img src="%1$s" alt="%2$s" title="%2$s" class="wpra-item-ft-image" /></div>',
            $feedItem['ft_image_url'],
            __('Feed item image', 'wprss')
        );
    }
}
