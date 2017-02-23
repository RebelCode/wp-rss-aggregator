<?php

namespace Aventura\Wprss\Core\Model\BulkSourceImport\Exception;

/**
 * An exception that occurs if a feed source representation is invalid.
 *
 * @since [*next-version*]
 */
class SourceValidationFailureException extends AbstractSourceValidationFailureException implements SourceValidationFailureExceptionInterface
{
    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     *
     * @param string $message {@inheritdoc}
     * @param int $code {@inheritdoc}
     * @param \Exception $previous {@inheritdoc}
     * @param array|\Traversable $validationErrors The list of validation errors.
     */
    public function __construct($message = "", $code = 0, \Exception $previous = null, $validationErrors = array())
    {
        parent::__construct($message, $code, $previous);

        $this->_setValidationErrors($validationErrors);
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function getValidationErrors()
    {
        return $this->_getValidationErrors();
    }
}
