<?php


namespace Smoren\Containers\Transactional\Tests\Unit\Utility;


use Smoren\Containers\Structs\MappedCollection;
use Smoren\Containers\Transactional\Base\Validator;
use Smoren\Containers\Transactional\Interfaces\ValidationRuleInterface;

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
    public function validate($data, $params = null, $owner = null): bool
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