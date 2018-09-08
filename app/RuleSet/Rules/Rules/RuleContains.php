<?php

namespace RuleSet\Rules\Rules;

use Transactions\IngTransaction;

use function is_string;
use function stripos;

class RuleContains implements RuleHasProperty, RuleHasValue
{
    use WithOrigin;
    use WithProperty;
    use WithValue;

    public function match(IngTransaction $transaction): bool
    {
        return stripos($this->getProperty($transaction), $this->value) !== false;
    }

    public function validate(IngTransaction $transaction): bool
    {
        return $this->validateProperty($transaction)
            && is_string($this->value)
            && $this->value !== '';
    }
}
