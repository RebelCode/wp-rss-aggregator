<?php

namespace Aventura\Wprss\Core\Model\BulkSourceImport;

use Exception;

/**
 * A simple feed source importer that imports from an array of URLs mapping to feed source names.
 *
 * @since [*next-version*]
 */
class ArrayImporter extends AbstractWpImporter implements ImporterInterface
{
    /**
     * Constructor.
     *
     * @since 4.11
     *
     * @param array    $data       Data members map.
     * @param callable $translator A translator.
     *
     * @throws Exception If the translator is invalid.
     */
    public function __construct($data, $translator = null)
    {
        parent::__construct($data);

        if (!is_null($translator)) {
            $this->_setTranslator($translator);
        }
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function _inputToSourcesList($input)
    {
        if (!is_array($input)) {
            return [];
        }

        $sources = [];
        foreach ($input as $k => $v) {
            $sources[] = [
                ImporterInterface::SK_URL => $k,
                ImporterInterface::SK_TITLE => $v,
                'status' => 'publish'
            ];
        }

        return $sources;
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function import($input)
    {
        $list = $this->_inputToSourcesList($input);
        $results = $this->_importFromSourcesList($list);

        return $results;
    }
}
