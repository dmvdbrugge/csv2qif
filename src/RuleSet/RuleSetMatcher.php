<?php

namespace Csv2Qif\RuleSet;

use Csv2Qif\Event\Hook;
use Csv2Qif\RuleSet\Description\DescriptionMatcher;
use Csv2Qif\RuleSet\Rules\Rules\RuleAllOf;
use Csv2Qif\Transactions\IngTransaction;

use function is_array;
use function str_replace;

class RuleSetMatcher
{
    public const MATCH_FOUND    = 'RuleSetMatcher::matchFound';
    public const MATCH_FALLBACK = 'RuleSetMatcher::matchFallback';

    /** @var RuleSetConfig */
    private $config;

    /** @var DescriptionMatcher */
    private $description;

    /** @var Hook */
    private $hook;

    public function __construct(DescriptionMatcher $description, Hook $hook)
    {
        $this->config      = new RuleSetConfig();
        $this->description = $description;
        $this->hook        = $hook;
    }

    public function setRuleSet(string $ruleSet): void
    {
        $this->config->setRuleSet($ruleSet);
    }

    /**
     * @return string[] Tuple of [$transfer, $description]
     */
    public function match(IngTransaction $transaction): array
    {
        /** @var array $matchers */
        $matchers = $this->config->get('matchers', []);

        foreach ($matchers as $name => $matcher) {
            /** @var RuleAllOf $allOf */
            $allOf = $matcher['rules'];

            if ($allOf->match($transaction)) {
                $this->hook->trigger(self::MATCH_FOUND, $name);

                $description = $matcher['description'] ?? ['defaultDescription'];
                $description = is_array($description)
                    ? $this->description->match($transaction, $description)
                    : $description;

                $transfer    = str_replace('/', '', $matcher['transfer'] ?? '');
                $description = str_replace('/', '', $description);

                return [$transfer, $description];
            }
        }

        if ($this->config->get('fallback') ?? true) {
            $parent = $transaction->amount > 0 ? 'Income' : 'Expenses';

            $this->hook->trigger(self::MATCH_FALLBACK, $parent);
            $transfer    = str_replace('/', '', "Unknown:{$parent}:{$transaction->description}");
            $description = str_replace('/', '', $this->description->defaultDescription($transaction));

            return [$transfer, $description];
        }

        return ['', ''];
    }
}
