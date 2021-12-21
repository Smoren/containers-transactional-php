<?php


namespace Smoren\StructsTransactional\base;


use Smoren\StructsTransactional\exceptions\ValidationException;
use Smoren\StructsTransactional\exceptions\WrapperException;

/**
 * Class TransactionWrapper
 */
class TransactionWrapper
{
    /**
     * @var mixed
     */
    protected $wrapped;
    /**
     * @var mixed|null
     */
    protected $temporary;
    /**
     * @var Validator|null $validator
     */
    protected ?Validator $validator;

    /**
     * TransactionWrapper constructor.
     * @param null $object
     * @param Validator|null $validator
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
     * @param callable $interactor
     * @return $this
     */
    public function interact(callable $interactor): self
    {
        $this->start();
        $interactor($this->temporary);
        return $this;
    }

    /**
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
     * @return $this
     * @throws ValidationException
     */
    public function validate(): self
    {
        if($this->validator === null) {
            return $this;
        }

        $this->start();
        $this->validator->validate($this->temporary);

        return $this;
    }

    /**
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
     * @return $this
     */
    public function rollback(): self
    {
        $this->start();
        $this->temporary = null;
        return $this;
    }

    /**
     * @return mixed|null
     */
    public function getWrapped()
    {
        return $this->wrapped;
    }

    /**
     * @param Validator $validator
     * @return $this
     */
    public function setValidator(Validator $validator): self
    {
        $this->validator = $validator;
        return $this;
    }

    /**
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
     * @return Validator|null
     */
    protected function getDefaultValidator(): ?Validator
    {
        return null;
    }

    /**
     * @return mixed|null
     */
    protected function getDefaultWrapped()
    {
        return null;
    }

    /**
     * @return string|null
     */
    protected function getWrappedType(): ?string
    {
        return null;
    }

    /**
     * @param $object
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