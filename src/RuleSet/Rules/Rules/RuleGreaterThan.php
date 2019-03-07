<?php

namespace Csv2Qif\RuleSet\Rules\Rules;

use Csv2Qif\Transactions\IngTransaction;

use function is_numeric;

class RuleGreaterThan implements RuleHasProperty, RuleHasValue
{
    use WithOrigin;
    use WithProperty;
    use WithValue;

    public function match(IngTransaction $transaction): bool
    {
        return $this->getProperty($transaction) > $this->value;
    }

    public function validate(IngTransaction $transaction): bool
    {
        return $this->validateProperty($transaction)
            && is_numeric($this->value);
    }
}
