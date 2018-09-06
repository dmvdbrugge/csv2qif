<?php

namespace RuleSet\Exceptions;

use Exception;

class RuleSetConfigException extends Exception
{
    public static function unreadable(string $filename): self
    {
        return new self("Cannot read config file {$filename}. Make sure it exists with the correct permissions.");
    }
}
