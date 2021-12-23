<?php

namespace Smoren\StructsTransactional\Structs;


use Exception;
use Smoren\Structs\Structs\LinkedList;
use Smoren\Structs\Structs\SortedMappedLinkedList;
use Smoren\StructsTransactional\Base\TransactionWrapper;

/**
 * Class TSortedMappedLinkedList
 * Wraps SortedMappedLinkedList
 * @see \Smoren\Structs\Structs\SortedMappedLinkedList
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
