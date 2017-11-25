<?php

namespace Transactions;

class GnuCashTransaction
{
    /** @var float */
    public $amount;

    /** @var \DateTime */
    public $date;

    /** @var string */
    public $description;

    /** @var string */
    public $transfer;
}
