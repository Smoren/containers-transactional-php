<?php


namespace Smoren\Containers\Transactional\Exceptions;


use Smoren\ExtendedExceptions\BadDataException;

/**
 * Class WrapperException
 */
class WrapperException extends BadDataException
{
    const STATUS_BAD_WRAPPED_TYPE = 1;
}