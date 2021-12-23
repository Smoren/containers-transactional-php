# Transactional data structures

Abstract wrapper structure for providing transactional interface for editing objects.

Includes implementations of transactional wrappers for some data structures.

### How to install as dependency to your project
```
composer require smoren/structs-transactional
```

### Unit testing
```
composer install
./vendor/bin/codecept build
./vendor/bin/codecept run unit tests/unit
```

### Using with your class

#### Class definitions

```php
use Smoren\StructsTransactional\Base\TransactionWrapper;
use Smoren\StructsTransactional\Base\Validator;
use Smoren\StructsTransactional\Interfaces\ValidationRuleInterface;

/**
 * Your class that you want to wrap with transactional interface
 */
class YourClass {
    /**
     * Example attribute
     * @var int 
     */
    public $someAttribute = 1;
    // ...
}

/**
 * @see Smoren\StructsTransactional\Structs\TLinkedList
 */
class TYourClass extends TransactionWrapper
{
    /**
     * This overriding is needed only to specify method's return type
     * @inheritDoc
     */
    public function getWrapped(): YourClass
    {
        return parent::getWrapped();
    }

    /**
     * Unnecessary override if there is not default value of YourClass
     * @inheritDoc
     */
    protected function getDefaultWrapped()
    {
        return new YourClass();
    }

    /**
     * @inheritDoc
     * @return string
     */
    protected function getWrappedType(): ?string
    {
        return YourClass::class;
    }
    
    /**
     * Unnecessary override if you are not going to use validation
     * @inheritDoc
     * @return Validator
     * @throws \Smoren\ExtendedExceptions\LogicException
     */
    protected function getDefaultValidator(): ?Validator
    {
        return (new Validator())
            ->addRule('validation_rule_alias', MyValidationRule::class);
    }
}

/**
 * This declaration is not necessary if you are not going to use validation
 * @see Smoren\StructsTransactional\Tests\Unit\Utility\PositiveNumberValidationRule
 */
class MyValidationRule implements ValidationRuleInterface
{
    protected array $errors = [];
    
    /**
     * @inheritDoc
     */
    public function validate($data): bool
    {
        // ...
        return true;
    }

    /**
     * @inheritDoc
     */
    public function getErrors()
    {
        return $this->errors;
    }
}
```

#### Client code

```php
use Smoren\StructsTransactional\Exceptions\ValidationException;

$yourObject = new YourClass();
$tWrapper = new TYourClass($yourObject);

echo $yourObject->someAttribute; // output: 1
echo $tWrapper->getWrapped()->someAttribute; // output: 1

$tWrapper->interact(function(YourClass $tmpObjectState) {
    $tmpObjectState->someAttribute = 2;
});

echo $yourObject->someAttribute; // output: 1

try {
    $tWrapper->validate(); // validate only
    echo $yourObject->someAttribute; // output: 1
} catch(ValidationException $e) {
    print_r($e->getErrors()); // output: ['validation_rule_alias' => [...]]
}

try {
    $tWrapper->apply(); // validate and apply
    echo $yourObject->someAttribute; // output: 2
} catch(ValidationException $e) {
    print_r($e->getErrors()); // output: ['validation_rule_alias' => [...]]
}
```

### Ready to use transactional containers

#### TArray

Wraps PHP array with transactional interface.

```php
use Smoren\StructsTransactional\Structs\TArray;
use Smoren\StructsTransactional\Exceptions\ValidationException;

$ta = new TArray([1, 2, 3]);
print_r($ta->getWrapped()); // output: [1, 2, 3]

$ta->interact(function(array &$arr) {
    array_push($arr, 4);
    array_push($arr, 5);
    $arr[0] = 0;
});


try {
    print_r($ta->getWrapped()); // output: [1, 2, 3]
    $ta->apply();
    print_r($ta->getWrapped()); // output: [0, 2, 3, 4, 5]
} catch(ValidationException $e) {
    // if validation failed
}
```

#### TMappedCollection

Map-like data structure with transactional interface.

