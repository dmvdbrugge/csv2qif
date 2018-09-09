<?php

namespace Csv2Qif\RuleSet\Rules\Rules;

use Csv2Qif\Transactions\IngTransaction;

class RuleIsEmpty implements RuleHasProperty
{
    use WithOrigin;
    use WithProperty;

    public function match(IngTransaction $transaction): bool
    {
        return empty($this->getProperty($transaction));
    }

    public function validate(IngTransaction $transaction): bool
    {
        return $this->validateProperty($transaction);
    }
}
