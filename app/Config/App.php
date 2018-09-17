<?php

namespace Config;

use Csv2Qif\Command\Convert;
use Csv2Qif\Command\Ui;
use Csv2Qif\Command\Validate;
use Parable\Framework\Interfaces\Config;

class App implements Config
{
    public function get(): array
    {
        return [
            'parable' => [
                'commands' => [
                    Convert::class,
                    Ui::class,
                    Validate::class,
                ],
            ],
        ];
    }
}
