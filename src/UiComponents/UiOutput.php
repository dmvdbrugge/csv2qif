<?php

namespace Csv2Qif\UiComponents;

use Parable\Console\Output;
use UI\Controls\MultilineEntry;

use function str_repeat;
use function substr;

use const PHP_EOL;

class UiOutput extends Output
{
    /** @var MultilineEntry */
    private $output;

    public function __construct(MultilineEntry $output)
    {
        $this->output = $output;

        foreach ($this->predefinedTags as $key => $val) {
            $this->predefinedTags[$key] = '';
        }
    }

    public function write(string $string): void
    {
        $string = $this->parseTags($string);

        $this->enableClearLine();
        $this->output->append($string);
    }

    public function newline(int $count = 1): void
    {
        $this->disableClearLine();
        $this->output->append(str_repeat(PHP_EOL, $count));
    }

    public function cursorReset(): void
    {
        // Cursor reset means we will be writing over stuff anyway,
        // just remove the current line
        $this->clearLine();
    }

    public function cls(): void
    {
        $this->disableClearLine();
        $this->output->setText('');
    }

    public function clearLine(): void
    {
        if (!$this->isClearLineEnabled()) {
            return;
        }

        $lastLineBreak = strrpos($this->output->getText(), PHP_EOL);

        if ($lastLineBreak === false) {
            $this->output->setText('');
        } else {
            $this->output->setText(substr($this->output->getText(), 0, $lastLineBreak + 1));
        }

        $this->disableClearLine();
    }
}
