<?php


namespace Smoren\StructsTransactional\interfaces;


interface ValidationRuleInterface
{
    public function validate($data): bool;

    public function getError();
}