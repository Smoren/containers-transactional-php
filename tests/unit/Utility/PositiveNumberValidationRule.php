<?php


namespace Smoren\Containers\Transactional\Tests\Unit\Utility;


use Smoren\Containers\Structs\MappedCollection;
use Smoren\Containers\Transactional\Base\Validator;
use Smoren\Containers\Transactional\Interfaces\ValidationRuleInterface;

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
    public function validate($data, $params = null, $owner = null): bool
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