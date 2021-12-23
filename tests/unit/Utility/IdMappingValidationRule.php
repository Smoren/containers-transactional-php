<?php


namespace Smoren\StructsTransactional\Tests\Unit\Utility;


use Smoren\Structs\Structs\MappedCollection;
use Smoren\StructsTransactional\Interfaces\ValidationRuleInterface;

/**
 * Class IdMappingValidationRule
 */
class IdMappingValidationRule implements ValidationRuleInterface
{
    /**
     * @var array[] errors
     */
    protected array $errors = [
        'not_specified' => [],
        'not_match' => [],
    ];

    /**
     * @inheritDoc
     * @param MappedCollection $data
     */
    public function validate($data, $owner): bool
    {
        $errorCount = 0;
        foreach($data as $id => $item) {
            if(!isset($item['id'])) {
                $this->errors['not_specified'][] = $id;
                ++$errorCount;
            } elseif((string)$item['id'] !== (string)$id) {
                $this->errors['not_match'][] = $id;
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