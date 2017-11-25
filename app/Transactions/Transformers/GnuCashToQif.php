<?php

namespace Transactions\Transformers;

use StephenHarris\QIF;
use Transactions\GnuCashTransaction;

class GnuCashToQif
{
    public function transform(GnuCashTransaction $transaction): string
    {
        return (string)(new QIF\Transaction(QIF\Transaction::BANK))
            ->setAmount($transaction->amount)
            ->setCategory($transaction->transfer)
            ->setDate($transaction->date)
            ->setDescription($transaction->description);
    }
}
