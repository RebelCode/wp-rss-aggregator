<?php

namespace RebelCode\Wpra\Core\Modules\Handlers;

use stdClass;

/**
 * A generic handler for registering a submenu page in WordPress.
 *
 * @since 4.13
 */
class RegisterSubMenuPageHandler
{
    /**
     * The submenu page info.
     *
     * @since 4.13
     *
     * @var array
     */
    protected $info;

    /**
     * Constructor.
     *
     * @since 4.13
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
     * @since 4.13
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
