<?php

namespace Command;

use File\CsvReader;
use File\QifWriter;
use Parable\Console\Command;
use Parable\Console\Parameter;
use Parable\DI\Container;
use Parable\Event\Hook;
use RuleSet\RuleSetMatcher;
use Transactions\Transformers\IngToGnuCash;

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
        $this->addOption(self::OPT_RULESET, Parameter::PARAMETER_OPTIONAL, Parameter::OPTION_VALUE_REQUIRED);
        $this->addOption(self::OPT_DEBUG);

        $this->hook = $hook;
    }

    public function run(): void
    {
        // Retrieve arguments and options
        $csv = $this->parameter->getArgument(self::ARG_CSV);
        $qif = $this->parameter->getArgument(self::ARG_QIF) ?? (rtrim($csv, '.csv') . '.qif');

        $ruleSet    = $this->parameter->getOption(self::OPT_RULESET) ?? '';
        $debugLevel = $this->parameter->getOption(self::OPT_DEBUG) ?? '';
        $debugLevel = mb_strlen($debugLevel);

        // Prepare reader/writer/transformer
        $csvReader   = Container::create(CsvReader::class);
        $qifWriter   = Container::create(QifWriter::class);
        $transformer = Container::create(IngToGnuCash::class);

        $csvReader->setFile($csv);
        $qifWriter->setFile($qif);
        $transformer->setRuleSet($ruleSet);

        // Prepare counter presentation
        $counter       = 0;
        $updateCounter = function () use (&$counter) {
            $counter++;
            $this->output->cursorReset();
            $this->output->write($counter);
        };

        // Hook the counter into the event
        $this->hook->into(CsvReader::READ_TRANSACTION_EVENT, $updateCounter);

        if ($debugLevel >= 2) {
            $this->addDebugHooks();
        }

        // The real magic happens here
        $qifWriter->writeTransactions($transformer->transformAll($csvReader->getTransactions()));

        // Print out final info
        $usingRuleSet = $ruleSet ? " using ruleset {$ruleSet}" : '';

        $this->output->cursorReset();
        $this->output->writeln([
            "{$counter} Transactions converted{$usingRuleSet}",
            "",
            "Source: {$csv}",
            "Dest:   {$qif}",
        ]);

        if ($debugLevel >= 1) {
            $this->output->writeln(PHP_EOL . 'Peak usage: ' . ceil(memory_get_peak_usage() / 1024) . 'kiB');
        }
    }

    private function addDebugHooks(): void
    {
        $printMatch = function (string $event, $payload) {
            $event == RuleSetMatcher::MATCH_FALLBACK
                ? $this->output->writeln(" <yellow>Fallback: {$payload}</yellow>")
                : $this->output->writeln(" <green>{$payload}</green>");
        };

        $this->hook->into(RuleSetMatcher::MATCH_FOUND, $printMatch);
        $this->hook->into(RuleSetMatcher::MATCH_FALLBACK, $printMatch);
    }
}
