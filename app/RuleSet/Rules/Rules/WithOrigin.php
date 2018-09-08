<?php

namespace RuleSet\Rules\Rules;

trait WithOrigin
{
    /** @var string */
    private $origin;

    public function getOrigin(): string
    {
        return $this->origin;
    }

    public function setOrigin(string $origin): void
    {
        $this->origin = $origin;
    }
}
