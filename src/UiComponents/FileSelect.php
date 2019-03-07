<?php

namespace Csv2Qif\UiComponents;

use DynamicComponents\Controls\Button;
use UI\Controls\Entry;
use UI\Controls\Grid;
use UI\Exception\InvalidArgumentException;
use UI\Window;

class FileSelect
{
    public const OPEN = 0;
    public const SAVE = 1;

    private $file = '';

    /** @var Button */
    private $button;

    /** @var Entry */
    private $entry;

    /** @var Button */
    private $clear;

    /** @var Window */
    private $window;

    public function __construct(string $text, Window $window, int $mode, bool $clearable = false)
    {
        if ($mode !== self::OPEN && $mode !== self::SAVE) {
            throw new InvalidArgumentException('Mode needs to be either OPEN or SAVE');
        }

        $this->window = $window;

        $this->entry = new Entry();
        $this->entry->setReadOnly(true);

        $this->button = new Button($text, function () use ($mode) {
            $this->select($mode);
        });

        if ($clearable) {
            $this->clear = new Button('  Clear  ', function () {
                $this->setFile('');
            });
        }
    }

    public function appendToGrid(Grid $grid, int $row = 0)
    {
        $clearable  = (bool) $this->clear;
        $entryXspan = $clearable ? 1 : 2;

        $grid->append($this->button, 0, $row, 1, 1, false, Grid::Fill, false, Grid::Fill);
        $grid->append($this->entry, 1, $row, $entryXspan, 1, true, Grid::Fill, false, Grid::Fill);

        if ($clearable) {
            $grid->append($this->clear, 2, $row, 1, 1, false, Grid::Fill, false, Grid::Fill);
        }
    }

    public function getFile(): string
    {
        return $this->file;
    }

    public function setFile(string $file): void
    {
        $this->file = $file;
        $this->entry->setText($file);
    }

    private function select(int $mode): void
    {
        $selected = $mode === self::OPEN
            ? $this->window->open()
            : $this->window->save();

        if ($selected) {
            $this->setFile($selected);
        }
    }
}
