<?php

namespace Csv2Qif\File;

use StephenHarris\QIF;

use function fwrite;

use const PHP_EOL;

class QifWriter extends File
{
    /**
     * @param iterable|QIF\Transaction[] $transactions
     */
    public function writeTransactions(iterable $transactions): void
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
