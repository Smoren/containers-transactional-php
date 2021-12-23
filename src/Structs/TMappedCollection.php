<?php

namespace Smoren\StructsTransactional\Structs;


use Smoren\Structs\Structs\MappedCollection;
use Smoren\StructsTransactional\Base\TransactionWrapper;

/**
 * Class TMappedCollection
 * Wraps MappedCollection
 * @see \Smoren\Structs\Structs\MappedCollection
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
