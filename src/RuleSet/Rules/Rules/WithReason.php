<?php

namespace Csv2Qif\RuleSet\Rules\Rules;

trait WithReason
{
    private $reason;

    public function getReason(): string
    {
        return $this->reason;
    }

    public function setReason(string $reason): void
    {
        $this->reason = $reason;
    }
}
