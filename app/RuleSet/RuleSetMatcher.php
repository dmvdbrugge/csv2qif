<?php

namespace RuleSet;

use Parable\Event\Hook;
use Parable\Framework\Config;
use RuleSet\Description\DescriptionMatcher;
use RuleSet\Rules\RulesMatcher;
use Transactions\IngTransaction;

class RuleSetMatcher
{
    const MATCH_FOUND    = 'RuleSetMatcher::matchFound';
    const MATCH_FALLBACK = 'RuleSetMatcher::matchFallback';

    /** @var Config */
    private $config;

    /** @var DescriptionMatcher */
    private $description;

    /** @var Hook */
    private $hook;

    /** @var RulesMatcher */
    private $rules;

    /** @var string */
    private $ruleSet = '';

    public function __construct(Config $config, DescriptionMatcher $description, Hook $hook, RulesMatcher $rules)
    {
        $this->config      = $config;
        $this->description = $description;
        $this->hook        = $hook;
        $this->rules       = $rules;
    }

    public function setRuleSet(string $ruleSet): void
    {
        if (!empty($ruleSet) && !is_array($this->config->get("csv2qif.{$ruleSet}"))) {
            throw new \Exception("Ruleset {$ruleSet} doesn't exist.");
        }

        $this->ruleSet = $ruleSet;
    }

    /**
     * @param IngTransaction $transaction
     *
     * @return string[] Tuple of [$transfer, $description]
     */
    public function match(IngTransaction $transaction): array
    {
        foreach ($this->config->get("csv2qif.{$this->ruleSet}.matchers", []) as $name => $matcher) {
            if ($this->rules->allOf($transaction, ...$matcher['rules'])) {
                $this->hook->trigger(self::MATCH_FOUND, $name);

                $description = $matcher['description'] ?? ['getNoteDescription'];
                $description = is_array($description)
                    ? $this->description->match($transaction, $description)
                    : $description;

                $transfer    = str_replace('/', '', $matcher['transfer'] ?? '');
                $description = str_replace('/', '', $description);

                return [$transfer, $description];
            }
        }

        if ($this->config->get("csv2qif.{$this->ruleSet}.fallback") ?? true) {
            $parent = $transaction->amount > 0 ? 'Income' : 'Expenses';

            $this->hook->trigger(self::MATCH_FALLBACK, $parent);
            $transfer    = str_replace('/', '', "Unknown:{$parent}:{$transaction->description}");
            $description = str_replace('/', '', $transaction->notes->source);

            return [$transfer, $description];
        }

        return ['', ''];
    }
}
