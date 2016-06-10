<?php

namespace Aventura\Wprss\Core\Block\Html;

/**
 * Something that can be an HTML tag.
 *
 * @since [*next-version*]
 */
interface TagInterface
{

    /**
     * Retrieve an array of attributes.
     *
     * Keys are attribute names, values are attribute values.
     *
     * @param array|bool If is a non-empty array, will return attribute values
     *  for attributes in that array; Otherwise, return all attributes.
     * @since [*next-version*]
     */
    public function getAttributes($attributes);

    /**
     * Set attributes.
     *
     * Will set attributes to those specified in this array. Existing values
     * remain.
     *
     * @since [*next-version*]
     */
    public function setAttributes($attributes);

    /**
     * Get content of the tag.
     *
     * @since [*next-version*]
     */
    public function getContent();

    /**
     * Set content of the tag.
     *
     * @since [*next-version*]
     */
    public function setContent($content);

    /**
     * Get name of the tag.
     *
     * @since [*next-version*]
     */
    public function getTagName();
}
