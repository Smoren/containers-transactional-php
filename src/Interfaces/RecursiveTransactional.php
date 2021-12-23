<?php


namespace Smoren\Containers\Transactional\Interfaces;


/**
 * Interface RecursiveTransactional
 */
interface RecursiveTransactional
{
    /**
     * Returns map of transactional properties
     * @return array [propName => propValue, ...]
     */
    public function getTransactionalFields(): array;
}