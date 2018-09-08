<?php

namespace RuleSet\Rules\Rules;

trait WithRules
{
    /** @var Rule[] */
    private $rules;

    public function setRules(Rule ...$rules): void
    {
        $this->rules = $rules;
    }
}
