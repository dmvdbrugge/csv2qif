<?php

namespace File;

class Base
{
    const MODE_READ  = 'r';
    const MODE_WRITE = 'w';

    /** @var resource */
    protected $handle;

    /** @var string */
    protected $file;

    public function setFile(string $file): void
    {
        $this->file = $file;
    }

    protected function open(string $mode = self::MODE_READ): void
    {
        $this->handle = fopen($this->file, $mode);
    }

    protected function close(): void
    {
        fclose($this->handle);
    }
}
