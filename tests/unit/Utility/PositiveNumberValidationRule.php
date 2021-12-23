<?php


namespace Smoren\StructsTransactional\Tests\Unit\Utility;


use Smoren\Structs\Structs\MappedCollection;
use Smoren\StructsTransactional\Interfaces\ValidationRuleInterface;

class PositiveNumberValidationRule implements ValidationRuleInterface
{
    /**
     * @var array[] errors
     */
    protected array $error = [
        'zero' => [],
        'negative' => [],
    ];

    /**
     * @inheritDoc
     * @param MappedCollection $data
     */
    public function validate($data): bool
    {
        $errorCount = 0;
        foreach($data as $id => $item) {
            if($item < 0) {
                $this->error['negative'][] = $id;
                ++$errorCount;
            } elseif($item == 0) {
                $this->error['zero'][] = $id;
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
        return $this->error;
    }
}