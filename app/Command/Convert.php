<?php

namespace Command;

use File\CsvReader;
use File\QifWriter;
use Parable\Console\Command;
use Parable\DI\Container;
use Parable\Event\Hook;
use Transactions\Transformers\IngToGnuCash;

class Convert extends Command
{
    /** @var string */
    protected $name = 'convert';

    /** @var string */
    protected $description = 'Converts given ING CSV to QIF';

    /** @var Hook */
    private $hook;

    public function __construct(Hook $hook)
    {
        $this->addArgument('config', true);
        $this->addArgument('csv', true);
        $this->addArgument('qif');
        $this->addOption('debug');

        $this->hook = $hook;
    }

    public function run()
    {
        $debugLevel = $this->parameter->getOption('debug');
        $debugLevel = mb_strlen($debugLevel);

        // Prepare reader/writer/transformer
        $cfg = $this->parameter->getArgument('config');
        $csv = $this->parameter->getArgument('csv');
        $qif = $this->parameter->getArgument('qif') ?: (rtrim($csv, '.csv') . '.qif');

        $csvReader   = Container::create(CsvReader::class);
        $qifWriter   = Container::create(QifWriter::class);
        $transformer = Container::create(IngToGnuCash::class);

        $csvReader->setFile($csv);
        $qifWriter->setFile($qif);
        $transformer->setRuleSet($cfg);

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
        $this->output->cursorReset();
        $this->output->writeln([
            "{$counter} Transactions converted",
            "",
            "Source: {$csv}",
            "Dest:   {$qif}",
        ]);

        if ($debugLevel >= 1) {
            $this->output->writeln(PHP_EOL . 'Peak usage: ' . ceil(memory_get_peak_usage() / 1024) . 'kiB');
        }
    }

    private function addDebugHooks()
    {
        $printMatch = function ($event, $payload) {
            $event == IngToGnuCash::MATCH_FALLBACK
                ? $this->output->writeln(" <yellow>Fallback: {$payload}</yellow>")
                : $this->output->writeln(" <green>{$payload}</green>");
        };

        $this->hook->into(IngToGnuCash::MATCH_FOUND, $printMatch);
        $this->hook->into(IngToGnuCash::MATCH_FALLBACK, $printMatch);
    }
}
