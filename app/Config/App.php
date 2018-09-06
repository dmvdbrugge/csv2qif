<?php

namespace Config;

use Command\Config;
use Command\Convert;
use Command\Ui;
use Command\Validate;
use Parable\Framework\Interfaces\Config as ParableConfig;

class App implements ParableConfig
{
    public function get(): array
    {
        return [
            'parable' => [
                'commands' => [
                    Config::class,
                    Convert::class,
                    Ui::class,
                    Validate::class,
                ],
                'configs' => [
                    Example::class,
                ],
            ],
        ];
    }
}
