<?php

namespace Csv2Qif\RuleSet\Rules\Rules;

interface RuleHasReason extends Rule
{
    public function getReason(): string;

    public function setReason(string $reason): void;
}
