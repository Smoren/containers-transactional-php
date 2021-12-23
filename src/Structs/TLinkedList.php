<?php

namespace Smoren\Containers\Transactional\Structs;


use Smoren\Containers\Structs\LinkedList;
use Smoren\Containers\Transactional\Base\TransactionWrapper;

/**
 * Class TLinkedList
 * Wraps LinkedList
 * @see \Smoren\Containers\Structs\LinkedList
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