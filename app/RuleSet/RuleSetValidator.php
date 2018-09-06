<?php

namespace RuleSet;

use Event\Hook;
use Generator;
use Parable\DI\Container;
use RuleSet\Description\DescriptionValidator;
use RuleSet\Exceptions\RuleSetConfigException;
use RuleSet\Rules\RulesValidator;
use Transactions\IngTransaction;

use function is_array;
use function is_string;

class RuleSetValidator
{
    public const VALIDATE_ERROR         = 'RuleSetValidator::error';
    public const VALIDATE_MATCHER_START = 'RuleSetValidator::matcherStart';
    public const VALIDATE_MATCHER_VALID = 'RuleSetValidator::matcherValid';

    /** @var RuleSetConfig */
    private $config;

    /** @var DescriptionValidator */
    private $description;

    /** @var Hook */
    private $hook;

    /** @var RulesValidator */
    private $rules;

    public function __construct(DescriptionValidator $description, Hook $hook, RulesValidator $rules)
    {
        $this->config      = Container::create(RuleSetConfig::class);
        $this->description = $description;
        $this->hook        = $hook;
        $this->rules       = $rules;
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

            $valid = true;
            $rules = $matcher['rules'] ?? null;

            if ($rules === null || !$this->rules->allOf($fakeTransaction, ...$rules)) {
                yield "Matcher {$name} is invalid: no or invalid rules.";
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
