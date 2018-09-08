<?php

namespace RuleSet\Rules\Rules;

interface RuleHasRules extends Rule
{
    public function setRules(Rule ...$rules): void;
}
