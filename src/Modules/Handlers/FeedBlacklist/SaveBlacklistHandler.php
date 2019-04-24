<?php

namespace RebelCode\Wpra\Core\Modules\Handlers\FeedBlacklist;

use RebelCode\Wpra\Core\Modules\Handlers\AbstractSavePostHandler;
use WP_Post;

/**
 * The handler for saving custom feed blacklist posts.
 *
 * @since 4.13
 */
class SaveBlacklistHandler extends AbstractSavePostHandler
{
    /**
     * {@inheritdoc}
     *
     * @since 4.13
     */
    protected function savePost(WP_Post $post, $meta, $autoDraft)
    {
        // Stop if the post is an auto draft
        if ($autoDraft) {
            return [];
        }

        // Make sure blacklist items are never draft
        if ($post->post_status === 'draft') {
            wp_update_post([
                'ID' => $post->ID,
                'post_status' => 'publish',
            ]);
        }

        // Check if the URL is empty
        if (empty($meta['wprss_permalink'])) {
            return [
                __('The blacklist item URL is empty. Please enter the URL to blacklist.', 'wprss')
            ];
        }

        // Empty titles default to the URL
        if (empty($post->post_title)) {
            wp_update_post([
                'ID' => $post->ID,
                'post_title' => $meta['wprss_permalink'],
            ]);
        }

        return [];
    }

    /**
     * {@inheritdoc}
     *
     * @since 4.13
     */
    protected function getMetaSchema()
    {
        return [
            'wprss_permalink' => [
                'filter' => FILTER_VALIDATE_URL,
            ],
        ];
    }
}
