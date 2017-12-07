<?php

namespace RuleSet;

use Transactions\IngTransaction;

class RulesValidator implements RulesEngine
{
    private $reflection;

    public function __construct()
    {
        $this->reflection = new \ReflectionClass($this);
    }

    public function allOf(IngTransaction $transaction, ...$rules): bool
    {
        foreach ($rules as $rule) {
            if (!$this->validateRule($transaction, $rule)) {
                return false;
            }
        }

        return true;
    }

    public function oneOf(IngTransaction $transaction, ...$rules): bool
    {
        foreach ($rules as $rule) {
            if (!$this->validateRule($transaction, $rule)) {
                return false;
            }
        }

        return true;
    }

    public function not(IngTransaction $transaction, ...$arguments): bool
    {
        return $this->validateRule($transaction, $arguments);
    }

    private function validateRule(IngTransaction $transaction, array $rule): bool
    {
        $function = array_shift($rule);

        if (!$function || !$this->reflection->hasMethod($function) || $function == '__construct') {
            return false;
        }

        $method = $this->reflection->getMethod($function);

        if (!$method->isPublic()) {
            return false;
        }

        if ($method->isVariadic()) {
            if (empty($rule)) {
                return false;
            }
        } else {
            if ($method->getNumberOfParameters() !== count($rule) + 1) {
                return false;
            }
        }

        return $this->{$function}($transaction, ...$rule);
    }

    private function validateProperty(IngTransaction $transaction, string $property): bool
    {
        $propertyParts = explode('->', $property);
        $lastPart      = array_pop($propertyParts);
        $value         = $transaction;

        foreach ($propertyParts as $part) {
            if (!property_exists($value, $part)) {
                return false;
            }

            $value = $value->{$part};
        }

        return (bool)property_exists($value, $lastPart);
    }

    public function contains(IngTransaction $transaction, $property, $value): bool
    {
        return $this->validateProperty($transaction, $property)
            && is_string($value)
            && $value !== '';
    }

    public function equals(IngTransaction $transaction, $property, $value): bool
    {
        return $this->validateProperty($transaction, $property);
    }

    public function greaterThan(IngTransaction $transaction, $property, $value): bool
    {
        return $this->validateProperty($transaction, $property)
            && is_numeric($value);
    }

    public function isEmpty(IngTransaction $transaction, $property): bool
    {
        return $this->validateProperty($transaction, $property);
    }

    public function lessThan(IngTransaction $transaction, $property, $value): bool
    {
        return $this->validateProperty($transaction, $property)
            && is_numeric($value);
    }
}
