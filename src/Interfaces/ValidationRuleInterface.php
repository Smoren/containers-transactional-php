<?php


namespace Smoren\Containers\Transactional\Interfaces;

use Smoren\Containers\Transactional\Base\Validator;

/**
 * Interface ValidationRuleInterface
 */
interface ValidationRuleInterface
{
    /**
     * Validate object
     * @param mixed $data object to validate
     * @param mixed $params extra validation params
     * @param mixed $owner owner of validating data
     * @return bool
     */
    public function validate($data, $params = null, $owner = null): bool;

    /**
     * Return error of validation
     * @return mixed
     */
    public function getErrors();
}