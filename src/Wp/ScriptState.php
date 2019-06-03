<?php

namespace RebelCode\Wpra\Core\Wp;

use Dhii\Output\RendererInterface;

/**
 * Class for providing state to UI application. This implementation wraps
 * `wp_localize_script` function.
 *
 * @since [*next-version*]
 */
class ScriptState implements RendererInterface
{
    /**
     * Unique script handle.
     *
     * @since [*next-version*]
     *
     * @var string
     */
    protected $handle;

    /**
     * The name of variable.
     *
     * @since [*next-version*]
     *
     * @var string
     */
    protected $name;

    /**
     * State to localize. If function is passed, it will be called during rendering.
     * This is useful when state relies on WP functions which can be called after
     * certain moment of tim.
     *
     * @since [*next-version*]
     *
     * @var array|callable
     */
    protected $state;

    /**
     * ScriptState constructor.
     *
     * @param $handle
     * @param $name
     * @param $state
     */
    public function __construct($handle, $name, $state)
    {
        $this->handle = $handle;
        $this->name = $name;
        $this->state = $state;
    }

    /**
     * Sets handle name for the script. Useful when different consumers want
     * to use different handle names.
     *
     * @since [*next-version*]
     *
     * @param string $handle
     *
     * @return $this
     */
    public function setHandle($handle)
    {
        $this->handle = $handle;
        return $this;
    }

    /**
     * Render script state.
     *
     * @since [*next-version*]
     *
     * @return string
     */
    public function render()
    {
        $state = is_callable($this->state) ? call_user_func($this->state) : $this->state;
        wp_localize_script($this->handle, $this->name, $state);
    }
}
