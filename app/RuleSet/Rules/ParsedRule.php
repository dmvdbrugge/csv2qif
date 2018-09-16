<?php

namespace Csv2Qif\RuleSet\Rules;

use Csv2Qif\RuleSet\Rules\Rules\Rule;
use Csv2Qif\RuleSet\Rules\Rules\RuleInvalid;
use Exception;

use function array_keys;
use function implode;
use function is_array;
use function is_string;

class ParsedRule
{
    /** @var string */
    private $class;

    /** @var bool */
    private $not;

    /** @var string */
    private $origin;

    /** @var string */
    private $property;

    /** @var string */
    private $reason;

    /** @var Rule[] */
    private $rules;

    /** @var mixed */
    private $value;

    private function __construct()
    {
        // Use the factory methods
    }

    public static function forRuleArray(string $class, string $origin, Rule ...$rules): self
    {
        $parsed = new self();

        $parsed->class    = $class;
        $parsed->not      = false;
        $parsed->origin   = $origin;
        $parsed->property = '';
        $parsed->reason   = '';
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
        $parsed->reason   = '';
        $parsed->rules    = [];
        $parsed->value    = $value;

        return $parsed;
    }

    public static function fromException(Exception $exception, $origin): self
    {
        if (is_array($origin)) {
            $origin = implode(', ', array_keys($origin));
        } elseif (!is_string($origin)) {
            $origin = '';
        }

        // Using already existing factory method with the least parameters ;)
        $parsed = self::forRuleArray(RuleInvalid::class, $origin);

        $parsed->reason = $exception->getMessage();

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

    public function getReason(): string
    {
        return $this->reason;
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
