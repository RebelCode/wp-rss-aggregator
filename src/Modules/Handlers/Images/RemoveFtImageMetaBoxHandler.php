<?php

namespace RebelCode\Wpra\Core\Modules\Handlers\Images;

/**
 * The handler that removes the WordPress featured image meta box.
 *
 * @since [*next-version*]
 */
class RemoveFtImageMetaBoxHandler
{
    /**
     * @inheritdoc
     *
     * @since [*next-version*]
     */
    public function __invoke()
    {
        // Removes the 'Featured Image' meta box
        remove_meta_box('postimagediv', 'wprss_feed', 'side');
    }
}
