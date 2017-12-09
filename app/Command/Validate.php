<?php

namespace Command;

use Parable\Console\Command;
use Parable\Console\Parameter;
use Parable\DI\Container;
use RuleSet\RuleSetValidator;

class Validate extends Command
{
    private const ARG_RULESET = 'ruleset';
    private const OPT_DEBUG   = 'debug';

    /** @var string */
    protected $name = 'validate';

    /** @var string */
    protected $description = 'Validates given ruleset for use with convert';

    public function __construct()
    {
        $this->addArgument(self::ARG_RULESET, Parameter::PARAMETER_REQUIRED);
        $this->addOption(self::OPT_DEBUG);
    }

    public function run(): void
    {
        $ruleSet    = $this->parameter->getArgument(self::ARG_RULESET);
        $debugLevel = $this->parameter->getOption(self::OPT_DEBUG) ?? '';
        $debugLevel = mb_strlen($debugLevel);

        $validator = Container::get(RuleSetValidator::class);
        $validator->validate($ruleSet);
    }
}
