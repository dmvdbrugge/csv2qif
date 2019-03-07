<?php

namespace Csv2Qif\RuleSet\Rules\Rules;

use BadMethodCallException;
use Csv2Qif\Transactions\IngTransaction;

class RuleInvalid implements RuleHasReason
{
    use WithOrigin;
    use WithReason;

    public function match(IngTransaction $transaction): bool
    {
        // When this rule exists, match should never have been called.
        throw new BadMethodCallException($this->getReason());
    }

    public function validate(IngTransaction $transaction): bool
    {
        return false;
    }
}
