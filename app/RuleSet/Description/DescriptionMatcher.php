<?php

namespace RuleSet\Description;

use Transactions\IngTransaction;

class DescriptionMatcher implements DescriptionEngine
{
    public function match(IngTransaction $transaction, array $descriptionFunction): string
    {
        $function = array_shift($descriptionFunction);

        return $this->{$function}($transaction, ...$descriptionFunction);
    }

    public function defaultDescription(IngTransaction $transaction): string
    {
        return "{$transaction->description}: {$this->getNoteDescription($transaction)}";
    }

    public function getNoteDescription(IngTransaction $transaction): string
    {
        return $transaction->notes->description ?: $transaction->notes->source;
    }

    public function geldvoorelkaarInstallment(IngTransaction $transaction): string
    {
        $regex = '/^8(\d{2,3})(\d{6})\d{6}$/';

        if (!preg_match($regex, $transaction->notes->description, $matches)) {
            return $this->getNoteDescription($transaction);
        }

        $project = ltrim($matches[2], '0');
        $period  = ltrim($matches[1], '0');

        return "{$project}: {$period}";
    }
}
