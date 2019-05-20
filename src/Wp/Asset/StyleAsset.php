<?php

namespace RebelCode\Wpra\Core\Wp\Asset;

/**
 * Class for enqueuing styles.
 *
 * @since [*next-version*]
 */
class StyleAsset extends AbstractAsset
{
    /**
     * The media for which this stylesheet has been defined. Accepts media types like 'all', 'print' and 'screen',
     * or media queries like '(orientation: portrait)' and '(max-width: 640px)'.
     *
     * @since [*next-version*]
     *
     * @var string
     */
    protected $media = 'all';

    /**
     * Set media property.
     *
     * @since [*next-version*]
     *
     * @param string $media
     *
     * @return $this
     */
    public function setMedia($media)
    {
        $this->media = $media;
        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function enqueue()
    {
        wp_enqueue_style($this->handle, $this->src, $this->dependencies, $this->version, $this->media);
    }
}
