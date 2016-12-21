<?php

namespace Aventura\Wprss\Core\Block\Html;

/**
 * Base functionality for a regular tag.
 * A regular tag is one that requires a closing tag, e.g. is not self-closing.
 */
class AbstractRegularTag extends AbstractTag
{

    /**
     * Renders the tag HTML.
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
