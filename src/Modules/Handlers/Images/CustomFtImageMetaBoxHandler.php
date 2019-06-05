<?php

namespace RebelCode\Wpra\Core\Modules\Handlers\Images;

/**
 * The handler that replaces the WordPress featured image meta box with a custom "Default featured image" variant.
 *
 * @since [*next-version*]
 */
class CustomFtImageMetaBoxHandler
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
        // Re-add it with custom title and callback
        add_meta_box(
            'postimagediv',
            __('Default Thumbnail', WPRSS_TEXT_DOMAIN),
            [$this, 'renderMetaBox'],
            'wprss_feed',
            'side',
            'default'
        );
    }

    /**
     * Renders the custom featured image metabox.
     *
     * @since [*next-version*]
     */
    public function renderMetaBox()
    {
        global $post;
        post_thumbnail_meta_box($post);
        ?>
        <script type="text/javascript">
            (function ($) {
                $(document).ready(function () {
                    $('#postimagediv > h2.hndle > span').text("<?php _e('Default Featured Image') ?>");
                });
            })(jQuery);
        </script>
        <?php
    }
}
