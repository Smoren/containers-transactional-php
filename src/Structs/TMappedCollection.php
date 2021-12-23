<?php

namespace Smoren\Containers\Transactional\Structs;


use Smoren\Containers\Structs\MappedCollection;
use Smoren\Containers\Transactional\Base\TransactionWrapper;

/**
 * Class TMappedCollection
 * Wraps MappedCollection
 * @see \Smoren\Containers\Structs\MappedCollection
 */
class TMappedCollection extends TransactionWrapper
{
    /**
     * @inheritDoc
     * @return MappedCollection
     */
    public function getWrapped(): MappedCollection
    {
        return parent::getWrapped();
    }

    /**
     * @inheritDoc
     * @return MappedCollection
     */
    protected function getDefaultWrapped()
    {
        return new MappedCollection();
    }

    /**
     * @inheritDoc
     */
    protected function getWrappedType(): ?string
    {
        return MappedCollection::class;
    }
}
