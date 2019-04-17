<?php

namespace RebelCode\Wpra\Core\Modules;

use Parsedown;
use Psr\Container\ContainerInterface;

/**
 * A module that provides the Parsedown service for WP RSS Aggregator.s
 *
 * @since [*next-version*]
 */
class ParsedownModule implements ModuleInterface
{
    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function getFactories()
    {
        return [
            /*
             * The Parsedown service.
             *
             * @since [*next-version*]
             */
            'wpra/parsedown' => function () {
                $instance = new Parsedown();
                $instance->setBreaksEnabled(true);
                $instance->setMarkupEscaped(true);

                return $instance;
            },
        ];
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function getExtensions()
    {
        return [
            /*
             * Extends the changelog by parsing it as Markdown using Parsedown.
             *
             * @since [*next-version*]
             */
            'wpra/core/changelog' => function (ContainerInterface $c, $changelog) {
                $parsed = $c->get('wpra/parsedown')->text($changelog);
                $wrapped = sprintf('<div class="wpra-changelog-container">%s</div>', $parsed);

                return $wrapped;
            },
        ];
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function run(ContainerInterface $c)
    {
    }
}
