<?php

namespace UiComponents;

use function call_user_func;

class Button extends \UI\Controls\Button
{
    /** @var callable */
    private $onClick;

    public function __construct(string $text, callable $onClick)
    {
        parent::__construct($text);

        $this->onClick = $onClick;
    }

    protected function onClick()
    {
        call_user_func($this->onClick, $this);
    }
}
