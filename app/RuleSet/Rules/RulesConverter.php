<?php

namespace RuleSet\Rules;

use Transactions\IngTransaction;

use function array_shift;
use function explode;

/**
 * Pretends to implement RulesEngine.
 */
class RulesConverter
{
    public function allOf(IngTransaction $transaction, ...$rules): array
    {
        $result = ['allOf' => []];

        foreach ($rules as $rule) {
            $result['allOf'][] = $this->convertRule($transaction, $rule);
        }

        return $result;
    }

    public function oneOf(IngTransaction $transaction, ...$rules): array
    {
        $result = ['oneOf' => []];

        foreach ($rules as $rule) {
            $result['oneOf'][] = $this->convertRule($transaction, $rule);
        }

        return $result;
    }

    public function contains(IngTransaction $transaction, $property, $value): string
    {
        return "{$property} contains {$value}";
    }

    public function equals(IngTransaction $transaction, $property, $value): string
    {
        return "{$property} equals {$value}";
    }

    public function greaterThan(IngTransaction $transaction, $property, $value): string
    {
        return "{$property} greaterThan {$value}";
    }

    public function isEmpty(IngTransaction $transaction, $property): string
    {
        return "{$property} isEmpty";
    }

    public function lessThan(IngTransaction $transaction, $property, $value): string
    {
        return "{$property} lessThan {$value}";
    }

    protected function not(IngTransaction $transaction, ...$arguments): string
    {
        $result        = $this->convertRule($transaction, $arguments);
        [$head, $tail] = explode(' ', $result, 2);

        return "{$head} not {$tail}";
    }

    /**
     * @return array|string
     */
    private function convertRule(IngTransaction $transaction, array $rule)
    {
        $function = array_shift($rule);

        return $this->{$function}($transaction, ...$rule);
    }
}
