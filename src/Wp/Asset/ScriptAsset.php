<?php

namespace RebelCode\Wpra\Core\Wp\Asset;

/**
 * Class for enqueuing scripts.
 *
 * @since [*next-version*]
 */
class ScriptAsset extends AbstractAsset
{
    /**
     * Whether to enqueue the script before `</body>` instead of in the `<head>`.
     *
     * @since [*next-version*]
     *
     * @var bool
     */
    protected $inFooter = false;

    /**
     * Set the in footer property.
     *
     * @since [*next-version*]
     *
     * @param bool $inFooter
     *
     * @return $this
     */
    public function setInFooter($inFooter)
    {
        $this->inFooter = $inFooter;
        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function enqueue()
    {
        wp_enqueue_script($this->handle, $this->src, $this->dependencies, $this->version, $this->inFooter);
    }
}
