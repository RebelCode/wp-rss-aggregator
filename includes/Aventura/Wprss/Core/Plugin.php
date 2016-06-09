<?php

namespace Aventura\Wprss\Core;

/**
 * A dummy plugin for the Core plugin.
 *
 * @since 4.8.1
 * @todo Create real Core plugin in the Core plugin.
 */
class Plugin extends Plugin\PluginAbstract
{
    const CODE = 'wprss';
    const VERSION = WPRSS_VERSION;

    /**
     * Returns a list of meta members for this plugin.
     *
     * Raises plugin-specific event `plugin_row_meta`.
     *
     * @since [*next-version*]
     * @return array An array of meta members for this plugin, by key.
     */
    public function getPluginRowMeta()
    {
        return $this->event('plugin_row_meta', array('links' => array(
            'getting_started'           => $this->getAnchor(array(
                'target'                    => '_blank',
                'href'                      => 'https://docs.wprssaggregator.com/category/getting-started/'
            ), $this->__('Getting Started')),
            'extensions'                => $this->getAnchor(array(
                'target'                    => '_blank',
                'href'                      => 'https://www.wprssaggregator.com/extensions/'
            ), $this->__('Extensions'))
        )))->getData('links');
    }

    /**
     * Get a new anchor block instance.
     *
     * @since [*next-version*]
     * @param array|string $attributes Keys are attribute names; values are attribute
     *  values. These will become the attributes of the anchor tag.
     *  If string, this will be treated as the value of the 'href' attribute.
     * @param string $content Content for the anchor tag. Usually text.
     * @return Block\Html\TagInterface An anchor block instance.
     */
    public function getAnchor($attributes = array(), $content = '')
    {
        if (is_string($attributes)) {
            $attributes = array('href' => $attributes);
        }
        $block = $this->createAnchorBlock()
            ->setAttributes($attributes)
            ->setContent($content);

        return $block;
    }

    /**
     * Anchor block factory.
     *
     * @since [*next-version*]
     * @return Aventura\Wprss\Core\Block\Html\TagInterface
     */
    public function createAnchorBlock()
    {
        return new \Aventura\Wprss\Core\Block\Html\Anchor();
    }
}
