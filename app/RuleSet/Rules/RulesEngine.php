<?php

namespace RuleSet\Rules;

use Transactions\IngTransaction;

interface RulesEngine
{
    public function allOf(IngTransaction $transaction, ...$rules): bool;

    public function oneOf(IngTransaction $transaction, ...$rules): bool;

    public function not(IngTransaction $transaction, ...$arguments): bool;

    public function contains(IngTransaction $transaction, $property, $value): bool;

    public function equals(IngTransaction $transaction, $property, $value): bool;

    public function greaterThan(IngTransaction $transaction, $property, $value): bool;

    public function isEmpty(IngTransaction $transaction, $property): bool;

    public function lessThan(IngTransaction $transaction, $property, $value): bool;
}
