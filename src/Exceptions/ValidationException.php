<?php


namespace Smoren\Containers\Transactional\Exceptions;


use Smoren\ExtendedExceptions\BadDataException;

/**
 * Class ValidationException
 */
class ValidationException extends BadDataException
{
    const STATUS_VALIDATION_FAILED = 1;

    /**
     * Returns errors of validation
     * @return array errors
     */
    public function getErrors(): array
    {
        return $this->data;
    }
}
