<?php


namespace Smoren\StructsTransactional\exceptions;


use Smoren\ExtendedExceptions\BadDataException;

/**
 * Class WrapperException
 */
class WrapperException extends BadDataException
{
    const STATUS_BAD_WRAPPED_TYPE = 1;
}