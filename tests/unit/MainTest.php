<?php


namespace Smoren\StructsTransactional\tests\unit;

use Codeception\Lib\Console\Output;
use Codeception\Test\Unit;
use Exception;
use Smoren\ExtendedExceptions\LogicException;
use Smoren\Structs\exceptions\LinkedListException;
use Smoren\Structs\exceptions\MappedCollectionException;
use Smoren\Structs\exceptions\MappedLinkedListException;
use Smoren\Structs\structs\LinkedList;
use Smoren\Structs\structs\LinkedListItem;
use Smoren\Structs\structs\MappedCollection;
use Smoren\Structs\structs\MappedLinkedList;
use Smoren\Structs\structs\SortedLinkedList;
use Smoren\Structs\structs\SortedMappedLinkedList;
use Smoren\StructsTransactional\base\Validator;
use Smoren\StructsTransactional\exceptions\ValidationException;
use Smoren\StructsTransactional\exceptions\WrapperException;
use Smoren\StructsTransactional\structs\TArray;
use Smoren\StructsTransactional\structs\TLinkedList;
use Smoren\StructsTransactional\structs\TMappedCollection;
use Smoren\StructsTransactional\structs\TMappedLinkedList;
use Smoren\StructsTransactional\structs\TSortedLinkedList;
use Smoren\StructsTransactional\structs\TSortedMappedLinkedList;
use Smoren\StructsTransactional\tests\unit\utility\ArraySortedMappedLinkedList;
use Smoren\StructsTransactional\tests\unit\utility\IdIssetValidationRule;
use Smoren\StructsTransactional\tests\unit\utility\IdMappingValidationRule;
use Smoren\StructsTransactional\tests\unit\utility\IntegerSortedLinkedList;
use Smoren\StructsTransactional\tests\unit\utility\PositiveNumberValidationRule;

class MainTest extends Unit
{
    /**
     * @throws LogicException
     * @throws ValidationException
     * @throws WrapperException
     */
    public function testTransactionalArray()
    {
        $ta = new TArray([1, 2, 3]);
        $this->assertCount(3, $ta->getWrapped());
        $this->assertEquals([1, 2, 3], $ta->getWrapped());

        $ta->interact(function(array &$arr) use ($ta) {
            array_push($arr, 4);
            array_push($arr, 5);
            $arr[0] = 0;

            $this->assertCount(5, $arr);
            $this->assertEquals([0, 2, 3, 4, 5], $arr);

            $this->assertCount(3, $ta->getWrapped());
            $this->assertEquals([1, 2, 3], $ta->getWrapped());
        });

        $ta->apply();
        $this->assertCount(5, $ta->getWrapped());
        $this->assertEquals([0, 2, 3, 4, 5], $ta->getWrapped());

        $ta->setValidator((new Validator())->addRule('positive', PositiveNumberValidationRule::class));

        try {
            $ta->validate();
            $this->assertTrue(false);
        } catch(ValidationException $e) {
            $this->assertTrue(isset($e->getData()['positive']['zero']));
            $this->assertCount(1, $e->getData()['positive']['zero']);
            $this->assertTrue(isset($e->getData()['positive']['negative']));
            $this->assertCount(0, $e->getData()['positive']['negative']);
        }

        $ta->interact(function(array &$arr) use ($ta) {
            array_shift($arr);
            $this->assertEquals([2, 3, 4, 5], $arr);
            $this->assertEquals([0, 2, 3, 4, 5], $ta->getWrapped());
        });

        $this->assertEquals([0, 2, 3, 4, 5], $ta->getWrapped());
        $ta->apply();
        $this->assertEquals([2, 3, 4, 5], $ta->getWrapped());

        $ta->interact(function(array &$arr) {
            $this->assertEquals([2, 3, 4, 5], $arr);
        });
    }

