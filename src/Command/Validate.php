<?php

namespace Csv2Qif\Command;

use Csv2Qif\Actors\Validator;
use Parable\Console\Command;
use Parable\Console\Parameter;

class Validate extends Command
{
    private const ARG_RULESET = 'ruleset';
    private const OPT_VERBOSE = 'verbose';

    protected $name = 'validate';

    protected $description = 'Validates given ruleset for use with convert.';

    /** @var Validator */
    private $validator;

    public function __construct(Validator $validator)
    {
        $this->addArgument(self::ARG_RULESET, Parameter::PARAMETER_REQUIRED);
        $this->addOption(self::OPT_VERBOSE);

        $this->validator = $validator;
    }

    public function run(): void
    {
        // Retrieve argument and option
        $ruleSet = $this->parameter->getArgument(self::ARG_RULESET);
        $verbose = (int) ($this->parameter->getOption(self::OPT_VERBOSE) ?? 0);

        $errorCount = $this->validator->validate($ruleSet, $verbose);

        // Signal result to the outside world
        exit($errorCount);
    }
}
