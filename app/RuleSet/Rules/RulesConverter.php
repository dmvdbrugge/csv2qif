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
        $result = ['all of' => []];

        foreach ($rules as $rule) {
            $result['all of'][] = $this->convertRule($transaction, $rule);
        }

        return $result;
    }

    public function oneOf(IngTransaction $transaction, ...$rules): array
    {
        $result = ['one of' => []];

        foreach ($rules as $rule) {
            $result['one of'][] = $this->convertRule($transaction, $rule);
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
        return "{$property} is greater than {$value}";
    }

    public function isEmpty(IngTransaction $transaction, $property): string
    {
        return "{$property} is empty";
    }

    public function lessThan(IngTransaction $transaction, $property, $value): string
    {
        return "{$property} is less than {$value}";
    }

    protected function not(IngTransaction $transaction, ...$arguments): string
    {
        $result = $this->convertRule($transaction, $arguments);

        [$head, $is_or_tail, $tail] = explode(' ', $result, 3);

        if ($is_or_tail === 'is') {
            $head = "{$head} is";
        } else {
            $tail = "{$is_or_tail} {$tail}";
        }

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
