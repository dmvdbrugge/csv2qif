<?php

namespace Csv2Qif\RuleSet\Rules\Rules;

use Csv2Qif\Transactions\IngTransaction;

class RuleEquals implements RuleHasProperty, RuleHasValue
{
    use WithOrigin;
    use WithProperty;
    use WithValue;

    public function match(IngTransaction $transaction): bool
    {
        // Not strict on purpose
        return $this->getProperty($transaction) == $this->value;
    }

    public function validate(IngTransaction $transaction): bool
    {
        return $this->validateProperty($transaction)
            && $this->validateValue();
    }
}