    /**
     * @throws LogicException
     * @throws MappedCollectionException
     * @throws ValidationException
     * @throws WrapperException
     */
    public function testTransactionalMappedCollection()
    {
        $wrapper = new TMappedCollection();
        $this->assertEquals(0, $wrapper->getWrapped()->count());

        $wrapper->interact(function(MappedCollection $collection) {
            $collection
                ->add('1', ['id' => 1])
                ->add('2', ['id' => 2])
                ->delete('2');
        });
        $this->assertEquals(0, $wrapper->getWrapped()->count());
        $this->assertEquals([], $wrapper->getWrapped()->toArray());
        $wrapper->apply();
        $this->assertEquals(1, $wrapper->getWrapped()->count());
        $this->assertEquals(['1' => ['id' => 1]], $wrapper->getWrapped()->getMap());

        $wrapper->interact(function(MappedCollection $collection) {
            $collection->add('2', ['id' => 2]);
            $this->assertEquals(2, $collection->count());
        });
        $this->assertEquals(1, $wrapper->getWrapped()->count());
        $wrapper->rollback();
        $this->assertEquals(1, $wrapper->getWrapped()->count());

        $wrapper->interact(function(MappedCollection $collection) {
            $collection->add('2', ['id' => 2]);
            $this->assertEquals(2, $collection->count());
        });
        $wrapper->interact(function(MappedCollection $collection) {
            $collection->add('3', ['id' => 3]);
            $this->assertEquals(3, $collection->count());
            try {
                $collection->add('2', ['id' => 2]);
                $this->assertTrue(false);
            } catch(MappedCollectionException $e) {
                $this->assertEquals(3, $collection->count());
            }
        });
        $this->assertEquals(1, $wrapper->getWrapped()->count());
        $wrapper->apply();
        $this->assertEquals(3, $wrapper->getWrapped()->count());

        $wrapper->setValidator((new Validator())->addRule('id', IdMappingValidationRule::class));
        $wrapper->validate();

        $wrapper->interact(function(MappedCollection $collection) {
            $collection->add(5, ['id' => 6]);
            $collection->add(6, ['no_id' => 6]);
            $collection->add(7, ['id' => 7]);
            $collection->add(8, ['no_id' => 9]);
        });
        $this->assertEquals(3, $wrapper->getWrapped()->count());

        try {
            $wrapper->apply();
            $this->assertTrue(false);
        } catch(ValidationException $e) {
            $this->assertEquals(3, $wrapper->getWrapped()->count());
            $errorData = $e->getErrors();

            $this->assertTrue(isset($errorData['id']['not_specified']));
            $this->assertTrue(isset($errorData['id']['not_match']));

            $this->assertEquals([6, 8], $errorData['id']['not_specified']);
            $this->assertEquals([5], $errorData['id']['not_match']);
        }

        $wrapper->interact(function(MappedCollection $collection) {
            $collection->add(5, ['id' => 5]);
        });
        $wrapper->apply();
        $this->assertEquals(4, $wrapper->getWrapped()->count());

        $wrapper->interact(function(MappedCollection $collection) {
            $this->assertFalse($collection->get(6, false));
            $this->assertEquals(['id' => 5], $collection->get(5, false));
        });

        $wrapper->interact(function(MappedCollection $collection) {
            $collection->delete(5);
            $collection->add(99, ['id' => 101]);
        });

        try {
            $wrapper->apply();
            $this->assertTrue(false);
        } catch(ValidationException $e) {
            $this->assertEquals(4, $wrapper->getWrapped()->count());
            $errorData = $e->getErrors();

            $this->assertTrue(isset($errorData['id']['not_specified']));
            $this->assertTrue(isset($errorData['id']['not_match']));

            $this->assertEquals([], $errorData['id']['not_specified']);
            $this->assertEquals([99], $errorData['id']['not_match']);

            $this->assertEquals(['id' => 5], $wrapper->getWrapped()->get(5));
        }

        $wrapper->interact(function(MappedCollection $collection) {
            $collection->add(99, ['id' => 101]);
        });
        $wrapper->commit();
        $this->assertEquals(5, $wrapper->getWrapped()->count());
        $this->assertEquals(['id' => 101], $wrapper->getWrapped()->get(99));

        try {
            $wrapper->validate();
            $this->assertTrue(false);
        } catch(ValidationException $e) {
            $this->assertEquals(5, $wrapper->getWrapped()->count());
            $this->assertEquals(['id' => 101], $wrapper->getWrapped()->get(99));
            $this->assertEquals([99], $e->getErrors()['id']['not_match']);
            $this->assertEquals([], $e->getErrors()['id']['not_specified']);
        }
    }

