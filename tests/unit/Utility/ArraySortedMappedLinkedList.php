<?php


namespace Smoren\Containers\Transactional\Tests\Unit\Utility;


use Smoren\Containers\Structs\SortedMappedLinkedList;

/**
 * Class ArrayMappedSortedLinkedList
 */
class ArraySortedMappedLinkedList extends SortedMappedLinkedList
{
    /**
     * @inheritDoc
     */
    protected function getComparator(): callable
    {
        return function(array $lhs, array $rhs) {
            return $lhs['id'] > $rhs['id'];
        };
    }
}