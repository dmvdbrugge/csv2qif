<?php

namespace RuleSet\Rules\Rules;

trait WithValue
{
    private $value;

    public function setValue($value): void
    {
        $this->value = $value;
    }

    private function validateValue(): bool
    {
        return $this->value !== null;
    }
}
