<?php

namespace Aventura\Wprss\Core\Model;

/**
 * Something that can represent settings.
 *
 * @since [*next-version*]
 */
interface SettingsInterface
{
    public function validate($settings);

    public function getSectionsFields();

    public function getData();
}