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

    /**
     * Adds error by key
     * @param string $key key
     * @param mixed $errorData data of error
     * @return self
     */
    public function addError(string $key, $errorData): self
    {
        $this->data[$key] = $errorData;
        return $this;
    }
}
