<?php

namespace Transactions;

use Generator;
use RuleSet\RuleSetMatcher;
use RuleSet\RuleSetValidator;
use StephenHarris\QIF;

class Transformer
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
     * @param iterable|IngTransaction[] $transactions
     *
     * @return Generator|QIF\Transaction[]
     */
    public function transformAll(iterable $transactions): Generator
    {
        foreach ($transactions as $transaction) {
            yield $this->transform($transaction);
        }
    }
}
