<?php

namespace RebelCode\Wpra\Core\Data;

use OutOfRangeException;
use RuntimeException;
use WP_Error;
use WP_Post;

/**
 * An implementation of a data set that acts as a wrapper for a WordPress post.
 *
 * @since [*next-version*]
 */
class WpPostDataSet extends AliasingDataSet
{
    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param int|string|WP_Post $post  The WordPress post instance or post ID.
     * @param array              $aliases Optional post key aliases to use or override.
     */
    public function __construct($post, array $aliases = [])
    {
        $post = ($post instanceof WP_Post) ? $post : get_post($post);

        if (!($post instanceof WP_Post)) {
            throw new OutOfRangeException(
                sprintf(__('Post with ID %s does not exist', WPRSS_TEXT_DOMAIN), $post)
            );
        }

        $inner = new ArrayDataSet($post->to_array());

        parent::__construct($inner, $this->getWpPostAliases($aliases));
    }

    /**
     * {@inheritdoc}
     *
     * Checks if the key exists first, and throws an exception if not.
     *
     * @since [*next-version*]
     */
    protected function set($key, $value)
    {
        $iKey = $this->getInnerKey($key);

        if (!$this->inner->offsetExists($iKey) || $iKey === 'ID') {
            throw new OutOfRangeException(
                sprintf(__('Cannot modify WordPress post entry with key "%s"', 'wprss'), $iKey)
            );
        }

        $result = wp_update_post([
            'ID' =>$this['id'],
            $iKey => $value,
        ], true);

        if ($result instanceof WP_Error) {
            throw new RuntimeException($result->get_error_message(), $result->get_error_code());
        }

        $this->inner->offsetSet($iKey, $value);
    }

    /**
     * {@inheritdoc}
     *
     * Since data cannot be deleted from a WP post, the value is instead set to an empty string.
     *
     * @since [*next-version*]
     */
    protected function delete($key)
    {
        $this->set($key, '');
    }

    /**
     * Retrieves the standard WordPress Post aliases, with optionally additional aliases.
     *
     * @since [*next-version*]
     *
     * @param array $extra Optional additional aliases.
     *
     * @return array The standard WordPress Post aliases, together with the $extra aliases if any were given.
     */
    protected function getWpPostAliases(array $extra = [])
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
