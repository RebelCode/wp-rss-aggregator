<?php

namespace RebelCode\Wpra\Core\Modules\Handlers\Images;

use Exception;
use RebelCode\Wpra\Core\Data\DataSetInterface;

/**
 * The handler that deletes attached images for an imported item.
 *
 * @since [*next-version*]
 */
class DeleteImagesHandler
{
    /**
     * @since [*next-version*]
     *
     * @var DataSetInterface
     */
    protected $importedItems;

    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param DataSetInterface $importedItems The imported items data set.
     */
    public function __construct(DataSetInterface $importedItems)
    {
        $this->importedItems = $importedItems;
    }

    /**
     * @inheritdoc
     *
     * @since [*next-version*]
     */
    public function __invoke($post_id)
    {
        try {
            $item = $this->importedItems[$post_id];
        } catch (Exception $e) {
            // Item is not imported by WPRA or does not exist
            return;
        }

        // Get the attachments
        $attachments = get_children([
            'post_parent' => $item['id'],
            'post_type' => 'attachment',
            'post_mime_type' => 'image',
        ]);

        // Delete them
        foreach ($attachments as $id => $attachment) {
            wp_delete_post($id);
        }
    }
}
