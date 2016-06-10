<?php
namespace Aventura\Wprss\Core\Block\Html;

/**
 * Represents an HTML anchor (link).
 *
 * @since [*next-version*]
 */
class Anchor extends AbstractTag
{
    const TAG_NAME = 'a';

    /**
     * Renders the anchor HTML.
     *
     * @since [*next-version*]
     */
    public function getOutput()
    {
        $attributes = $this->getAttributes();
        $attributes = count($attributes)
                ? ' '.static::getAttributesStringFromArray($attributes)
                : '';
        $content = $this->getContent();
        $tagName = $this->getTagName();

        return sprintf('<%1$s%2$s>%3$s</%1$s>', $tagName, $attributes, $content);
    }
}
