<?php

namespace RebelCode\Wpra\Core\Templates;

use Dhii\Output\TemplateInterface as DhiiTemplateInterface;
use RebelCode\Wpra\Core\Data\DataSetInterface;

/**
 * Interface for objects that represent WP RSS Aggregator templates.
 *
 * @since [*next-version*]
 */
interface FeedTemplateInterface extends DhiiTemplateInterface
{
    /**
     * Retrieves the template's ID.
     *
     * @since [*next-version*]
     *
     * @return string
     */
    public function getId();

    /**
     * Retrieves the template's name.
     *
     * @since [*next-version*]
     *
     * @return string
     */
    public function getName();

    /**
     * Retrieves the template's options.
     *
     * @since [*next-version*]
     *
     * @return DataSetInterface
     */
    public function getOptions();
}
