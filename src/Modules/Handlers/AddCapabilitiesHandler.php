<?php

namespace RebelCode\Wpra\Core\Modules\Handlers;

use stdClass;
use Traversable;
use WP_Roles;

/**
 * A handler for adding capabilities to user roles.
 *
 * @since [*next-version*]
 */
class AddCapabilitiesHandler
{
    /**
     * The WordPress role manager instance.
     *
     * @since [*next-version*]
     *
     * @var WP_Roles
     */
    protected $wpRoles;

    /**
     * The list of user roles to which the capabilities are added.
     *
     * @since [*next-version*]
     *
     * @var array|stdClass|Traversable
     */
    protected $roles;

    /**
     * The list of capabilities to add.
     *
     * @since [*next-version*]
     *
     * @var array|stdClass|Traversable
     */
    protected $capabilities;

    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param WP_Roles                   $wpRoles      The WordPress role manager instance.
     * @param array|stdClass|Traversable $roles        The list of user roles to which the capabilities are added.
     * @param array|stdClass|Traversable $capabilities The list of capabilities to add.
     */
    public function __construct(WP_Roles $wpRoles, $roles, $capabilities)
    {
        $this->wpRoles = $wpRoles;
        $this->roles = $roles;
        $this->capabilities = $capabilities;
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function __invoke()
    {
        foreach ($this->roles as $role) {
            foreach ($this->capabilities as $capability) {
                $this->wpRoles->add_cap($role, $capability);
            }
        }
    }
}
