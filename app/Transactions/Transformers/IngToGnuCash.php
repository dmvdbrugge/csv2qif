<?php

namespace Transactions\Transformers;

use Parable\Event\Hook;
use Parable\Framework\Config;
use Transactions\GnuCashTransaction;
use Transactions\IngTransaction;
use Transactions\RulesMatcher;
use Transactions\RulesValidator;

class IngToGnuCash
{
    const MATCH_FOUND    = 'IngToGnuCash::matchFound';
    const MATCH_FALLBACK = 'IngToGnuCash::matchFallback';

    /** @var Config */
    private $config;

    /** @var Hook */
    private $hook;

    /** @var RulesMatcher */
    private $rulesMatcher;

    /** @var RulesValidator */
    private $rulesValidator;

    /** @var string */
    private $ruleSet = '';

    public function __construct(Config $config, Hook $hook, RulesMatcher $rulesMatcher, RulesValidator $rulesValidator)
    {
        $this->config         = $config;
        $this->hook           = $hook;
        $this->rulesMatcher   = $rulesMatcher;
        $this->rulesValidator = $rulesValidator;
    }

    public function setRuleSet(string $ruleSet): void
    {
        if (!empty($ruleSet) && !is_array($this->config->get("csv2qif.{$ruleSet}"))) {
            throw new \Exception("Ruleset {$ruleSet} doesn't exist.");
        }

        $fakeTransaction = new IngTransaction();
        $fakeTransaction->notes = new IngTransaction\Notes('Fake notes ;)');

        foreach ($this->config->get("csv2qif.{$ruleSet}.matchers", []) as $name => $matcher) {
            $rules = $matcher['rules'] ?? null;

            if ($rules === null || !$this->rulesValidator->allOf($fakeTransaction, ...$rules)) {
                throw new \Exception("Matcher {$name} in ruleset {$ruleSet} is invalid.");
            }
        }

        $this->ruleSet = $ruleSet;
    }

    public function transform(IngTransaction $transaction): GnuCashTransaction
    {
        $gnuCash = new GnuCashTransaction();

        $gnuCash->amount = $transaction->amount;
        $gnuCash->date   = $transaction->date;

        return $this->tryMatchTransferAndDescription($transaction, $gnuCash);
    }

    /**
     * @param \Traversable|IngTransaction[] $transactions
     *
     * @return \Traversable|GnuCashTransaction[]
     */
    public function transformAll(\Traversable $transactions): \Traversable
    {
        foreach ($transactions as $transaction) {
            yield $this->transform($transaction);
        }
    }

    private function tryMatchTransferAndDescription(
        IngTransaction $ing,
        GnuCashTransaction $gnuCash
    ): GnuCashTransaction {
        $match = false;

        foreach ($this->config->get("csv2qif.{$this->ruleSet}.matchers", []) as $name => $matcher) {
            if ($this->rulesMatcher->allOf($ing, ...$matcher['rules'])) {
                $this->hook->trigger(self::MATCH_FOUND, $name);

                $match       = true;
                $description = $matcher['description'] ?? ['getNoteDescription'];
                $description = is_array($description)
                    ? $this->getDescriptionFromFunction($ing, $description)
                    : $description;

                $gnuCash->transfer    = str_replace('/', '', $matcher['transfer']);
                $gnuCash->description = str_replace('/', '', $description);
                break;
            }
        }

        if (!$match && ($this->config->get("csv2qif.{$this->ruleSet}.fallback") ?? true)) {
            $parent = $ing->amount > 0 ? 'Income' : 'Expenses';

            $this->hook->trigger(self::MATCH_FALLBACK, $parent);
            $gnuCash->transfer    = str_replace('/', '', "Unknown:{$parent}:{$ing->description}");
            $gnuCash->description = str_replace('/', '', $ing->notes->source);
        }

        return $gnuCash;
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