    /**
     * @throws LogicException
     * @throws ValidationException
     * @throws WrapperException
     */
    public function testTransactionalLinkedList()
    {
        $wrapper = new TLinkedList();
        $this->assertEquals(0, $wrapper->getWrapped()->count());
        $this->assertEquals([], $wrapper->getWrapped()->toArray());

        $wrapper->interact(function(LinkedList $list) {
            $this->assertEquals(0, $list->count());
            $list->pushBack(['id' => 1]);
            $this->assertEquals(1, $list->count());
            $this->assertEquals([['id' => 1]], $list->toArray());
        });
        $this->assertEquals(0, $wrapper->getWrapped()->count());
        $this->assertEquals([], $wrapper->getWrapped()->toArray());

        $wrapper->apply();
        $this->assertEquals(1, $wrapper->getWrapped()->count());
        $this->assertEquals([['id' => 1]], $wrapper->getWrapped()->toArray());

        $wrapper->interact(function(LinkedList $list) {
            $this->assertEquals(1, $list->count());
            $list->pushBack(['id' => 2]);
            $list->pushFront(['id' => 0]);
            $this->assertEquals([['id' => 0], ['id' => 1], ['id' => 2]], $list->toArray());
            $this->assertEquals(3, $list->count());
        });
        $this->assertEquals([['id' => 1]], $wrapper->getWrapped()->toArray());
        $this->assertEquals(1, $wrapper->getWrapped()->count());

        $wrapper->apply();
        $this->assertEquals(3, $wrapper->getWrapped()->count());
        $this->assertEquals([['id' => 0], ['id' => 1], ['id' => 2]], $wrapper->getWrapped()->toArray());

        $wrapper->setValidator((new Validator())->addRule('id', IdIssetValidationRule::class));
        $wrapper->validate();

        $wrapper->interact(function(LinkedList $list) {
            $this->assertEquals(3, $list->count());
            $list->pushBack(['non_id_attr' => 10]);
            $this->assertEquals(4, $list->count());
            $this->assertEquals([['id' => 0], ['id' => 1], ['id' => 2], ['non_id_attr' => 10]], $list->toArray());
        });
        $this->assertEquals(3, $wrapper->getWrapped()->count());
        $this->assertEquals([['id' => 0], ['id' => 1], ['id' => 2]], $wrapper->getWrapped()->toArray());

        try {
            $wrapper->apply();
            $this->assertTrue(false);
        } catch(ValidationException $e) {
            $this->assertTrue(isset($e->getData()['id']['not_specified']));
            $this->assertCount(1, $e->getData()['id']['not_specified']);
            $this->assertEquals([['id' => 0], ['id' => 1], ['id' => 2]], $wrapper->getWrapped()->toArray());
            $this->assertTrue($e->getData()['id']['not_specified'][0] instanceof LinkedListItem);
        }
    }

