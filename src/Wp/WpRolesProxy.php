<?php

namespace RebelCode\Wpra\Core\Wp;

use WP_Roles;

/**
 * A proxy class for the {@link WP_Roles} class, that lazily fetches the global `$wp_roles` instance.
 *
 * This implementation is useful for declaring as a service, allowing other classes to be injected with it at load time,
 * since the original `$wp_roles` instance is `null` until the `admin_init` hook, which breaks parameter signatures
 * that are typed with `WP_Roles`.
 *
 * @since [*next-version*]
 */
class WpRolesProxy extends WP_Roles
{
    /**
     * Constructor.
     *
     * @since [*next-version*]
     */
    public function __construct()
    {
    }

    public function __get($name)
    {
        global $wp_roles;

        if (is_object($wp_roles)) {
            return $wp_roles->$name;
        }

        return null;
    }

    public function __set($name, $value)
    {
        global $wp_roles;

        if (is_object($wp_roles)) {
            $wp_roles->$name = $value;
        }
    }

    public function __call($name, $arguments)
    {
        global $wp_roles;

        if (is_object($wp_roles)) {
            call_user_func_array([$wp_roles, $name], $arguments);
        }
    }
}
