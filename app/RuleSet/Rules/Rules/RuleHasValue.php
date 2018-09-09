<?php

namespace Csv2Qif\RuleSet\Rules\Rules;

interface RuleHasValue extends Rule
{
    public function setValue($value): void;
}
