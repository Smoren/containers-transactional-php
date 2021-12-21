<?php


namespace Smoren\StructsTransactional\base;


use Smoren\StructsTransactional\exceptions\ValidationException;
use Smoren\StructsTransactional\interfaces\ValidationRuleInterface;
use Smoren\ExtendedExceptions\LogicException;

class Validator
{
    /**
     * @var string[]
     */
    protected array $rules;

    public function validate($data): self
    {
        $errors = [];

        foreach($this->rules as $id => $ruleClass) {
            /** @var ValidationRuleInterface $rule */
            $rule = new $ruleClass();
            if(!$rule->validate($data)) {
                $errors[$id] = $rule->getError();
            }
        }

        if(count($errors)) {
            throw new ValidationException('validation failed', 1, null, $errors);
        }

        return $this;
    }

    public function addRule(string $id, string $rule): self
    {
        if(!is_subclass_of($rule, ValidationRuleInterface::class)) {
            throw new LogicException("{$rule} is not an instance of ".ValidationRuleInterface::class, 1);
        }
        $this->rules[$id] = $rule;
        return $this;
    }
}