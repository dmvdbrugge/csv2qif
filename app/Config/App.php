<?php

namespace Config;

use Command\Convert;
use Command\Validate;
use Parable\Framework\Interfaces\Config;

class App implements Config
{
    public function get(): array
    {
        return [
            "parable" => [
                "commands" => [
                    Convert::class,
                    Validate::class,
                ],
                "configs" => [
                    Example::class,
                ],
            ],
        ];
    }
}
