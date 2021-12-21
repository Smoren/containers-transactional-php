<?php


namespace Smoren\StructsTransactional\structs;


use Smoren\Structs\structs\LinkedList;
use Smoren\StructsTransactional\base\TransactionWrapper;

/**
 * Class TLinkedList
 */
class TLinkedList extends TransactionWrapper
{
    /**
     * @inheritDoc
     * @return LinkedList
     */
    public function getWrapped(): LinkedList
    {
        return parent::getWrapped();
    }

    /**
     * @inheritDoc
     * @return LinkedList
     */
    protected function getDefaultWrapped()
    {
        return new LinkedList();
    }

    /**
     * @inheritDoc
     */
    protected function getWrappedType(): ?string
    {
        return LinkedList::class;
    }
}