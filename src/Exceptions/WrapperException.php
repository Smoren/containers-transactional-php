<?php


namespace Smoren\StructsTransactional\Exceptions;


use Smoren\ExtendedExceptions\BadDataException;

/**
 * Class WrapperException
 */
class WrapperException extends BadDataException
{
    const STATUS_BAD_WRAPPED_TYPE = 1;
}