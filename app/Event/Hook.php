<?php

namespace Csv2Qif\Event;

class Hook extends \Parable\Event\Hook
{
    public function reset(?string $event = null): void
    {
        if ($event !== null) {
            $this->hooks[$event] = [];
        } else {
            $this->hooks = [];
        }
    }
}
