<?php

namespace RebelCode\Wpra\Core\Templates\Models;

use RebelCode\Wpra\Core\Data\AbstractDelegateDataSet;
use RebelCode\Wpra\Core\Util\NormalizeWpPostCapableTrait;
use WP_Post;

/**
 * A wrapper template class that can delegate to either a built in feed template or a normal post feed template.
 *
 * @since [*next-version*]
 */
class FeedTemplate extends AbstractDelegateDataSet
{
    /* @since [*next-version*] */
    use NormalizeWpPostCapableTrait;

    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param int|string|WP_Post $postOrId The WordPress post instance or its ID.
     */
    public function __construct($postOrId)
    {
        $post = $this->normalizeWpPost($postOrId);
        $type = get_post_meta($post->ID, 'wprss_template_type', true);

        $inner = ($type === '__built_in')
            ? new BuiltInFeedTemplate($post)
            : new WpPostFeedTemplate($post);

        parent::__construct($inner);
    }
}
