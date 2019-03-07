<?php

namespace Csv2Qif\RuleSet\Rules\Rules;

use Csv2Qif\Transactions\IngTransaction;

interface Rule
{
    public function getOrigin(): string;

    public function setOrigin(string $origin): void;

    public function match(IngTransaction $transaction): bool;

    public function validate(IngTransaction $transaction): bool;
}
