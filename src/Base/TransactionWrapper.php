<?php


namespace Smoren\Containers\Transactional\Base;


use Smoren\Containers\Transactional\Exceptions\ValidationException;
use Smoren\Containers\Transactional\Exceptions\WrapperException;

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
        if($this->validator === null) {
            return $this;
        }

        $this->start();
        $this->validator->validate($this->temporary, $this);

        return $this;
    }

    /**
     * Commits transaction
     * @return $this
     */
    public function commit(): self
    {
        $this->start();
        $this->wrapped = $this->temporary;
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
}