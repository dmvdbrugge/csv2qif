<?php

namespace Csv2Qif\RuleSet\Rules;

use BadMethodCallException;
use Csv2Qif\RuleSet\Rules\Exceptions\RulesParserException;
use Csv2Qif\RuleSet\Rules\Rules\Rule;
use Csv2Qif\RuleSet\Rules\Rules\RuleHasProperty;
use Csv2Qif\RuleSet\Rules\Rules\RuleHasReason;
use Csv2Qif\RuleSet\Rules\Rules\RuleHasRules;
use Csv2Qif\RuleSet\Rules\Rules\RuleHasValue;
use Csv2Qif\RuleSet\Rules\Rules\RuleNot;
use Parable\Di\Container;

class RulesFactory
{
    /** @var Container */
    private static $container;

    public static function setContainer(Container $container): void
    {
        self::$container = $container;
    }

    /**
     * @param array|string $ruleConfig
     */
    public static function create($ruleConfig): Rule
    {
        if (!self::$container instanceof Container) {
            throw new BadMethodCallException('RulesFactory not properly initiated, container missing.');
        }

        try {
            $parsed = RulesParser::parse($ruleConfig);
        } catch (RulesParserException $e) {
            $parsed = ParsedRule::fromException($e, $ruleConfig);
        }

        /** @var Rule $rule */
        $rule = self::$container->build($parsed->getClass());
        $rule->setOrigin($parsed->getOrigin());

        if ($rule instanceof RuleHasReason) {
            $rule->setReason($parsed->getReason());
        }

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
            $not = self::$container->build(RuleNot::class);
            $not->setOrigin($parsed->getOrigin());
            $not->setRules($rule);

            return $not;
        }

        return $rule;
    }
}
