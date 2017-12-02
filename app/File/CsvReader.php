<?php

namespace File;

use Parable\Event\Hook;
use Transactions\IngTransaction;

class CsvReader extends File
{
    const READ_TRANSACTION_EVENT = 'CsvReader::readTransaction';

    /** @var Hook */
    private $hook;

    public function __construct(Hook $hook)
    {
        $this->hook = $hook;
    }

    /**
     * @return \Traversable|IngTransaction[]
     */
    public function getTransactions(): \Traversable
    {
        $this->open();
        $this->parseHeaders();

        while (($transaction = $this->readTransaction()) !== null) {
            yield $transaction;
        }

        $this->close();
    }

    private function parseHeaders(): void
    {
        $fileHeaders  = fgetcsv($this->handle);
        $knownHeaders = str_getcsv('"Datum","Naam / Omschrijving","Rekening","Tegenrekening","Code","Af Bij","Bedrag (EUR)","MutatieSoort","Mededelingen"');

        if ($fileHeaders !== $knownHeaders) {
            throw new \Exception('Format changed!');
        }
    }

    /**
     * @return IngTransaction|null
     */
    private function readTransaction(): ?IngTransaction
    {
        $transaction_arr = fgetcsv($this->handle);

        if ($transaction_arr === false) {
            return null;
        }

        $this->hook->trigger(self::READ_TRANSACTION_EVENT);

        return IngTransaction::fromIngCsv(...$transaction_arr);
    }
}
