<?php
namespace Aventura\Wprss\Core\Block\Html;

/**
 * Represents an HTML anchor (link).
 *
 * @since 4.9
 */
class Anchor extends AbstractTag
{
    const TAG_NAME = 'a';

    /**
     * Renders the anchor HTML.
     *
     * @since 4.9
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
