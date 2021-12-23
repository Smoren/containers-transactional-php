<?php


namespace Smoren\Containers\Transactional\Interfaces;


/**
 * Interface Restorable
 */
interface Restorable
{
    /**
     * Restore object state from another instance
     * @param Restorable $source
     * @return mixed
     */
    public function restoreState($source);
}