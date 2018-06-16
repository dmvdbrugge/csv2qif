<?php

namespace File;

use StephenHarris\QIF;
use Traversable;

use function fwrite;

use const PHP_EOL;

class QifWriter extends File
{
    /**
     * @param Traversable|QIF\Transaction[] $transactions
     */
    public function writeTransactions(Traversable $transactions): void
    {
        $this->open(self::MODE_WRITE);

        foreach ($transactions as $transaction) {
            $this->writeTransaction($transaction);
        }

        $this->close();
    }

    private function writeTransaction(QIF\Transaction $transaction): void
    {
        fwrite($this->handle, (string) $transaction . PHP_EOL);
    }
}
