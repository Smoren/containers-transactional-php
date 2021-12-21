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
use Smoren\StructsTransactional\base\TransactionWrapper;
use Smoren\StructsTransactional\base\Validator;
use Smoren\StructsTransactional\interfaces\ValidationRuleInterface;

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
 * @see Smoren\StructsTransactional\structs\TLinkedList
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
 * @see Smoren\StructsTransactional\tests\unit\utility\PositiveNumberValidationRule
 */
class MyValidationRule implements ValidationRuleInterface
{
    protected array $error = [];
    
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
    public function getError()
    {
        return $this->error;
    }
}
```

#### Client code

```php
use \Smoren\StructsTransactional\exceptions\ValidationException;

$yourObject = new YourClass();
$tWrapper = new TYourClass($yourObject);

echo $yourObject->someAttribute; // output: 1
echo $tWrapper->getWrapped()->someAttribute; output: 1

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

```\Smoren\StructsTransactional\structs\TArray```

Wraps PHP array with transactional interface

#### TMappedCollection

```\Smoren\StructsTransactional\structs\TMappedCollection```

Map-like data structure with transactional interface.

#### TLinkedList

```\Smoren\StructsTransactional\structs\TLinkedList```

Classic implementation of linked list data structure wrapped with transactional interface.

#### TSortedLinkedList

LinkedList with presort
