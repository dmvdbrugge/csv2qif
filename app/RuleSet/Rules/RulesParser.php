<?php

namespace Csv2Qif\RuleSet\Rules;

use Csv2Qif\RuleSet\Rules\Exceptions\RulesParserException;

use function array_key_exists;
use function array_map;
use function array_shift;
use function count;
use function current;
use function explode;
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

    /**
     * @param array|string $rule
     *
     * @throws RulesParserException
     */
    public static function parse($rule): ParsedRule
    {
        if (is_array($rule)) {
            return self::parseRuleArray($rule);
        }

        if (is_string($rule)) {
            return self::parseRuleString($rule);
        }

        throw RulesParserException::invalidRuleType($rule);
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
            throw RulesParserException::ruleArrayCountNotOne($count);
        }

        $ruleName   = key($rule);
        $normalized = self::normalize(...self::stringToParts($ruleName));

        if (!self::isRuleArrayRule($normalized)) {
            throw RulesParserException::ruleArrayIsNotArrayRule($ruleName);
        }

        $rules = current($rule);

        if (!is_array($rules)) {
            throw RulesParserException::ruleArrayIsNotArray($rules);
        }

        $mapper = function ($rule): Rules\Rule {
            return RulesFactory::create($rule);
        };

        return ParsedRule::forRuleArray(
            self::NORMALIZED_RULE_MAP[$normalized],
            $ruleName,
            ...array_map($mapper, $rules)
        );
    }

    private static function parseRuleString(string $rule): ParsedRule
    {
        $negate     = false;
        $ruleParts  = self::stringToParts($rule);
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
                throw RulesParserException::ruleStringIsNotStringRule($rule, self::partsToString($ruleName));
            }

            $ruleName[] = $ruleNamePart;
            $normalized = self::normalize(...$ruleName);
        }

        if (self::isRuleArrayRule($normalized)) {
            throw RulesParserException::ruleStringIsArrayRule(self::partsToString($ruleName));
        }

        return ParsedRule::forRuleString(
            self::NORMALIZED_RULE_MAP[$normalized],
            $negate,
            $rule,
            $property,
            self::partsToString($ruleParts)
        );
    }

    private static function partsToString(array $parts): string
    {
        return implode(' ', $parts);
    }

    /**
     * @return string[]
     */
    private static function stringToParts(string $rule): array
    {
        return explode(' ', $rule);
    }
}
