<?php

namespace Aventura\Wprss\Core\Model\BulkSourceImport;

/**
 * Something that can create multiple feed sources from input.
 *
 * @since [*next-version*]
 */
interface ImporterInterface
{
    const SK_TITLE = 'title';
    const SK_URL = 'url';

    /**
     * Creates feed sources from input.
     *
     * @since [*next-version*]
     *
     * @param mixed $input Some kind of input that can be used to retrieve information about a feed source.
     *
     * @return array The import results. For each source representation (in order), the result will be one of:
     * - Integer, representing the ID of the created resource;
     * - An {@see \Exception} if something went wrong during import.
     */
    public function import($input);
}