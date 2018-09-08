<?php

namespace RuleSet\Rules\Rules;

use Transactions\IngTransaction;

trait WithProperty
{
    /** @var string */
    private $property;

    public function setProperty(string $property): void
    {
        $this->property = $property;
    }

    private function getProperty(IngTransaction $transaction)
    {
        $propertyParts = explode('->', $this->property);
        $value         = $transaction;

        foreach ($propertyParts as $part) {
            $value = $value->{$part} ?? null;
        }

        return $value;
    }

    private function validateProperty(IngTransaction $transaction): bool
    {
        $propertyParts = explode('->', $this->property);
        $lastPart      = array_pop($propertyParts);
        $value         = $transaction;

        foreach ($propertyParts as $part) {
            if (!property_exists($value, $part)) {
                return false;
            }

            $value = $value->{$part};
        }

        return (bool) property_exists($value, $lastPart);
    }
}