    /**
     * @throws LinkedListException
     * @throws LogicException
     * @throws MappedCollectionException
     * @throws MappedLinkedListException
     * @throws ValidationException
     * @throws WrapperException
     *
     */
    public function testTransactionalMappedLinkedList()
    {
        $ll = new TMappedLinkedList(
            new MappedLinkedList([1 => -1, 2 => -2, -2 => 2, 10 => -10, 8 => -8])
        );
        $this->assertCount(5, $ll->getWrapped()->toArray());
        $this->assertEquals([1, 2, -2, 10, 8], array_keys($ll->getWrapped()->toArray()));
        $this->assertEquals([-1, -2, 2, -10, -8], array_values($ll->getWrapped()->toArray()));

        $ll->interact(function(MappedLinkedList $list) use ($ll) {
            $this->assertCount(5, $list->toArray());
            $this->assertEquals([1, 2, -2, 10, 8], array_keys($list->toArray()));
            $this->assertEquals([-1, -2, 2, -10, -8], array_values($list->toArray()));

            $list->pushBack(6, -6);
            $list->pushBefore(6, 5, -5);
            $list->pop(1);
            $list->pushAfter(2, 3, -3);
            $list->popFront();
            $list->popBack();
            $list->pop(10);

            $this->assertCount(4, $list->toArray());
            $this->assertEquals([3, -2, 8, 5], array_keys($list->toArray()));
            $this->assertEquals([-3, 2, -8, -5], array_values($list->toArray()));

            $this->assertCount(5, $ll->getWrapped()->toArray());
            $this->assertEquals([1, 2, -2, 10, 8], array_keys($ll->getWrapped()->toArray()));
            $this->assertEquals([-1, -2, 2, -10, -8], array_values($ll->getWrapped()->toArray()));
        });

        $this->assertCount(5, $ll->getWrapped()->toArray());
        $this->assertEquals([1, 2, -2, 10, 8], array_keys($ll->getWrapped()->toArray()));
        $this->assertEquals([-1, -2, 2, -10, -8], array_values($ll->getWrapped()->toArray()));

        $ll->apply();

        $this->assertCount(4, $ll->getWrapped()->toArray());
        $this->assertEquals([3, -2, 8, 5], array_keys($ll->getWrapped()->toArray()));
        $this->assertEquals([-3, 2, -8, -5], array_values($ll->getWrapped()->toArray()));

        $ll->setValidator((new Validator())->addRule('positive', PositiveNumberValidationRule::class));

        try {
            $ll->validate();
            $this->assertTrue(false);
        } catch(ValidationException $e) {
            $this->assertTrue(isset($e->getData()['positive']['zero']));
            $this->assertCount(0, $e->getData()['positive']['zero']);
            $this->assertTrue(isset($e->getData()['positive']['negative']));
            $this->assertCount(3, $e->getData()['positive']['negative']);
        }

        $ll->interact(function(MappedLinkedList $list) {
            $list->pop(3);
            $list->pop(8);
            $list->pop(5);
            $list->pushFront(1, 1);
            $list->pushBack(5, 5);

            $this->assertEquals([1, -2, 5], array_keys($list->toArray()));
            $this->assertEquals([1, 2, 5], array_values($list->toArray()));
        });

        $ll->validate();

        $this->assertCount(4, $ll->getWrapped()->toArray());
        $this->assertEquals([3, -2, 8, 5], array_keys($ll->getWrapped()->toArray()));
        $this->assertEquals([-3, 2, -8, -5], array_values($ll->getWrapped()->toArray()));

        $ll->apply();

        $this->assertCount(3, $ll->getWrapped()->toArray());
        $this->assertEquals([1, -2, 5], array_keys($ll->getWrapped()->toArray()));
        $this->assertEquals([1, 2, 5], array_values($ll->getWrapped()->toArray()));
    }

