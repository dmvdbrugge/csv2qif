<?php

namespace Csv2Qif\RuleSet\Rules;

use Csv2Qif\RuleSet\Rules\Rules\Rule;

class ParsedRule
{
    /** @var string */
    private $class;

    /** @var bool */
    private $not;

    /** @var string */
    private $property;

    /** @var string */
    private $origin;

    /** @var Rule[] */
    private $rules;

    /** @var mixed */
    private $value;

    private function __construct()
    {
        // Use the forRule* methods
    }

    public static function forRuleArray(string $class, string $origin, Rule ...$rules): self
    {
        $parsed = new self();

        $parsed->class    = $class;
        $parsed->not      = false;
        $parsed->origin   = $origin;
        $parsed->property = '';
        $parsed->rules    = $rules;

        return $parsed;
    }

    public static function forRuleString(string $class, bool $negate, string $origin, string $property, $value): self
    {
        $parsed = new self();

        $parsed->class    = $class;
        $parsed->not      = $negate;
        $parsed->origin   = $origin;
        $parsed->property = $property;
        $parsed->rules    = [];
        $parsed->value    = $value;

        return $parsed;
    }

    public function getClass(): string
    {
        return $this->class;
    }

    public function isNot(): bool
    {
        return $this->not;
    }

    public function getOrigin(): string
    {
        return $this->origin;
    }

    public function getProperty(): string
    {
        return $this->property;
    }

    /**
     * @return Rule[]
     */
    public function getRules(): array
    {
        return $this->rules;
    }

    public function getValue()
    {
        return $this->value;
    }
}
