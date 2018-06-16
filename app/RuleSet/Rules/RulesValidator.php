<?php

namespace RuleSet\Rules;

use Event\Hook;
use ReflectionClass;
use Transactions\IngTransaction;

use function array_pop;
use function array_shift;
use function count;
use function explode;
use function is_numeric;
use function is_string;
use function property_exists;

class RulesValidator implements RulesEngine
{
    public const VALIDATE_ERROR = 'RulesValidator::error';

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
        $valid = true;

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
        $valid = true;

        foreach ($rules as $rule) {
            if (!$this->validateRule($transaction, $rule)) {
                $this->hook->trigger(self::VALIDATE_ERROR, $rule);
                $valid = false;
            }
        }

        return $valid;
    }

    public function not(IngTransaction $transaction, ...$arguments): bool
    {
        return $this->validateRule($transaction, $arguments);
    }

    private function validateRule(IngTransaction $transaction, array $rule): bool
    {
        $function = array_shift($rule);

        if (!$function || !$this->reflection->hasMethod($function)) {
            return false;
        }

        $method = $this->reflection->getMethod($function);

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

        return (bool) property_exists($value, $lastPart);
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
