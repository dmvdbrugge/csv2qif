<?php

namespace Config;

use Parable\Framework\Interfaces\Config;

class Example implements Config
{
    public function get(): array
    {
        return [
            // This is ruleset 'example', for a personal account
            'csv2qif.example'       => [
                'fallback' => true, // default
                'matchers' => [
                    'Joint Account In'                                 => [
                        'transfer'    => 'Income:Joint Account',
                        'description' => ['getNoteDescription'], // default
                        'rules'       => [
                            ['equals', 'transfer', '<IBAN of Joint Account>'],
                            ['greaterThan', 'amount', 0],
                        ],
                    ],
                    'Joint Account Ex'                                 => [
                        'transfer' => 'Expenses:Joint Account',
                        'rules'    => [
                            ['equals', 'transfer', '<IBAN of Joint Account>'],
                            ['lessThan', 'amount', 0],
                        ],
                    ],
                    'Father\'s House Ministries'                       => [
                        'transfer' => 'Gifts:Father\'s House Ministries',
                        'rules'    => [
                            ['contains', 'description', 'Fathers House.Ministries'],
                        ],
                    ],
                    'Go and Tell Gifts'                                => [
                        'transfer' => 'Gifts:Go and Tell',
                        'rules'    => [
                            ['contains', 'description', 'Go and Tell'],
                            ['equals', 'notes->authorizationId', '12345'],
                        ],
                    ],
                    'ING Costs'                                        => [
                        'transfer' => 'Expenses:ING',
                        'rules'    => [
                            ['contains', 'description', 'Kosten'],
                            [
                                'oneOf',
                                ['contains', 'notes->description', 'ING Bank'],
                                [
                                    'allOf',
                                    ['isEmpty', 'notes->description'],
                                    ['contains', 'notes->source', 'ING Bank'],
                                ],
                            ],
                        ],
                    ],
                    'Specsavers Contacts'                              => [
                        'transfer' => 'Expenses:Specsavers:Contacts',
                        'rules'    => [
                            ['contains', 'description', 'Specsavers'],
                            ['not', 'isEmpty', 'notes->authorizationId'],
                        ],
                    ],
                    'Specsavers Glasses'                               => [
                        'transfer' => 'Expenses:Specsavers:Glasses',
                        'rules'    => [
                            ['contains', 'description', 'Specsavers'],
                            ['isEmpty', 'notes->authorizationId'],
                        ],
                    ],
                    'Oranje Spaarrekening <account_number> Deposit'    => [
                        'transfer'    => 'Assets:Bank:Savings',
                        'description' => 'Deposit',
                        'rules'       => [
                            ['equals', 'account', '<Your IBAN>'],
                            ['equals', 'notes->source', 'Naar Oranje Spaarrekening <account_number>'],
                        ],
                    ],
                    'Oranje Spaarrekening <account_number> Withdrawal' => [
                        'transfer'    => 'Assets:Bank:Savings',
                        'description' => 'Withdrawal',
                        'rules'       => [
                            ['equals', 'account', '<Your IBAN>'],
                            ['equals', 'notes->source', 'Van Oranje Spaarrekening <account_number>'],
                        ],
                    ],
                ],
            ],
            // This ruleset is an example for the joint account referenced above, if you manage that separately.
            'csv2qif.example-joint' => [
                'matchers' => [
                    'Personal Account In' => [
                        'transfer' => 'Income:Personal Account',
                        'rules'    => [
                            ['equals', 'transfer', '<IBAN of Personal Account>'],
                            ['greaterThan', 'amount', 0],
                        ],
                    ],
                    'Personal Account Ex' => [
                        'transfer' => 'Expenses:Personal Account',
                        'rules'    => [
                            ['equals', 'transfer', '<IBAN of Personal Account>'],
                            ['lessThan', 'amount', 0],
                        ],
                    ],
                    'Rent'                => [
                        'transfer' => 'Expenses:Rent',
                        'rules'    => [

                        ],
                    ],
                ],
            ],
        ];
    }
}
