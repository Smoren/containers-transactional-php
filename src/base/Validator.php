<?php


namespace Smoren\StructsTransactional\base;


use Smoren\StructsTransactional\exceptions\ValidationException;
use Smoren\StructsTransactional\interfaces\ValidationRuleInterface;
use Smoren\ExtendedExceptions\LogicException;

/**
 * Class Validator
 */
class Validator
{
    /**
     * @var string[] rules classnames list
     */
    protected array $rules;

    /**
     * Validates object by rules
     * @param mixed $data object to validate
     * @return $this
     * @throws ValidationException
     */
    public function validate($data): self
    {
        $errors = [];

        foreach($this->rules as $id => $ruleClass) {
            /** @var ValidationRuleInterface $rule */
            $rule = new $ruleClass();
            if(!$rule->validate($data)) {
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
    public function addRule(string $id, string $rule): self
    {
        if(!is_subclass_of($rule, ValidationRuleInterface::class)) {
            throw new LogicException("{$rule} is not an instance of ".ValidationRuleInterface::class, 1);
        }
        $this->rules[$id] = $rule;
        return $this;
    }
}