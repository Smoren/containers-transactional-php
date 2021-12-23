<?php


namespace Smoren\Containers\Transactional\Interfaces;

/**
 * Interface ValidationRuleInterface
 */
interface ValidationRuleInterface
{
    /**
     * Validate object
     * @param mixed $data object to validate
     * @param mixed $owner owner of validating data
     * @return bool
     */
    public function validate($data, $owner): bool;

    /**
     * Return error of validation
     * @return mixed
     */
    public function getErrors();
}