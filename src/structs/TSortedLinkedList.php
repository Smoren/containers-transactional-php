<?php

namespace Smoren\StructsTransactional\structs;


use Smoren\Structs\structs\LinkedList;
use Smoren\Structs\structs\SortedLinkedList;
use Smoren\StructsTransactional\base\TransactionWrapper;

/**
 * Class TSortedLinkedList
 * Wraps SortedLinkedList
 * @see \Smoren\Structs\structs\SortedLinkedList
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
