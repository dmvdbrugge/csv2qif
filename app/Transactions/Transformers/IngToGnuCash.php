<?php

namespace Transactions\Transformers;

use Transactions\GnuCashTransaction;
use Transactions\IngTransaction;
use RuleSet\RuleSetMatcher;
use RuleSet\RuleSetValidator;

class IngToGnuCash
{
    /** @var RuleSetMatcher */
    private $matcher;

    /** @var RuleSetValidator */
    private $validator;

    public function __construct(RuleSetMatcher $matcher, RuleSetValidator $validator)
    {
        $this->matcher   = $matcher;
        $this->validator = $validator;
    }

    public function setRuleSet(string $ruleSet): void
    {
        $this->validator->validate($ruleSet);
        $this->matcher->setRuleSet($ruleSet);
    }

    public function transform(IngTransaction $transaction): GnuCashTransaction
    {
        $gnuCash = new GnuCashTransaction();

        [$transfer, $description] = $this->matcher->match($transaction);

        $gnuCash->amount      = $transaction->amount;
        $gnuCash->date        = $transaction->date;
        $gnuCash->transfer    = $transfer;
        $gnuCash->description = $description;

        return $gnuCash;
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
}
