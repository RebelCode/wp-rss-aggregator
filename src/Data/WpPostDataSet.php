<?php

namespace RebelCode\Wpra\Core\Data;

use OutOfRangeException;
use WP_Post;

/**
 * An implementation of a data set that acts as a wrapper for a WordPress post.
 *
 * @since [*next-version*]
 */
class WpPostDataSet extends ArrayDataSet
{
    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param int|string|WP_Post $post The WordPress post instance or post ID.
     */
    public function __construct($post)
    {
        $post = ($post instanceof WP_Post) ? $post : get_post($post);

        if (!($post instanceof WP_Post)) {
            throw new OutOfRangeException(
                sprintf(__('Post with ID %s does not exist', WPRSS_TEXT_DOMAIN), $post)
            );
        }

        parent::__construct($post->to_array());
    }

    /**
     * Utility method for using standard WordPress Post aliases, with optionally additional aliases.
     *
     * @since [*next-version*]
     *
     * @param array $extra Optional additional aliases.
     *
     * @return array The standard WordPress Post aliases, together with the $extra aliases if any were given.
     */
    public static function useStandardAliases(array $extra = [])
    {
        return array_merge([
            'id' => 'ID',
            'slug' => 'post_name',
            'type' => 'post_type',
            'title' => 'post_title',
            'content' => 'post_content',
            'filtered_content' => 'post_content_filtered',
            'excerpt' => 'post_excerpt',
            'status' => 'post_status',
            'author' => 'post_author',
            'publish_date' => 'post_date',
            'publish_date_gmt' => 'post_date_gmt',
            'modified_date' => 'post_modified',
            'modified_date_gmt' => 'post_modified_gmt',
            'parent' => 'post_parent',
            'categories' => 'post_category',
            'tags' => 'tags_input',
            'password' => 'post_password',
            'mime_type' => 'post_mime_type',
        ], $extra);
    }
}
