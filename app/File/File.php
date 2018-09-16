<?php

namespace Csv2Qif\File;

use BadMethodCallException;
use InvalidArgumentException;

use function dirname;
use function fclose;
use function file_exists;
use function fopen;
use function is_readable;
use function is_resource;
use function is_writable;

class File
{
    protected const MODE_READ  = 'r';
    protected const MODE_WRITE = 'w';

    /** @var resource */
    protected $handle;

    /** @var string */
    protected $file;

    public function __destruct()
    {
        $this->close();
    }

    public function setFile(string $file): void
    {
        $this->file = $file;
    }

    /**
     * @throws \Exception
     */
    protected function open(string $mode = self::MODE_READ): void
    {
        if ($this->isOpen()) {
            throw new BadMethodCallException("File {$this->file} already open!");
        }

        if ($mode !== self::MODE_READ && $mode !== self::MODE_WRITE) {
            throw new InvalidArgumentException("Unsupported mode {$mode}!");
        }

        if (file_exists($this->file)) {
            if ($mode === self::MODE_READ && !is_readable($this->file)) {
                throw new \Exception("File {$this->file} is not readable!");
            }

            if ($mode === self::MODE_WRITE && !is_writable($this->file)) {
                throw new \Exception("File {$this->file} is not writable!");
            }
        } else {
            if ($mode === self::MODE_READ) {
                throw new \Exception("File {$this->file} does not exist!");
            }

            if (!($dirname = dirname($this->file)) || !is_writable($dirname)) {
                throw new \Exception("Directory {$dirname} is not writable!");
            }
        }

        $this->handle = fopen($this->file, $mode);
    }

    protected function close(): void
    {
        if ($this->isOpen()) {
            fclose($this->handle);
        }
    }

    protected function isOpen(): bool
    {
        return is_resource($this->handle);
    }
}
