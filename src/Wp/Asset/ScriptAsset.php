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
     * Function to execute after the script was enqueued.
     *
     * @since [*next-version*]
     *
     * @var null|callable
     */
    protected $afterEnqueue;

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
     * Set the callback to execute right after the script was enqueued.
     *
     * @since [*next-version*]
     *
     * @param callable $callback
     *
     * @return $this
     */
    public function setAfterEnqueue($callback)
    {
        $this->afterEnqueue = $callback;
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

        if ($this->afterEnqueue && is_callable($this->afterEnqueue)) {
            call_user_func($this->afterEnqueue);
        }
    }
}
