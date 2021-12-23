<?php

namespace Smoren\StructsTransactional\Structs;


use Smoren\Structs\Structs\LinkedList;
use Smoren\Structs\Structs\SortedLinkedList;
use Smoren\StructsTransactional\Base\TransactionWrapper;

/**
 * Class TSortedLinkedList
 * Wraps SortedLinkedList
 * @see \Smoren\Structs\Structs\SortedLinkedList
 */
class TSortedLinkedList extends TransactionWrapper
{
    /**
     * @inheritDoc
     * @return LinkedList
     */
    public function getWrapped(): SortedLinkedList
    {
        return parent::getWrapped();
    }

    /**
     * @inheritDoc
     */
    protected function getWrappedType(): ?string
    {
        return SortedLinkedList::class;
    }
}
