<?php

namespace Csv2Qif\RuleSet\Rules;

use Csv2Qif\RuleSet\Rules\Rules\Rule;
use Csv2Qif\RuleSet\Rules\Rules\RuleHasProperty;
use Csv2Qif\RuleSet\Rules\Rules\RuleHasRules;
use Csv2Qif\RuleSet\Rules\Rules\RuleHasValue;
use Csv2Qif\RuleSet\Rules\Rules\RuleNot;
use Parable\DI\Container;

class RulesFactory
{
    /**
     * @param array|string $ruleConfig
     */
    public static function create($ruleConfig): Rule
    {
        $parsed = RulesParser::parse($ruleConfig);

        /** @var Rule $rule */
        $rule = Container::create($parsed->getClass());
        $rule->setOrigin($parsed->getOrigin());

        if ($rule instanceof RuleHasRules) {
            $rule->setRules(...$parsed->getRules());

            return $rule;
        }

        if ($rule instanceof RuleHasProperty) {
            $rule->setProperty($parsed->getProperty());
        }

        if ($rule instanceof RuleHasValue) {
            $rule->setValue($parsed->getValue());
        }

        if ($parsed->isNot()) {
            $not = Container::create(RuleNot::class);
            $not->setOrigin($parsed->getOrigin());
            $not->setRules($rule);

            return $not;
        }

        return $rule;
    }
}