    /**
     * @throws LogicException
     * @throws ValidationException
     * @throws WrapperException
     * @throws Exception
     */
    public function testTransactionalSortedLinkedList()
    {
        $ll = new TSortedLinkedList(new IntegerSortedLinkedList([3, 1, 7, 2, 1]));
        $this->assertCount(5, $ll->getWrapped());
        $this->assertEquals([1, 1, 2, 3, 7], $ll->getWrapped()->toArray());

        $ll->interact(function(IntegerSortedLinkedList $list) use ($ll) {
            $this->assertCount(5, $list);
            $this->assertEquals([1, 1, 2, 3, 7], $list->toArray());

            $list->insert(2);
            $this->assertCount(6, $list);
            $this->assertEquals([1, 1, 2, 2, 3, 7], $list->toArray());

            $this->assertCount(5, $ll->getWrapped());
            $this->assertEquals([1, 1, 2, 3, 7], $ll->getWrapped()->toArray());
        });

        $this->assertCount(5, $ll->getWrapped());
        $this->assertEquals([1, 1, 2, 3, 7], $ll->getWrapped()->toArray());

        $ll->apply();
        $this->assertEquals([1, 1, 2, 2, 3, 7], $ll->getWrapped()->toArray());

        $ll->setValidator((new Validator())->addRule('positive', PositiveNumberValidationRule::class));
        $ll->validate();

        $ll->interact(function(SortedLinkedList $list) use ($ll) {
            $list->insert(-1);
            $list->insert(-2);
            $list->insert(5);
            $list->insert(0);

            $this->assertEquals([-2, -1, 0, 1, 1, 2, 2, 3, 5, 7], $list->toArray());
        });

        try {
            $ll->validate();
            $this->assertTrue(false);
        } catch(ValidationException $e) {
            $this->assertEquals([1, 1, 2, 2, 3, 7], $ll->getWrapped()->toArray());
        }

        $ll->interact(function(SortedLinkedList $list) use ($ll) {
            $this->assertEquals([-2, -1, 0, 1, 1, 2, 2, 3, 5, 7], $list->toArray());
        });

        try {
            $ll->apply();
            $this->assertTrue(false);
        } catch(ValidationException $e) {
            $this->assertEquals([1, 1, 2, 2, 3, 7], $ll->getWrapped()->toArray());
        }

        $ll->interact(function(SortedLinkedList $list) use ($ll) {
            $this->assertEquals([1, 1, 2, 2, 3, 7], $list->toArray());
        });
    }

