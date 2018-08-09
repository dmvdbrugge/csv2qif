<?php

namespace RuleSet\Description;

use Transactions\IngTransaction;

interface DescriptionEngine
{
    /**
     * @return bool|string String for matching, bool for validating
     */
    public function defaultDescription(IngTransaction $transaction);

    /**
     * @return bool|string String for matching, bool for validating
     */
    public function getNoteDescription(IngTransaction $transaction);

    /**
     * @return bool|string String for matching, bool for validating
     */
    public function geldvoorelkaarInstallment(IngTransaction $transaction);
}
