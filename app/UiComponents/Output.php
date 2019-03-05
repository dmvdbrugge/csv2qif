<?php

namespace Csv2Qif\UiComponents;

use UI\Controls\MultilineEntry;

use function str_repeat;
use function substr;

use const PHP_EOL;

class Output extends \Parable\Console\Output
{
    /** @var MultilineEntry */
    private $output;

    public function __construct(MultilineEntry $output)
    {
        $this->output = $output;

        foreach ($this->tags as $key => $val) {
            $this->tags[$key] = '';
        }
    }

    /**
     * @param string $string
     *
     * @return $this
     */
    public function write($string)
    {
        $string = $this->parseTags($string);

        $this->enableClearLine();
        $this->output->append($string);

        return $this;
    }

    /**
     * @param int $count
     *
     * @return $this
     */
    public function newline($count = 1)
    {
        $this->disableClearLine();
        $this->output->append(str_repeat(PHP_EOL, $count));

        return $this;
    }

    /**
     * @return $this
     */
    public function cursorReset()
    {
        // Cursor reset means we will be writing over stuff anyway,
        // just remove the current line
        return $this->clearLine();
    }

    /**
     * @return $this
     */
    public function cls()
    {
        $this->disableClearLine();
        $this->output->setText('');

        return $this;
    }

    /**
     * @return $this
     */
    public function clearLine()
    {
        if (!$this->isClearLineEnabled()) {
            return $this;
        }

        $lastLineBreak = strrpos($this->output->getText(), PHP_EOL);

        if ($lastLineBreak === false) {
            $this->output->setText('');
        } else {
            $this->output->setText(substr($this->output->getText(), 0, $lastLineBreak + 1));
        }

        $this->disableClearLine();

        return $this;
    }
}
