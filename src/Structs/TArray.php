<?php

namespace Smoren\Containers\Transactional\Structs;


use Smoren\Containers\Transactional\Base\TransactionWrapper;

/**
 * Class TArray
 * Wraps php array
 */
class TArray extends TransactionWrapper
{
    /**
     * @inheritDoc
     * @return array
     */
    public function getWrapped(): array
    {
        return parent::getWrapped();
    }

    /**
     * @inheritDoc
     * @return array
     */
    protected function getDefaultWrapped()
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    protected function getWrappedType(): ?string
    {
        return 'array';
    }
}
