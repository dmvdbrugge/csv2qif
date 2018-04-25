<?php

namespace Transactions;

use DateTime;
use Transactions\IngTransaction\Notes;

use function str_replace;
use function strtolower;

class IngTransaction
{
    /** @var DateTime */
    public $date;

    /** @var string */
    public $description;

    /** @var string */
    public $account;

    /** @var string */
    public $transfer;

    /** @var string */
    public $code;

    /** @var float */
    public $amount;

    /** @var string */
    public $mutation;

    /** @var Notes */
    public $notes;

    public static function fromIngCsv(
        string $date,
        string $description,
        string $account,
        string $transfer,
        string $code,
        string $af_bij,
        string $amount,
        string $mutation,
        string $notes
    ): self {
        $transaction = new self;

        $transaction->date        = DateTime::createFromFormat('Ymd|', $date);
        $transaction->description = $description;
        $transaction->account     = $account;
        $transaction->transfer    = $transfer;
        $transaction->code        = $code;
        $transaction->amount      = (float) str_replace(',', '.', str_replace('.', '', $amount));
        $transaction->mutation    = $mutation;
        $transaction->notes       = new Notes($notes);

        if (strtolower($af_bij) === 'af') {
            $transaction->amount *= -1;
        }

        return $transaction;
    }
}
