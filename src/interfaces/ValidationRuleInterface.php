<?php


namespace Smoren\StructsTransactional\interfaces;

/**
 * Interface ValidationRuleInterface
 */
interface ValidationRuleInterface
{
    /**
     * Validate object
     * @param mixed $data object to validate
     * @return bool
     */
    public function validate($data): bool;

    /**
     * Return error of validation
     * @return mixed
     */
    public function getErrors();
}