```php
use Smoren\StructsTransactional\Structs\TMappedCollection;
use Smoren\StructsTransactional\Exceptions\ValidationException;
use Smoren\Structs\Structs\MappedCollection;

$tmc = new TMappedCollection([
    '0' => ['id' => 0],
]);

print_r($tmc->getWrapped()->toArray()); // output: ['0' => ['id' => 0]]

$tmc->interact(function(MappedCollection $collection) {
    $collection
        ->add('1', ['id' => 1])
        ->add('2', ['id' => 2])
        ->delete('2');
});

try {
    print_r($tmc->getWrapped()->toArray()); // output: ['0' => ['id' => 0]]
    $tmc->apply();
    print_r($tmc->getWrapped()->toArray()); // output: ['0' => ['id' => 0], ['1' => ['id' => 1]]]
} catch(ValidationException $e) {
    // if validation failed
}
```

#### TLinkedList

Classic implementation of linked list data structure wrapped with transactional interface.

```php
use Smoren\StructsTransactional\Structs\TLinkedList;
use Smoren\StructsTransactional\Exceptions\ValidationException;
use Smoren\Structs\Structs\LinkedList;

$tll = new TLinkedList([1, 2, 3]);
print_r($tll->getWrapped()->toArray()); // output: [1, 2, 3]

$tll->interact(function(LinkedList $list) {
    $list->pushBack(4);
    $list->pushBack(5);
});

try {
    print_r($tll->getWrapped()->toArray()); // output: [1, 2, 3]
    $tll->apply();
    print_r($tll->getWrapped()->toArray()); // output: [1, 2, 3, 4, 5]
} catch(ValidationException $e) {
    // if validation failed
}
```

#### TMappedLinkedList

LinkedList with mapping by id and with transactional interface.

```php
use Smoren\StructsTransactional\Structs\TMappedLinkedList;
use Smoren\StructsTransactional\Exceptions\ValidationException;
use Smoren\Structs\Structs\MappedLinkedList;

$tmll = new TMappedLinkedList(
    new MappedLinkedList([1 => 11])
);

print_r($tmll->getWrapped()->toArray()); // output: [1 => 11]

$tmll->interact(function(MappedLinkedList $list) {
    $list->pushBack(2, 22);
    $list->pushBack(3, 33);
});

try {
    print_r($tmll->getWrapped()->toArray()); // output: [1 => 11]
    $tmll->apply();
    print_r($tmll->getWrapped()->toArray()); // output: [1 => 11, 2 => 22, 3 => 33]
} catch(ValidationException $e) {
    // if validation failed
}
```

#### TSortedLinkedList

LinkedList with presort and transactional interface.

```php
use Smoren\StructsTransactional\Structs\TSortedLinkedList;
use Smoren\StructsTransactional\Exceptions\ValidationException;
use Smoren\Structs\Structs\SortedLinkedList;

/**
 * Class IntegerSortedLinkedList
 */
class IntegerSortedLinkedList extends SortedLinkedList
{
    /**
     * @inheritDoc
     */
    protected function getComparator(): callable
    {
        return function(int $lhs, int $rhs) {
            return $lhs > $rhs;
        };
    }
}

$tsll = new TSortedLinkedList(
    new IntegerSortedLinkedList([4, 1, 2])
);

print_r($tsll->getWrapped()->toArray()); // output: [1, 2, 4]

$tsll->interact(function(IntegerSortedLinkedList $list) {
    $list->insert(5);
    $list->insert(3);
});

try {
    print_r($tsll->getWrapped()->toArray()); // output: [1, 2, 4]
    $tsll->apply();
    print_r($tsll->getWrapped()->toArray()); // output: [1, 2, 3, 4, 5]
} catch(ValidationException $e) {
    // if validation failed
}
```

#### TSortedMappedLinkedList

LinkedList with presort, mapping and transactional interface.

```php
use Smoren\StructsTransactional\Structs\TSortedMappedLinkedList;
use Smoren\StructsTransactional\Exceptions\ValidationException;
use Smoren\Structs\Structs\SortedMappedLinkedList;

$tsmll = new TSortedMappedLinkedList(
    new SortedMappedLinkedList([2 => -2, 1 => -1, 4 => -4])
);

print_r($tsmll->getWrapped()->toArray()); // output: [1 => -1, 2 => -2, 4 => -4]

$tsmll->interact(function(SortedMappedLinkedList $list) {
    $list->insert(3, 0);
});

try {
    print_r($tsmll->getWrapped()->toArray()); // output: [1 => -1, 2 => -2, 4 => -4]
    $tsmll->apply();
    print_r($tsmll->getWrapped()->toArray()); // output: [1 => -1, 2 => -2, 3 => 0, 4 => -4]
} catch(ValidationException $e) {
    // if validation failed
}
```
