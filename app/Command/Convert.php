<?php

namespace Command;

use Actors\Converter;
use Event\Hook;
use Parable\Console\Command;
use Parable\Console\Parameter;

class Convert extends Command
{
    private const ARG_CSV = 'csv';
    private const ARG_QIF = 'qif';

    private const OPT_DEBUG   = 'debug';
    private const OPT_RULESET = 'ruleset';

    /** @var string */
    protected $name = 'convert';

    /** @var string */
    protected $description = 'Converts given ING CSV to QIF';

    /** @var Hook */
    private $hook;

    public function __construct(Hook $hook)
    {
        $this->addArgument(self::ARG_CSV, Parameter::PARAMETER_REQUIRED);
        $this->addArgument(self::ARG_QIF);
        $this->addOption(self::OPT_RULESET, Parameter::OPTION_VALUE_REQUIRED);
        $this->addOption(self::OPT_DEBUG);

        $this->hook = $hook;
    }

    public function run(): void
    {
        // Retrieve arguments and options
        $csv = $this->parameter->getArgument(self::ARG_CSV);
        $qif = $this->parameter->getArgument(self::ARG_QIF);

        $ruleSet    = $this->parameter->getOption(self::OPT_RULESET) ?? '';
        $debugLevel = (int) $this->parameter->getOption(self::OPT_DEBUG);

        $converter = new Converter($this->hook, $this->output);
        $converter->convert($csv, $qif, $ruleSet, $debugLevel);
    }
}
