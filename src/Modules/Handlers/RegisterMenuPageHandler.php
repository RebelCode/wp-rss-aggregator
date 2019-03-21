<?php

namespace RebelCode\Wpra\Core\Modules\Handlers;

use stdClass;

/**
 * A generic handler for registering a menu page in WordPress.
 *
 * @since [*next-version*]
 */
class RegisterMenuPageHandler
{
    /**
     * The menu page info.
     *
     * @since [*next-version*]
     *
     * @var array
     */
    protected $info;

    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param array|stdClass $info The menu page info, containing the keys:
     *                             - slug
     *                             - page_title
     *                             - menu_label
     *                             - capability
     *                             - callback
     *                             - icon (optional)
     *                             - position (optional)
     */
    public function __construct($info)
    {
        $this->info = (array) $info;
    }

    /**
     * @since [*next-version*]
     */
    public function __invoke()
    {
        add_menu_page(
            $this->info['page_title'],
            $this->info['menu_label'],
            $this->info['capability'],
            $this->info['slug'],
            $this->info['callback'],
            isset($this->info['icon']) ? $this->info['info'] : null,
            isset($this->info['position']) ? $this->info['position'] : null
        );
    }
}
