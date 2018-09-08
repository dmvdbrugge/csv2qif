<?php

namespace RuleSet\Rules\Rules;

interface RuleHasProperty extends Rule
{
    public function setProperty(string $property): void;
}
