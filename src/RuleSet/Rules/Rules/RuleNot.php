<?php

namespace Csv2Qif\RuleSet\Rules\Rules;

use Csv2Qif\Transactions\IngTransaction;

use function count;

class RuleNot implements RuleHasRules
{
    use WithOrigin;
    use WithRules;

    public function match(IngTransaction $transaction): bool
    {
        return !$this->rules[0]->match($transaction);
    }

    public function validate(IngTransaction $transaction): bool
    {
        if (count($this->rules) !== 1) {
            return false;
        }

        return $this->rules[0]->validate($transaction);
    }
}
