<?php


namespace Smoren\StructsTransactional\exceptions;


use Smoren\ExtendedExceptions\BadDataException;

class ValidationException extends BadDataException
{
    public function getErrors(): array
    {
        return $this->data;
    }
}
