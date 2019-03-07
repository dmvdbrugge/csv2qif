<?php

namespace Csv2Qif\Command;

use Csv2Qif\Actors\Converter;
use Parable\Console\Command;
use Parable\Console\Parameter;

class Convert extends Command
{
    private const ARG_CSV = 'csv';
    private const ARG_QIF = 'qif';

    private const OPT_DEBUG   = 'debug';
    private const OPT_RULESET = 'ruleset';

    protected $name = 'convert';

    protected $description = 'Converts given ING CSV to QIF.';

    /** @var Converter */
    private $converter;

    public function __construct(Converter $converter)
    {
        $this->addArgument(self::ARG_CSV, Parameter::PARAMETER_REQUIRED);
        $this->addArgument(self::ARG_QIF);
        $this->addOption(self::OPT_RULESET, Parameter::OPTION_VALUE_REQUIRED);
        $this->addOption(self::OPT_DEBUG);

        $this->converter = $converter;
    }

    public function run(): void
    {
        // Retrieve arguments and options
        $csv = $this->parameter->getArgument(self::ARG_CSV);
        $qif = $this->parameter->getArgument(self::ARG_QIF);

        $ruleSet    = $this->parameter->getOption(self::OPT_RULESET) ?? '';
        $debugLevel = (int) $this->parameter->getOption(self::OPT_DEBUG);

        $this->converter->convert($csv, $qif, $ruleSet, $debugLevel);
    }
}
