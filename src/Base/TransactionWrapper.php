<?php


namespace Smoren\Containers\Transactional\Base;


use Smoren\Containers\Transactional\Exceptions\ValidationException;
use Smoren\Containers\Transactional\Exceptions\WrapperException;
use Smoren\Containers\Transactional\Interfaces\Restorable;
use Smoren\ExtendedExceptions\LogicException;

/**
 * Class TransactionWrapper
 */
class TransactionWrapper
{
    /**
     * @var mixed wrapped object
     */
    protected $wrapped;
    /**
     * @var mixed|null temporary state of transaction
     */
    protected $temporary;
    /**
     * @var Validator|null $validator validator object
     */
    protected ?Validator $validator;

    /**
     * TransactionWrapper constructor.
     * @param null $object object to wrap
     * @param Validator|null $validator validation object
     * @throws WrapperException
     */
    public function __construct($object = null, ?Validator $validator = null)
    {
        $object = $object ?? $this->getDefaultWrapped();

        $this->checkWrappedType($object);

        $this->wrapped = $object;
        $this->temporary = null;
        $this->validator = $validator ?? $this->getDefaultValidator();
    }

    /**
     * Method from interacting with temporary transaction state
     * @param callable $interactor callback for interaction
     * @return $this
     */
    public function interact(callable $interactor): self
    {
        $this->start();
        $interactor($this->temporary);
        return $this;
    }

    /**
     * Validates and commits transaction if validation success otherwise rollbacks it
     * @throws ValidationException
     */
    public function apply(): self
    {
        try {
            $this->start();
            $this->validate();
            return $this->commit();
        } catch(ValidationException $e) {
            $this->rollback();
            throw $e;
        }
    }

    /**
     * Validates transaction temporary state
     * @return $this
     * @throws ValidationException
     */
    public function validate(): self
    {
        $this->start();

        $subErrors = [];
        $this->callRecursive(function(TransactionWrapper $wrapper, string $attrName, $id = null) use (&$subErrors) {
            try {
                $wrapper->validate();
            } catch(ValidationException $e) {
                if($id === null) {
                    $subErrors[$attrName] = $e->getErrors();
                }

                if(!isset($subErrors[$attrName])) {
                    $subErrors[$attrName] = [];
                }

                $subErrors[$attrName][$id] = $e->getErrors();
            }
        });

        if($this->validator === null) {
            return $this;
        }

        try {
            $this->validator->validate($this->temporary, $this);
        } catch(ValidationException $e) {
            if(count($subErrors)) {
                $e->addError('recursive', $subErrors);
            }
            throw $e;
        }

        return $this;
    }

    /**
     * Commits transaction
     * @return $this
     */
    public function commit(): self
    {
        $this->start();

        $this->callRecursive(function(TransactionWrapper $wrapper) {
            $wrapper->commit();
        });

        if($this->wrapped instanceof Restorable) {
            $this->wrapped->restoreState($this->temporary);
        } else {
            $this->wrapped = $this->temporary;
        }
        $this->temporary = null;
        return $this;
    }

    /**
     * Rollbacks transaction
     * @return $this
     */
    public function rollback(): self
    {
        $this->start();

        $this->callRecursive(function(TransactionWrapper $wrapper) {
            $wrapper->rollback();
        });

        $this->temporary = null;
        return $this;
    }

    /**
     * Returns wrapped object
     * @return mixed|null
     */
    public function getWrapped()
    {
        return $this->wrapped;
    }

    /**
     * Sets validator object
     * @param Validator $validator validator object
     * @return $this
     */
    public function setValidator(Validator $validator): self
    {
        $this->validator = $validator;
        return $this;
    }

    /**
     * Starts transaction if not already started
     * @return $this
     */
    protected function start(): self
    {
        if($this->temporary === null) {
            if(is_object($this->wrapped)) {
                $this->temporary = clone $this->wrapped;
            } else {
                $this->temporary = $this->wrapped;
            }
        }

        return $this;
    }

    /**
     * Returns default validator object
     * @return Validator|null
     */
    protected function getDefaultValidator(): ?Validator
    {
        return null;
    }

    /**
     * Return default instance of wrapped object
     * @return mixed|null
     */
    protected function getDefaultWrapped()
    {
        return null;
    }

    /**
     * Returns string with wrapped object classname
     * @return string|null
     */
    protected function getWrappedType(): ?string
    {
        return null;
    }

    /**
     * Validates wrapped object type
     * @param mixed $object wrapped object
     * @return $this
     * @throws WrapperException
     */
    protected function checkWrappedType($object): self
    {
        if($object === null) {
            throw new WrapperException('cannot wrap null', WrapperException::STATUS_BAD_WRAPPED_TYPE);
        }

        $type = $this->getWrappedType();
        if($type === null) {
            return $this;
        }

        $errorFlag = false;

        if(is_object($object)) {
            $objectType = get_class($object);
            if(!($object instanceof $type)) {
                $errorFlag = true;
            }
        } else {
            $objectType = gettype($object);
            if($objectType !== $type) {
                $errorFlag = true;
            }
        }

        if($errorFlag) {
            throw new WrapperException(
                "expected wrapped type {$type}, given {$objectType}",
                WrapperException::STATUS_BAD_WRAPPED_TYPE
            );
        }

        return $this;
    }

    /**
     * Returns names of transactional properties
     * @return string[]
     */
    protected function getTransactionalAttributes(): array
    {
        return [];
    }

    /**
     * Returns names of transactional properties collections
     * @return string[]
     */
    protected function getTransactionalCollectionAttributes(): array
    {
        return [];
    }

    /**
     * Makes recursive calls to transactional children
     * @param callable $callback actions to call
     * @return $this
     */
    protected function callRecursive(callable $callback): self
    {
        foreach($this->getTransactionalAttributes() as $attrName) {
            $callback($this->{$attrName}, $attrName);
        }

        foreach($this->getTransactionalCollectionAttributes() as $attrName) {
            $i = 0;
            foreach($this->{$attrName} as $id => $item) {
                if(is_object($id) || is_array($id)) {
                    $id = $i;
                }
                $callback($item, $attrName, $id);
                ++$i;
            }
        }

        return $this;
    }
}