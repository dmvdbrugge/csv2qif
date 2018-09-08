<?php

namespace RuleSet\Rules;

use Parable\DI\Container;
use RuleSet\Rules\Rules\Rule;
use RuleSet\Rules\Rules\RuleHasProperty;
use RuleSet\Rules\Rules\RuleHasRules;
use RuleSet\Rules\Rules\RuleHasValue;
use RuleSet\Rules\Rules\RuleNot;

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
