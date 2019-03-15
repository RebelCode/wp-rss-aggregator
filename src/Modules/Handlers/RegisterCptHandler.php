<?php

namespace RebelCode\Wpra\Core\Modules\Handlers;

/**
 * A handler for registering custom post types.
 *
 * @since [*next-version*]
 */
class RegisterCptHandler
{
    /**
     * The CPT name.
     *
     * @since [*next-version*]
     *
     * @var string
     */
    protected $cptName;

    /**
     * The CPT args.
     *
     * @since [*next-version*]
     *
     * @var array
     */
    protected $cptArgs;

    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param string $cptName The CPT name.
     * @param array  $cptArgs The CPT args.
     */
    public function __construct($cptName, array $cptArgs)
    {
        $this->cptName = $cptName;
        $this->cptArgs = $cptArgs;
    }

    /**
     * @since [*next-version*]
     */
    public function __invoke()
    {
        register_post_type($this->cptName, $this->cptArgs);
    }
}
