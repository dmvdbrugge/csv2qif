<?php

namespace Csv2Qif\RuleSet\Rules\Rules;

interface RuleHasRules extends Rule
{
    public function setRules(Rule ...$rules): void;
}
