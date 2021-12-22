<?php

namespace Smoren\StructsTransactional\structs;


use Smoren\Structs\structs\MappedCollection;
use Smoren\StructsTransactional\base\TransactionWrapper;

/**
 * Class TMappedCollection
 * Wraps MappedCollection
 * @see \Smoren\Structs\structs\MappedCollection
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
