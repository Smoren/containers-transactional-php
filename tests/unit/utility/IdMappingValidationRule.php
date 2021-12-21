<?php


namespace Smoren\StructsTransactional\tests\unit\utility;


use Smoren\Structs\structs\MappedCollection;
use Smoren\StructsTransactional\interfaces\ValidationRuleInterface;

class IdMappingValidationRule implements ValidationRuleInterface
{
    protected array $error = [
        'not_specified' => [],
        'not_match' => [],
    ];

    /**
     * @param MappedCollection $data
     * @return bool
     */
    public function validate($data): bool
    {
        $errorCount = 0;
        foreach($data as $id => $item) {
            if(!isset($item['id'])) {
                $this->error['not_specified'][] = $id;
                ++$errorCount;
            } elseif((string)$item['id'] !== (string)$id) {
                $this->error['not_match'][] = $id;
                ++$errorCount;
            }
        }

        if($errorCount) {
            return false;
        }

        return true;
    }

    public function getError()
    {
        return $this->error;
    }
}