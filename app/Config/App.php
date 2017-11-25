<?php

namespace Config;

use Command\Convert;
use Parable\Framework\Interfaces\Config;

class App implements Config
{
    public function get(): array
    {
        return [
            "parable" => [
                "commands" => [
                    Convert::class,
                ],
                "configs" => [
                    Example::class,
                ],
            ],
        ];
    }
}
