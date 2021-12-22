<?php

namespace Smoren\StructsTransactional\structs;


use Exception;
use Smoren\Structs\structs\LinkedList;
use Smoren\Structs\structs\SortedMappedLinkedList;
use Smoren\StructsTransactional\base\TransactionWrapper;

/**
 * Class TSortedMappedLinkedList
 * Wraps SortedMappedLinkedList
 * @see \Smoren\Structs\structs\SortedMappedLinkedList
 */
class TSortedMappedLinkedList extends TransactionWrapper
{
    /**
     * @inheritDoc
     * @return LinkedList
     */
    public function getWrapped(): SortedMappedLinkedList
    {
        return parent::getWrapped();
    }

    /**
     * @inheritDoc
     * @return SortedMappedLinkedList
     * @throws Exception
     */
    protected function getDefaultWrapped()
    {
        return new SortedMappedLinkedList();
    }

    /**
     * @inheritDoc
     */
    protected function getWrappedType(): ?string
    {
        return SortedMappedLinkedList::class;
    }
}
