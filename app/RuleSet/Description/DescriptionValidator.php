<?php

namespace RuleSet\Description;


use Transactions\IngTransaction;

class DescriptionValidator implements DescriptionEngine
{
    /** @var \ReflectionClass */
    private $reflection;

    public function __construct()
    {
        $this->reflection = new \ReflectionClass(DescriptionEngine::class);
    }

    public function validate(IngTransaction $transaction, array $descriptionFunction): bool
    {
        $function = array_shift($descriptionFunction);

        if (!$function || !$this->reflection->hasMethod($function)) {
            return false;
        }

        $method = $this->reflection->getMethod($function);

        if ($method->isVariadic()) {
            if (empty($descriptionFunction)) {
                return false;
            }
        } elseif ($method->getNumberOfParameters() !== count($descriptionFunction) + 1) {
            return false;
        }

        return $this->{$function}($transaction, ...$descriptionFunction);
    }

    public function defaultDescription(IngTransaction $transaction): bool
    {
        return true;
    }

    public function getNoteDescription(IngTransaction $transaction): bool
    {
        return true;
    }

    public function geldvoorelkaarInstallment(IngTransaction $transaction): bool
    {
        return true;
    }
}
