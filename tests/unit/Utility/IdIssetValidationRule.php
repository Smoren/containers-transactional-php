<?php


namespace Smoren\StructsTransactional\Tests\Unit\Utility;


use Smoren\StructsTransactional\Interfaces\ValidationRuleInterface;

/**
 * Class IdIssetValidationRule
 */
class IdIssetValidationRule implements ValidationRuleInterface
{
    /**
     * @var array[] errors
     */
    protected array $errors = [
        'not_specified' => [],
    ];

    /**
     * @inheritDoc
     */
    public function validate($data): bool
    {
        $errorCount = 0;
        foreach($data as $id => $item) {
            if(!isset($item['id'])) {
                $this->errors['not_specified'][] = $id;
                ++$errorCount;
            }
        }

        if($errorCount) {
            return false;
        }

        return true;
    }

    /**
     * @inheritDoc
     * @return array[]
     */
    public function getErrors()
    {
        return $this->errors;
    }
}