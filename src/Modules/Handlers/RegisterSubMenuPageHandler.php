<?php

namespace RebelCode\Wpra\Core\Modules\Handlers;

use stdClass;

/**
 * A generic handler for registering a submenu page in WordPress.
 *
 * @since [*next-version*]
 */
class RegisterSubMenuPageHandler
{
    /**
     * The submenu page info.
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
     * @param array|stdClass $info The submenu page info, containing the keys:
     *                             - parent
     *                             - slug
     *                             - page_title
     *                             - menu_label
     *                             - capability
     *                             - callback
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
        add_submenu_page(
            $this->info['parent'],
            $this->info['page_title'],
            $this->info['menu_label'],
            $this->info['capability'],
            $this->info['slug'],
            $this->info['callback']
        );
    }
}
