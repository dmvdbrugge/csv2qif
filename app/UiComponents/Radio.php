<?php

namespace UiComponents;

use UI\Exception\InvalidArgumentException;

use function count;
use function is_int;
use function is_string;

class Radio extends \UI\Controls\Radio
{
    /** @var array */
    private $options;

    /** @var array */
    private $flipped;

    /**
     * @param string[]        $options
     * @param int|string|null $selected Text or index of option to be selected (null for none)
     */
    public function __construct(array $options, $selected = 0)
    {
        $this->options = $options;
        $this->flipped = [];

        foreach ($options as $key => $value) {
            $this->append($value);
            $this->flipped[$value] = $key;
        }

        if (is_int($selected)) {
            $this->setSelected($selected);
        } elseif (is_string($selected)) {
            $this->setSelectedText($selected);
        }
    }

    public function append(string $text): void
    {
        parent::append($text);

        $this->flipped[$text] = count($this->options);
        $this->options[]      = $text;
    }

    public function getSelectedText(): string
    {
        return $this->options[$this->getSelected()];
    }

    public function setSelectedText(string $text): void
    {
        if (!isset($this->flipped[$text])) {
            throw new InvalidArgumentException("Cannot select {$text}: it's not in options!");
        }

        $this->setSelected($this->flipped[$text]);
    }
}
