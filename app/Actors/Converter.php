<?php

namespace Actors;

use Event\Hook;
use File\CsvReader;
use File\QifWriter;
use Parable\Console\Output;
use Parable\DI\Container;
use RuleSet\RuleSetMatcher;
use Transactions\Transformer;

use function ceil;
use function memory_get_peak_usage;
use function microtime;
use function rtrim;
use function sprintf;

class Converter
{
    /** @var Hook */
    private $hook;

    /** @var Output */
    private $output;

    public function __construct(Hook $hook, Output $output)
    {
        $this->hook   = $hook;
        $this->output = $output;
    }

    public function convert(string $csv, ?string $qif = null, string $ruleSet = '', int $debugLevel = 0)
    {
        $start = microtime(true);
        $qif   = $qif ?? (rtrim($csv, '.csv') . '.qif');

        // Prepare reader/writer/transformer
        $csvReader   = Container::create(CsvReader::class);
        $qifWriter   = Container::create(QifWriter::class);
        $transformer = Container::create(Transformer::class);

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
        $this->hook->into(CsvReader::TRANSACTION_READ, $updateCounter);

        if ($debugLevel >= 2) {
            $this->addDebugHooks($debugLevel);
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
            $this->output->newline();
            $this->output->writeln('Peak usage: ' . ceil(memory_get_peak_usage() / 1024) . 'kiB');
            $this->output->writeln(sprintf('Duration:   %.2fs', microtime(true) - $start));
        }
    }

    private function addDebugHooks(int $debugLevel): void
    {
        $printMatch = function (string $event, $payload) {
            $event === RuleSetMatcher::MATCH_FALLBACK
                ? $this->output->writeln(" <yellow>Fallback: {$payload}</yellow>")
                : $this->output->writeln(" <green>{$payload}</green>");
        };

        $this->hook->into(RuleSetMatcher::MATCH_FOUND, $printMatch);

        if ($debugLevel >= 3) {
            $this->hook->into(RuleSetMatcher::MATCH_FALLBACK, $printMatch);
        }
    }
}
