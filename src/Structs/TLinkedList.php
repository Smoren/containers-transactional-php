<?php

namespace Smoren\StructsTransactional\Structs;


use Smoren\Structs\Structs\LinkedList;
use Smoren\StructsTransactional\Base\TransactionWrapper;

/**
 * Class TLinkedList
 * Wraps LinkedList
 * @see \Smoren\Structs\Structs\LinkedList
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