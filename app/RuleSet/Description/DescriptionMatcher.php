<?php

namespace Csv2Qif\RuleSet\Description;

use Csv2Qif\Transactions\IngTransaction;

use function array_shift;
use function intval;
use function ltrim;
use function preg_match;

class DescriptionMatcher implements DescriptionEngine
{
    private const GVE_REGEX = [
        '/^8(1?\d{2})(\d{6})\d{6}$/',
        '/^\d((5|6)\d{2})(\d{6})\d{6}$/',
    ];

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
        if (preg_match(self::GVE_REGEX[0], $transaction->notes->description, $matches)) {
            $project = ltrim($matches[2], '0');
            $period  = ltrim($matches[1], '0');
        } elseif (preg_match(self::GVE_REGEX[1], $transaction->notes->description, $matches)) {
            $project = ltrim($matches[3], '0');
            $period  = intval(ltrim($matches[1], '0'), 10) - 500;
        } else {
            return $this->getNoteDescription($transaction);
        }

        return "{$project}: {$period}";
    }
}
