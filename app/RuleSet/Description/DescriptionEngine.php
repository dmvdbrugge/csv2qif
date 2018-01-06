<?php

namespace RuleSet\Description;

use Transactions\IngTransaction;

interface DescriptionEngine
{
    /**
     * @param IngTransaction $transaction
     *
     * @return string|bool String for matching, bool for validating
     */
    public function defaultDescription(IngTransaction $transaction);

    /**
     * @param IngTransaction $transaction
     *
     * @return string|bool String for matching, bool for validating
     */
    public function getNoteDescription(IngTransaction $transaction);

    /**
     * @param IngTransaction $transaction
     *
     * @return string|bool String for matching, bool for validating
     */
    public function geldvoorelkaarInstallment(IngTransaction $transaction);
}
