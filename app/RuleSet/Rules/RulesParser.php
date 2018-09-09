<?php

namespace Csv2Qif\RuleSet\Rules;

use InvalidArgumentException;

use function array_key_exists;
use function array_map;
use function array_shift;
use function count;
use function current;
use function explode;
use function gettype;
use function implode;
use function in_array;
use function is_array;
use function is_string;
use function strtolower;

class RulesParser
{
    /**
     * 'not' is a special case thus not in the map.
     */
    private const NORMALIZED_RULE_MAP = [
        'allof'       => Rules\RuleAllOf::class,
        'contains'    => Rules\RuleContains::class,
        'equals'      => Rules\RuleEquals::class,
        'greaterthan' => Rules\RuleGreaterThan::class,
        'empty'       => Rules\RuleIsEmpty::class,
        'lessthan'    => Rules\RuleLessThan::class,
        'oneof'       => Rules\RuleOneOf::class,
    ];

    /**
     * While 'not' implements HasRules, it is again a special
     * case and thus also not in this list.
     */
    private const RULE_ARRAY_RULES = [
        'allof',
        'oneof',
    ];

    public static function parse($rule): ParsedRule
    {
        if (is_array($rule)) {
            return self::parseRuleArray($rule);
        }

        if (is_string($rule)) {
            return self::parseRuleString($rule);
        }

        throw new InvalidArgumentException(
            "Rule is neither array nor string but '" . gettype($rule) . "' instead."
        );
    }

    private static function isRuleArrayRule(string $normalizedRule): bool
    {
        return in_array($normalizedRule, self::RULE_ARRAY_RULES, true);
    }

    private static function normalize(string ...$rule): string
    {
        return strtolower(implode('', $rule));
    }

    private static function parseRuleArray(array $rule): ParsedRule
    {
        $count = count($rule);

        if ($count !== 1) {
            throw new InvalidArgumentException("Rule array count {$count} is not 1");
        }

        $origin     = key($rule);
        $normalized = self::normalize(...explode(' ', $origin));

        if (!self::isRuleArrayRule($normalized)) {
            throw new InvalidArgumentException("Rule {$normalized} is not an array rule");
        }

        $rules = current($rule);

        if (!is_array($rules)) {
            throw new InvalidArgumentException('Content of rule array is not an array');
        }

        $mapper = function ($rule): Rules\Rule {
            return RulesFactory::create($rule);
        };

        return ParsedRule::forRuleArray(
            self::NORMALIZED_RULE_MAP[$normalized],
            $origin,
            ...array_map($mapper, $rules)
        );
    }

    private static function parseRuleString(string $rule): ParsedRule
    {
        $negate     = false;
        $ruleParts  = explode(' ', $rule);
        $property   = array_shift($ruleParts);
        $ruleName   = [array_shift($ruleParts)];
        $normalized = self::normalize(...$ruleName);

        if ($normalized === 'is') {
            $ruleName[0] = array_shift($ruleParts);
            $normalized  = self::normalize(...$ruleName);
        }

        if ($normalized === 'not') {
            $negate      = true;
            $ruleName[0] = array_shift($ruleParts);
            $normalized  = self::normalize(...$ruleName);
        }

        while (!array_key_exists($normalized, self::NORMALIZED_RULE_MAP)) {
            $ruleNamePart = array_shift($ruleParts);

            if ($ruleNamePart === null) {
                $imploded = implode(' ', $ruleName);

                throw new InvalidArgumentException(
                    "Could not parse '{$rule}', no valid test in '{$imploded}'"
                );
            }

            $ruleName[] = $ruleNamePart;
            $normalized = self::normalize(...$ruleName);
        }

        if (self::isRuleArrayRule($normalized)) {
            throw new InvalidArgumentException(
                "Rule array rule {$normalized} can't be part of a string rule"
            );
        }

        return ParsedRule::forRuleString(
            self::NORMALIZED_RULE_MAP[$normalized],
            $negate,
            $rule,
            $property,
            implode(' ', $ruleParts)
        );
    }
}
