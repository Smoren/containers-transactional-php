<?php

namespace Smoren\StructsTransactional\structs;


use Smoren\Structs\exceptions\MappedCollectionException;
use Smoren\Structs\exceptions\MappedLinkedListException;
use Smoren\Structs\structs\LinkedList;
use Smoren\Structs\structs\MappedLinkedList;
use Smoren\StructsTransactional\base\TransactionWrapper;

/**
 * Class TMappedLinkedList
 * Wraps MappedLinkedList
 * @see \Smoren\Structs\structs\MappedLinkedList
 */
class TMappedLinkedList extends TransactionWrapper
{
    /**
     * @inheritDoc
     * @return LinkedList
     */
    public function getWrapped(): MappedLinkedList
    {
        return parent::getWrapped();
    }

    /**
     * @inheritDoc
     * @return MappedLinkedList
     * @throws MappedLinkedListException|MappedCollectionException
     */
    protected function getDefaultWrapped()
    {
        return new MappedLinkedList();
    }

    /**
     * @inheritDoc
     */
    protected function getWrappedType(): ?string
    {
        return MappedLinkedList::class;
    }
}
