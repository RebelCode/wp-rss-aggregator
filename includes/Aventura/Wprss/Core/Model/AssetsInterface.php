<?php

namespace Aventura\Wprss\Core\Model;

/**
 * Something that can be used as an assets controller.
 *
 * @since [*next-version*]
 */
interface AssetsInterface
{
    /**
     * Enqueues the styles for the front-end.
     *
     * @since [*next-version*]
     */
    public function enqueuePublicStyles();

    /**
     * Enqueues the scripts for the front-end.
     *
     * @since [*next-version*]
     */
    public function enqueuePublicScripts();

    /**
     * Enqueues the styles for the front-end.
     *
     * @since [*next-version*]
     */
    public function enqueueAdminStyles();

    /**
     * Enqueues the scripts for the front-end.
     *
     * @since [*next-version*]
     */
    public function enqueueAdminScripts();
}