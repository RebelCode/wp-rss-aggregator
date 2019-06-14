<?php

namespace RebelCode\Wpra\Core\Handlers;

use WP_Screen;

/**
 * A generic handler for registering WordPress meta boxes.
 *
 * @since [*next-version*]
 */
class RegisterMetaBoxHandler
{
    /**
     * @since [*next-version*]
     */
    const CONTEXT_NORMAL = 'normal';

    /**
     * @since [*next-version*]
     */
    const CONTEXT_ADVANCED = 'advanced';

    /**
     * @since [*next-version*]
     */
    const CONTEXT_SIDE = 'side';

    /**
     * @since [*next-version*]
     */
    const PRIORITY_DEFAULT = 'default';

    /**
     * @since [*next-version*]
     */
    const PRIORITY_LOW = 'low';

    /**
     * @since [*next-version*]
     */
    const PRIORITY_HIGH = 'high';

    /**
     * @since [*next-version*]
     *
     * @var string
     */
    protected $id;

    /**
     * @since [*next-version*]
     *
     * @var string
     */
    protected $title;

    /**
     * @since [*next-version*]
     *
     * @var callable
     */
    protected $callback;

    /**
     * @since [*next-version*]
     *
     * @var string
     */
    protected $screen;

    /**
     * @since [*next-version*]
     *
     * @var string|array|WP_Screen
     */
    protected $context;

    /**
     * @since [*next-version*]
     *
     * @var string
     */
    protected $priority;

    /**
     * @since [*next-version*]
     *
     * @var array
     */
    protected $args;

    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param string                 $id       The meta box ID.
     * @param string                 $title    The title of the meta box.
     * @param callable               $callback The callback that renders the contents of the meta box.
     * @param string|array|WP_Screen $screen   The screen(s) on which to add the meta box.
     * @param string                 $context  The meta box context. See the `CONTEXT_*` constants in this class.
     * @param string                 $priority The meta box priority. See the `PRIORITY_*` constants in this class.
     * @param array                  $args     Additional arguments to pass to the render callback.
     */
    public function __construct(
        $id,
        $title,
        callable $callback,
        $screen,
        $context = self::CONTEXT_NORMAL,
        $priority = self::PRIORITY_DEFAULT,
        array $args = []
    ) {
        $this->id = $id;
        $this->title = $title;
        $this->callback = $callback;
        $this->screen = $screen;
        $this->context = $context;
        $this->priority = $priority;
        $this->args = $args;
    }

    /**
     * @inheritdoc
     *
     * @since [*next-version*]
     */
    public function __invoke()
    {
        add_meta_box(
            $this->id,
            $this->title,
            $this->callback,
            $this->screen,
            $this->context,
            $this->priority,
            $this->args
        );
    }
}
