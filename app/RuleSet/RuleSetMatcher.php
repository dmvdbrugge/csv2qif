<?php

namespace RuleSet;

use Parable\Event\Hook;
use Parable\Framework\Config;
use Transactions\IngTransaction;

class RuleSetMatcher
{
    const MATCH_FOUND    = 'RuleSetMatcher::matchFound';
    const MATCH_FALLBACK = 'RuleSetMatcher::matchFallback';

    /** @var Config */
    private $config;

    /** @var Hook */
    private $hook;

    /** @var RulesMatcher */
    private $matcher;

    /** @var string */
    private $ruleSet = '';

    public function __construct(Config $config, Hook $hook, RulesMatcher $matcher)
    {
        $this->config  = $config;
        $this->hook    = $hook;
        $this->matcher = $matcher;
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
            if ($this->matcher->allOf($transaction, ...$matcher['rules'])) {
                $this->hook->trigger(self::MATCH_FOUND, $name);

                $description = $matcher['description'] ?? ['getNoteDescription'];
                $description = is_array($description)
                    ? $this->getDescriptionFromFunction($transaction, $description)
                    : $description;

                $transfer    = str_replace('/', '', $matcher['transfer']);
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

    private function getDescriptionFromFunction(IngTransaction $transaction, array $descriptionFunction): string
    {
        $function = array_shift($descriptionFunction);

        return $this->{$function}($transaction, ...$descriptionFunction);
    }

    private function getNoteDescription(IngTransaction $transaction): string
    {
        return $transaction->notes->description ?: $transaction->notes->source;
    }

    /**
     * @see getDescriptionFromFunction This method is not unused.
     */
    private function geldvoorelkaarInstallment(IngTransaction $transaction): string
    {
        $regex = '/^8(\d{2,3})(\d{6})\d{6}$/';

        if (!preg_match($regex, $transaction->notes->description, $matches)) {
            return $this->getNoteDescription($transaction);
        }

        $project = ltrim($matches[2], '0');
        $period  = ltrim($matches[1], '0');

        return "{$project}: {$period}";
    }
}
