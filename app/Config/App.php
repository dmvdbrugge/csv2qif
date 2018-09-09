<?php

namespace Config;

use Csv2Qif\Command\Config;
use Csv2Qif\Command\Convert;
use Csv2Qif\Command\Ui;
use Csv2Qif\Command\Validate;
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
