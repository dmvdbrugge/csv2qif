<?php

namespace Csv2Qif\RuleSet\Rules\Exceptions;

use Exception;

use function gettype;

class RulesParserException extends Exception
{
    public static function invalidRuleType($rule): self
    {
        return new self("Rule is neither array nor string but '" . gettype($rule) . "' instead.");
    }

    public static function ruleArrayCountNotOne(int $count): self
    {
        return new self("Rule array count '{$count}' is not 1.");
    }

    public static function ruleArrayIsNotArray($rules): self
    {
        return new self("Content type of rule array should be array, got '" . gettype($rules) . "' instead.");
    }

    public static function ruleArrayIsNotArrayRule(string $ruleName): self
    {
        return new self("Rule '{$ruleName}' is not an array rule.");
    }

    public static function ruleStringIsArrayRule(string $ruleName): self
    {
        return new self("Rule array rule '{$ruleName}' can't be part of a string rule.");
    }

    public static function ruleStringIsNotStringRule(string $origin, string $rulePart): self
    {
        return new self("Could not parse '{$origin}', no valid rule in '{$rulePart}'.");
    }
}
