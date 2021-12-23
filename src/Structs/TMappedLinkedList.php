<?php

namespace Smoren\Containers\Transactional\Structs;


use Smoren\Containers\exceptions\MappedCollectionException;
use Smoren\Containers\exceptions\MappedLinkedListException;
use Smoren\Containers\Structs\LinkedList;
use Smoren\Containers\Structs\MappedLinkedList;
use Smoren\Containers\Transactional\Base\TransactionWrapper;

/**
 * Class TMappedLinkedList
 * Wraps MappedLinkedList
 * @see \Smoren\Containers\Structs\MappedLinkedList
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
