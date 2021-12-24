<?php


namespace Smoren\Containers\Transactional\Base;


use Smoren\Containers\Transactional\Exceptions\ValidationException;
use Smoren\Containers\Transactional\Exceptions\WrapperException;
use Smoren\Containers\Transactional\Interfaces\RecursiveTransactional;
use Smoren\Containers\Transactional\Interfaces\Restorable;
use Traversable;

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
     * @var Validator $validator validator object
     */
    protected Validator $validator;

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

        $subErrors = $this->callRecursive(function(TransactionWrapper $wrapper) use (&$subErrors) {
            $wrapper->validate();
        });

        try {
            if($this->validator !== null) {
                $this->validator->validate($this->temporary, $this);
            }
        } catch(ValidationException $e) {
            if(count($subErrors)) {
                $e->addError('recursive', $subErrors);
            }
            throw $e;
        }

        if(count($subErrors)) {
            throw new ValidationException(
                'validation failed', ValidationException::STATUS_VALIDATION_FAILED, null,
                ['recursive' => $subErrors]
            );
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
     * Returns temporary state
     * @return mixed|null
     */
    public function getTemporary()
    {
        return $this->temporary;
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
     * Returns Validator object
     * @return Validator
     */
    public function getValidator(): Validator
    {
        return $this->validator;
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
     * @return Validator
     */
    protected function getDefaultValidator(): Validator
    {
        return new Validator();
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
     * Makes recursive calls to transactional children
     * @param callable $callback actions to call
     * @return array errors
     */
    protected function callRecursive(callable $callback): array
    {
        $errors = [];

        if($this->temporary instanceof RecursiveTransactional) {
            foreach($this->temporary->getTransactionalFields() as $propName => $propValue) {
                if($propValue instanceof TransactionWrapper) {
                    try {
                        $callback($propValue);
                    } catch(ValidationException $e) {
                        $errors[$propName] = $e->getErrors();
                    }
                } else {
                    $i = 0;
                    foreach($propValue as $id => $propValueItem) {
                        if(is_object($id) || is_array($id)) {
                            $id = $i;
                        }

                        try {
                            $callback($propValueItem);
                        } catch(ValidationException $e) {
                            if(!isset($errors[$propName])) {
                                $errors[$propName] = [];
                            }

                            $errors[$propName][$id] = $e->getErrors();
                        }

                        ++$i;
                    }
                }
            }
        } elseif($this->temporary instanceof Traversable) {
            $i = 0;
            foreach($this->temporary as $id => $propValue) {
                if(!($propValue instanceof TransactionWrapper)) {
                    continue;
                }

                if(is_object($id) || is_array($id)) {
                    $id = $i;
                }

                try {
                    $callback($propValue);
                } catch(ValidationException $e) {
                    $errors[$id] = $e->getErrors();
                }

                ++$i;
            }
        }

        return $errors;
    }
}