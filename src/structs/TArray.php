<?php


namespace Smoren\StructsTransactional\structs;


use Smoren\StructsTransactional\base\TransactionWrapper;

/**
 * Class TArray
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