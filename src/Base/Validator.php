<?php


namespace Smoren\Containers\Transactional\Base;


use Smoren\Containers\Transactional\Exceptions\ValidationException;
use Smoren\Containers\Transactional\Interfaces\ValidationRuleInterface;
use Smoren\ExtendedExceptions\LogicException;

/**
 * Class Validator
 */
class Validator
{
    /**
     * @var string[] rules classnames list
     */
    protected array $rules = [];
    /**
     * @var array rules validation params
     */
    protected array $params = [];

    /**
     * Validates object by rules
     * @param mixed $data object to validate
     * @param mixed $owner owner of validating data
     * @return $this
     * @throws ValidationException
     */
    public function validate($data, $owner = null): self
    {
        $errors = [];

        foreach($this->rules as $id => $ruleClass) {
            /** @var ValidationRuleInterface $rule */
            $rule = new $ruleClass();
            if(!$rule->validate($data, $this->params[$id] ?? null, $owner)) {
                $errors[$id] = $rule->getErrors();
            }
        }

        if(count($errors)) {
            throw new ValidationException(
                'validation failed', ValidationException::STATUS_VALIDATION_FAILED, null, $errors
            );
        }

        return $this;
    }

    /**
     * Adds rule to validator
     * @param string $id rule ID
     * @param string $rule classname of rule
     * @return $this
     * @throws LogicException
     */
    public function addRule(string $id, string $rule, $params = null): self
    {
        if(!is_subclass_of($rule, ValidationRuleInterface::class)) {
            throw new LogicException("{$rule} is not an instance of ".ValidationRuleInterface::class, 1);
        }
        $this->rules[$id] = $rule;
        $this->params[$id] = $params;
        return $this;
    }
}