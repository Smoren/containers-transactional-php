<?php

namespace Smoren\Containers\Transactional\Structs;


use Smoren\Containers\Structs\LinkedList;
use Smoren\Containers\Structs\SortedLinkedList;
use Smoren\Containers\Transactional\Base\TransactionWrapper;

/**
 * Class TSortedLinkedList
 * Wraps SortedLinkedList
 * @see \Smoren\Containers\Structs\SortedLinkedList
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
