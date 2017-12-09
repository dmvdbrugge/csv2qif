<?php

namespace Transactions\Transformers;

use RuleSet\RuleSetMatcher;
use RuleSet\RuleSetValidator;
use StephenHarris\QIF;
use Transactions\IngTransaction;

class IngToQif
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

    public function transform(IngTransaction $transaction): QIF\Transaction
    {
        [$transfer, $description] = $this->matcher->match($transaction);
        $qif = new QIF\Transaction(QIF\Transaction::BANK);

        return $qif->setAmount($transaction->amount)
            ->setCategory($transfer)
            ->setDate($transaction->date)
            ->setDescription($description);
    }

    /**
     * @param \Traversable|IngTransaction[] $transactions
     *
     * @return \Traversable|QIF\Transaction[]
     */
    public function transformAll(\Traversable $transactions): \Traversable
    {
        foreach ($transactions as $transaction) {
            yield $this->transform($transaction);
        }
    }
}
