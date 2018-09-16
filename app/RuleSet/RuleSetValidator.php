<?php

namespace Csv2Qif\RuleSet;

use Csv2Qif\Event\Hook;
use Csv2Qif\RuleSet\Description\DescriptionValidator;
use Csv2Qif\RuleSet\Exceptions\RuleSetConfigException;
use Csv2Qif\RuleSet\Rules\Rules\RuleAllOf;
use Csv2Qif\Transactions\IngTransaction;
use Generator;
use Parable\DI\Container;

use function is_array;
use function is_string;

class RuleSetValidator
{
    public const VALIDATE_ERROR         = 'RuleSetValidator::error';
    public const VALIDATE_MATCHER_START = 'RuleSetValidator::matcherStart';
    public const VALIDATE_MATCHER_VALID = 'RuleSetValidator::matcherValid';
    public const VALIDATE_RULE_ERROR    = 'RuleSetValidator::ruleError';

    /** @var RuleSetConfig */
    private $config;

    /** @var DescriptionValidator */
    private $description;

    /** @var Hook */
    private $hook;

    public function __construct(DescriptionValidator $description, Hook $hook)
    {
        $this->config      = Container::create(RuleSetConfig::class);
        $this->description = $description;
        $this->hook        = $hook;
    }

    /**
     * Answers the simple question: is the given ruleset valid (nothing happens) or not (Exception).
     *
     * @throws \Exception when invalid
     */
    public function validate(string $ruleSet): void
    {
        foreach ($this->getValidateGenerator($ruleSet) as $message) {
            throw new \Exception($message);
        }
    }

    public function validateAll(string $ruleSet): int
    {
        $errorCount = 0;

        foreach ($this->getValidateGenerator($ruleSet) as $message) {
            $this->hook->trigger(self::VALIDATE_ERROR, $message);
            $errorCount++;
        }

        return $errorCount;
    }

    /**
     * @return Generator|string[]
     */
    private function getValidateGenerator(string $ruleSet): Generator
    {
        try {
            $this->config->setRuleSet($ruleSet);
        } catch (RuleSetConfigException $e) {
            yield $e->getMessage();

            return;
        }

        $fakeTransaction        = new IngTransaction();
        $fakeTransaction->notes = new IngTransaction\Notes('Fake notes ;)');

        /** @var array $matchers */
        $matchers = $this->config->get('matchers', []);

        foreach ($matchers as $name => $matcher) {
            $this->hook->trigger(self::VALIDATE_MATCHER_START, $name);

            /** @var RuleAllOf $allOf */
            $allOf = $matcher['rules'];
            $valid = true;

            if (!$allOf->validate($fakeTransaction)) {
                yield "Matcher {$name} is invalid: invalid rules.";
                $valid = false;
            }

            $transfer = $matcher['transfer'] ?? '';

            if (!is_string($transfer)) {
                yield "Matcher {$name} is invalid: invalid transfer.";
                $valid = false;
            }

            $description = $matcher['description'] ?? '';

            if (
                (!is_array($description) && !is_string($description))
                || (is_array($description) && !$this->description->validate($fakeTransaction, $description))
            ) {
                yield "Matcher {$name} is invalid: invalid description.";
                $valid = false;
            }

            if ($valid) {
                $this->hook->trigger(self::VALIDATE_MATCHER_VALID, $name);
            }
        }
    }
}
