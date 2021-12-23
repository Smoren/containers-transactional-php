<?php

namespace Smoren\Containers\Transactional\Structs;


use Exception;
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
     * @return SortedLinkedList
     */
    public function getWrapped(): SortedLinkedList
    {
        return parent::getWrapped();
    }

    /**
     * @return TransactionWrapper
     * @throws Exception
     */
    public function commit(): TransactionWrapper
    {
        $result = parent::commit();
        $this->getWrapped()->refresh();
        return $result;
    }

    /**
     * @inheritDoc
     */
    protected function getWrappedType(): ?string
    {
        return SortedLinkedList::class;
    }
}
