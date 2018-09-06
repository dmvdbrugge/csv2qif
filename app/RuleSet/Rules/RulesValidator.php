<?php

namespace RuleSet\Rules;

use Event\Hook;
use ReflectionClass;
use Transactions\IngTransaction;

use function array_pop;
use function count;
use function current;
use function explode;
use function in_array;
use function is_array;
use function is_numeric;
use function is_string;
use function property_exists;
use function rtrim;

class RulesValidator implements RulesEngine
{
    public const VALIDATE_ERROR = 'RulesValidator::error';

    private const ARRAY_RULES = ['allOf', 'oneOf'];

    /** @var Hook */
    private $hook;

    /** @var ReflectionClass */
    private $reflection;

    public function __construct(Hook $hook)
    {
        $this->hook       = $hook;
        $this->reflection = new ReflectionClass(RulesEngine::class);
    }

    public function allOf(IngTransaction $transaction, ...$rules): bool
    {
        $valid = !empty($rules);

        foreach ($rules as $rule) {
            if (!$this->validateRule($transaction, $rule)) {
                $this->hook->trigger(self::VALIDATE_ERROR, $rule);
                $valid = false;
            }
        }

        return $valid;
    }

    public function oneOf(IngTransaction $transaction, ...$rules): bool
    {
        $valid = !empty($rules);

        foreach ($rules as $rule) {
            if (!$this->validateRule($transaction, $rule)) {
                $this->hook->trigger(self::VALIDATE_ERROR, $rule);
                $valid = false;
            }
        }

        return $valid;
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

    /**
     * @param array|string $rule
     */
    private function validateRule(IngTransaction $transaction, $rule): bool
    {
        if (is_array($rule)) {
            return $this->validateArrayRule($transaction, $rule);
        }

        $rule = explode(' ', $rule, 4);

        for ($i = count($rule); $i <= 4; $i++) {
            $rule[] = null;
        }

        [$property, $not, $function, $value] = $rule;

        if ($not !== 'not') {
            $value    = rtrim("{$function} {$value}");
            $function = $not;
        }

        if (!$function || !$this->reflection->hasMethod($function)) {
            return false;
        }

        $method = $this->reflection->getMethod($function);

        if ($method->isVariadic()) {
            return false;
        }

        switch ($method->getNumberOfParameters()) {
            case 2:
                return $this->{$function}($transaction, $property);

            case 3:
                return $this->{$function}($transaction, $property, $value);
        }

        return false;
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

        return (bool) property_exists($value, $lastPart);
    }

    private function validateArrayRule(IngTransaction $transaction, array $rule): bool
    {
        if (count($rule) !== 1) {
            return false;
        }

        $rules = current($rule);

        if (!is_array($rules)) {
            return false;
        }

        $function = key($rule);

        if (!in_array($function, self::ARRAY_RULES, true)) {
            return false;
        }

        return $this->{$function}($transaction, ...$rules);
    }
}
