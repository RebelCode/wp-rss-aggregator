<?php

namespace RebelCode\Wpra\Core\Handlers\Images;

use Exception;
use RebelCode\Wpra\Core\Data\DataSetInterface;

/**
 * The handler that deletes attached images for an imported item.
 *
 * @since 4.14
 */
class DeleteImagesHandler
{
    /**
     * @since 4.14
     *
     * @var DataSetInterface
     */
    protected $importedItems;

    /**
     * Constructor.
     *
     * @since 4.14
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
     * @since 4.14
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