    /**
     * @throws LogicException
     * @throws ValidationException
     * @throws WrapperException
     * @throws Exception
     */
    public function testTransactionalSortedMappedLinkedList()
    {
        $ll = new TSortedMappedLinkedList(
            new SortedMappedLinkedList([1 => -1, 2 => -2, -2 => 2, 10 => -10, 8 => -8])
        );
        $this->assertCount(5, $ll->getWrapped()->toArray());
        $this->assertEquals([-2, 1, 2, 8, 10], array_keys($ll->getWrapped()->toArray()));
        $this->assertEquals([2, -1, -2, -8, -10], array_values($ll->getWrapped()->toArray()));

        $ll->interact(function(SortedMappedLinkedList $list) use ($ll) {
            $this->assertCount(5, $list->toArray());
            $this->assertEquals([-2, 1, 2, 8, 10], array_keys($list->toArray()));
            $this->assertEquals([2, -1, -2, -8, -10], array_values($list->toArray()));

            $list->insert(6, -6);
            $list->pop(1);
            $list->popFront();
            $list->popBack();

            $this->assertCount(3, $list->toArray());
            $this->assertEquals([2, 6, 8], array_keys($list->toArray()));
            $this->assertEquals([-2, -6, -8], array_values($list->toArray()));

            $this->assertCount(5, $ll->getWrapped()->toArray());
            $this->assertEquals([-2, 1, 2, 8, 10], array_keys($ll->getWrapped()->toArray()));
            $this->assertEquals([2, -1, -2, -8, -10], array_values($ll->getWrapped()->toArray()));
        });

        $this->assertCount(5, $ll->getWrapped()->toArray());
        $this->assertEquals([-2, 1, 2, 8, 10], array_keys($ll->getWrapped()->toArray()));
        $this->assertEquals([2, -1, -2, -8, -10], array_values($ll->getWrapped()->toArray()));

        $ll->apply();

        $this->assertCount(3, $ll->getWrapped()->toArray());
        $this->assertEquals([2, 6, 8], array_keys($ll->getWrapped()->toArray()));
        $this->assertEquals([-2, -6, -8], array_values($ll->getWrapped()->toArray()));

        $ll->setValidator((new Validator())->addRule('positive', PositiveNumberValidationRule::class));

        try {
            $ll->validate();
            $this->assertTrue(false);
        } catch(ValidationException $e) {
            $this->assertTrue(isset($e->getData()['positive']['zero']));
            $this->assertCount(0, $e->getData()['positive']['zero']);
            $this->assertTrue(isset($e->getData()['positive']['negative']));
            $this->assertCount(3, $e->getData()['positive']['negative']);
        }

        $ll = new TSortedMappedLinkedList(
            new ArraySortedMappedLinkedList([1 => ['id' => 1], 3 => ['id' => 3], 2 => ['id' => 2]])
        );
        $this->assertCount(3, $ll->getWrapped()->toArray());
        $this->assertEquals([1, 2, 3], array_keys($ll->getWrapped()->toArray()));
        $this->assertEquals([['id' => 1], ['id' => 2], ['id' => 3]], array_values($ll->getWrapped()->toArray()));

        $ll->interact(function(ArraySortedMappedLinkedList $list) use ($ll) {
            $this->assertCount(3, $list);
            $this->assertEquals([1, 2, 3], array_keys($list->toArray()));
            $this->assertEquals([['id' => 1], ['id' => 2], ['id' => 3]], array_values($list->toArray()));

            $list->insert(0, ['id' => 0]);
            $this->assertCount(4, $list);
            $this->assertEquals([0, 1, 2, 3], array_keys($list->toArray()));
            $this->assertEquals([['id' => 0], ['id' => 1], ['id' => 2], ['id' => 3]], array_values($list->toArray()));

            $this->assertCount(3, $ll->getWrapped());
            $this->assertEquals([1, 2, 3], array_keys($ll->getWrapped()->toArray()));
            $this->assertEquals([['id' => 1], ['id' => 2], ['id' => 3]], array_values($ll->getWrapped()->toArray()));
        });

        $this->assertEquals([1, 2, 3], array_keys($ll->getWrapped()->toArray()));
        $this->assertEquals([['id' => 1], ['id' => 2], ['id' => 3]], array_values($ll->getWrapped()->toArray()));

        $ll->apply();
        $this->assertEquals([0, 1, 2, 3], array_keys($ll->getWrapped()->toArray()));
        $this->assertEquals([
            ['id' => 0], ['id' => 1], ['id' => 2], ['id' => 3]
        ], array_values($ll->getWrapped()->toArray()));

        $ll->setValidator((new Validator())->addRule('positive', IdMappingValidationRule::class));
        $ll->validate();

        $ll->interact(function(ArraySortedMappedLinkedList $list) use ($ll) {
            $list->insert(6, ['id' => 6]);
            $list->insert(4, ['id' => 5]);

            $this->assertEquals([0, 1, 2, 3, 4, 6], array_keys($list->toArray()));
            $this->assertEquals([
                ['id' => 0], ['id' => 1], ['id' => 2], ['id' => 3], ['id' => 5], ['id' => 6]
            ], array_values($list->toArray()));
        });

        try {
            $ll->validate();
            $this->assertTrue(false);
        } catch(ValidationException $e) {
            $this->assertEquals([
                ['id' => 0], ['id' => 1], ['id' => 2], ['id' => 3]
            ], array_values($ll->getWrapped()->toArray()));
        }

        try {
            $ll->apply();
            $this->assertTrue(false);
        } catch(ValidationException $e) {
            $this->assertEquals([
                ['id' => 0], ['id' => 1], ['id' => 2], ['id' => 3]
            ], array_values($ll->getWrapped()->toArray()));
        }

        $ll->interact(function(SortedMappedLinkedList $list) use ($ll) {
            $this->assertEquals([
                ['id' => 0], ['id' => 1], ['id' => 2], ['id' => 3]
            ], array_values($ll->getWrapped()->toArray()));
        });
    }

    /**
     * Debug print method
     * @param mixed $log
     */
    public function log($log)
    {
        $output = new Output([]);
        $output->writeln(PHP_EOL);
        $output->writeln('-------------------');
        $output->writeln(print_r($log, 1));
        $output->writeln('-------------------');
    }
}