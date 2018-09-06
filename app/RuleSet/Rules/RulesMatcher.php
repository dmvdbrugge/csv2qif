<?php

namespace RuleSet\Rules;

use Transactions\IngTransaction;

use function count;
use function current;
use function explode;
use function is_array;
use function stripos;

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

    /**
     * @param array|string $rule
     */
    private function matchRule(IngTransaction $transaction, $rule): bool
    {
        if (is_array($rule)) {
            return $this->matchArrayRule($transaction, $rule);
        }

        $rule = explode(' ', $rule, 4);

        for ($i = count($rule); $i <= 4; $i++) {
            $rule[] = null;
        }

        [$property, $not, $function, $value] = $rule;

        $params = [$property];
        $negate = $not === 'not';

        if (!$negate) {
            $value    = rtrim("{$function} {$value}");
            $function = $not;
        }

        if (!empty($value)) {
            $params[] = $value;
        }

        return $negate xor $this->{$function}($transaction, ...$params);
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

    private function matchArrayRule(IngTransaction $transaction, array $rule): bool
    {
        $function = key($rule);

        return $this->{$function}($transaction, ...current($rule));
    }
}
