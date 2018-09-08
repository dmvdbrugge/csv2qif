<?php

namespace RuleSet\Rules\Rules;

use Event\Hook;
use RuleSet\RuleSetValidator;
use Transactions\IngTransaction;

class RuleOneOf implements RuleHasRules
{
    use WithOrigin;
    use WithRules;

    /** @var Hook */
    private $hook;

    public function __construct(Hook $hook)
    {
        $this->hook = $hook;
    }

    public function match(IngTransaction $transaction): bool
    {
        foreach ($this->rules as $rule) {
            if ($rule->match($transaction)) {
                return true;
            }
        }

        return false;
    }

    public function validate(IngTransaction $transaction): bool
    {
        $valid = !empty($this->rules);

        foreach ($this->rules as $rule) {
            if (!$rule->validate($transaction)) {
                $this->hook->trigger(RuleSetValidator::VALIDATE_RULE_ERROR, $rule);
                $valid = false;
            }
        }

        return $valid;
    }
}
