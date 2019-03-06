<?php

namespace RebelCode\Wpra\Core\Data;

use ArrayIterator;
use OutOfRangeException;
use WP_Post;

/**
 * An implementation of a data set that acts as a wrapper for a WordPress post.
 *
 * @since [*next-version*]
 */
class WpPostDataSet extends AbstractInheritingDataSet
{
    /**
     * The WordPress post instance.
     *
     * @since [*next-version*]
     *
     * @var WP_Post
     */
    protected $post;

    /**
     * Optional prefix for meta keys.
     *
     * @since [*next-version*]
     *
     * @var string
     */
    protected $metaPrefix;

    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param int|string|WP_Post    $post       The WordPress post instance or post ID.
     * @param string                $metaPrefix Optional prefix for meta keys.
     * @param array                 $aliases    A mapping of input keys to real meta keys.
     * @param DataSetInterface|null $parent     Optional parent data set to inherit from.
     */
    public function __construct($post, $metaPrefix, $aliases = [], DataSetInterface $parent = null)
    {
        parent::__construct($aliases, $parent);

        $this->post = ($post instanceof WP_Post) ? $post : get_post($post);

        if (!($this->post instanceof WP_Post)) {
            throw new OutOfRangeException(
                sprintf(__('Post with ID %s does not exist', WPRSS_TEXT_DOMAIN), $post)
            );
        }

        $this->metaPrefix = $metaPrefix;
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function getIterator()
    {
        $array = [];
        foreach ($this->post->to_array() as $key => $value) {
            $array[$this->reverseAlias($key)] = $value;
        }

        foreach (get_post_meta($this->post->ID, '') as $key => $value) {
            // Reverse alias the key
            $aliasKey = $this->reverseAlias($key);
            // Unpack meta arrays if they are "single" values
            $realValue = (is_array($value) && count($value) === 1) ? $value[0] : $value;
            // Add to the final array
            $array[$aliasKey] = $realValue;
        }

        return new ArrayIterator($array);
    }

    /**
     * {@inheritdoc}
     *
     * Additionally adds the meta prefix to keys.
     *
     * @since [*next-version*]
     */
    protected function aliasKey($key)
    {
        return $this->metaPrefix . parent::aliasKey($key);
    }

    /**
     * {@inheritdoc}
     *
     * Additionally strips the meta prefix from the alias before reversing the alias.
     *
     * @since [*next-version*]
     */
    protected function reverseAlias($alias)
    {
        $key = (!empty($this->metaPrefix) && strpos($alias, $this->metaPrefix) === 0)
            ? substr($alias, strlen($this->metaPrefix))
            : $alias;

        return parent::reverseAlias($key);
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function get($key)
    {
        return $this->post->{$key};
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function has($key)
    {
        return property_exists($this->post, $key) || metadata_exists('post', $this->post->ID, $key);
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function set($key, $value)
    {
        if (property_exists($this->post, $key)) {
            wp_update_post([
                'ID' => $this->post->ID,
                $key => $value,
            ]);

            return;
        }

        update_post_meta($this->post->ID, $key, $value);
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function delete($key)
    {
        if (!property_exists($this->post, $key)) {
            delete_post_meta($this->post->ID, $key);
        }
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
