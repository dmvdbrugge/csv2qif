<?php

namespace File;

use StephenHarris\QIF;
use Transactions\GnuCashTransaction;
use Transactions\Transformers\GnuCashToQif;

class QifWriter extends Base
{
    /** @var GnuCashToQif */
    private $transformer;

    public function __construct(GnuCashToQif $transformer)
    {
        $this->transformer = $transformer;
    }

    /**
     * @param \Traversable|GnuCashTransaction[] $transactions
     */
    public function writeTransactions(\Traversable $transactions)
    {
        $this->open(self::MODE_WRITE);

        foreach ($transactions as $transaction) {
            $this->writeTransaction($transaction);
        }

        $this->close();
    }

    private function writeTransaction(GnuCashTransaction $transaction): void
    {
        fwrite($this->handle, $this->transformer->transform($transaction) . PHP_EOL);
    }
}
