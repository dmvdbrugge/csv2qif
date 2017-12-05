<?php

namespace Transactions;

class RulesMatcher implements RulesEngine
{
    public function allOf(IngTransaction $transaction, ...$rules): bool
    {
        foreach ($rules as $rule) {
            if (!$this->matchRule($transaction, $rule)) {
                return false;
            }
        }

        return true;
    }

    public function oneOf(IngTransaction $transaction, ...$rules): bool
    {
        foreach ($rules as $rule) {
            if ($this->matchRule($transaction, $rule)) {
                return true;
            }
        }

        return false;
    }

    public function not(IngTransaction $transaction, ...$arguments): bool
    {
        return !$this->matchRule($transaction, $arguments);
    }

    private function matchRule(IngTransaction $transaction, array $rule): bool
    {
        $function = array_shift($rule);

        return $this->{$function}($transaction, ...$rule);
    }

    private function getProperty(IngTransaction $transaction, string $property)
    {
        $propertyParts = explode('->', $property);
        $value         = $transaction;

        foreach ($propertyParts as $part) {
            $value = $value->{$part} ?? null;
        }

        return $value;
    }

    public function contains(IngTransaction $transaction, $property, $value): bool
    {
        return stripos($this->getProperty($transaction, $property), $value) !== false;
    }

    public function equals(IngTransaction $transaction, $property, $value): bool
    {
        return $this->getProperty($transaction, $property) == $value;
    }

    public function greaterThan(IngTransaction $transaction, $property, $value): bool
    {
        return $this->getProperty($transaction, $property) > $value;
    }

    public function isEmpty(IngTransaction $transaction, $property): bool
    {
        return empty($this->getProperty($transaction, $property));
    }

    public function lessThan(IngTransaction $transaction, $property, $value): bool
    {
        return $this->getProperty($transaction, $property) < $value;
    }
}
