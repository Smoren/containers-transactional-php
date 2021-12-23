<?php

namespace Smoren\StructsTransactional\Structs;


use Smoren\Structs\exceptions\MappedCollectionException;
use Smoren\Structs\exceptions\MappedLinkedListException;
use Smoren\Structs\Structs\LinkedList;
use Smoren\Structs\Structs\MappedLinkedList;
use Smoren\StructsTransactional\Base\TransactionWrapper;

/**
 * Class TMappedLinkedList
 * Wraps MappedLinkedList
 * @see \Smoren\Structs\Structs\MappedLinkedList
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
