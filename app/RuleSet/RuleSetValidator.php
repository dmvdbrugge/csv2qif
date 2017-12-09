<?php

namespace RuleSet;

use Parable\Framework\Config;
use RuleSet\Rules\RulesValidator;
use Transactions\IngTransaction;

class RuleSetValidator
{
    // TODO: Validate entire ruleset instead of only its matchers' rules
    // TODO: Add option to validate everything instead of bailing on first fail
    // TODO: Hooks?

    /** @var Config */
    private $config;

    /** @var RulesValidator */
    private $validator;

    public function __construct(Config $config, RulesValidator $validator)
    {
        $this->config = $config;
        $this->validator = $validator;
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

            if ($rules === null || !$this->validator->allOf($fakeTransaction, ...$rules)) {
                throw new \Exception("Matcher {$name} in ruleset {$ruleSet} is invalid.");
            }
        }
    }
}
