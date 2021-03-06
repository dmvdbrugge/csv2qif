<?php

namespace Csv2Qif\RuleSet\Exceptions;

use Exception;
use Symfony\Component\Yaml\Exception\ParseException;

class RuleSetConfigException extends Exception
{
    public static function unreadable(string $filename): self
    {
        return new self("Cannot read config file {$filename}. Make sure it exists with the correct permissions.");
    }

    public static function invalidYaml(ParseException $e): self
    {
        return new self("Cannot parse config file {$e->getParsedFile()}: {$e->getMessage()}", $e->getCode(), $e);
    }
}
