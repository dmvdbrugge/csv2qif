<?php

namespace RuleSet;

use Parable\Framework\Config;
use RuleSet\Description\DescriptionValidator;
use RuleSet\Rules\RulesValidator;
use Transactions\IngTransaction;

class RuleSetValidator
{
    // TODO: Add option to validate everything instead of bailing on first fail
    // TODO: Hooks?

    /** @var Config */
    private $config;

    /** @var DescriptionValidator */
    private $description;

    /** @var RulesValidator */
    private $rules;

    public function __construct(Config $config, DescriptionValidator $description, RulesValidator $rules)
    {
        $this->config      = $config;
        $this->description = $description;
        $this->rules       = $rules;
    }

    /**
     * @param string $ruleSet
     *
     * @throws \Exception
     */
    public function validate(string $ruleSet): void
    {
        if (!empty($ruleSet) && !is_array($this->config->get("csv2qif.{$ruleSet}"))) {
            throw new \Exception("Ruleset {$ruleSet} doesn't exist.");
        }

        $fakeTransaction        = new IngTransaction();
        $fakeTransaction->notes = new IngTransaction\Notes('Fake notes ;)');

        foreach ($this->config->get("csv2qif.{$ruleSet}.matchers", []) as $name => $matcher) {
            $rules = $matcher['rules'] ?? null;

            if ($rules === null || !$this->rules->allOf($fakeTransaction, ...$rules)) {
                throw new \Exception("Matcher {$name} is invalid: no or invalid rules.");
            }

            $transfer = $matcher['transfer'] ?? '';

            if (!is_string($transfer)) {
                throw new \Exception("Matcher {$name} is invalid: invalid transfer.");
            }

            $description = $matcher['description'] ?? '';

            if (
                !is_array($description) && !is_string($description)
                || is_array($description) && !$this->description->validate($fakeTransaction, $description)
            ) {
                throw new \Exception("Matcher {$name} is invalid: invalid description.");
            }
        }
    }
}
