<?php

namespace Csv2Qif\Event;

use Parable\Event\EventManager;

class Hook extends EventManager
{
    public function reset(?string $event = null): void
    {
        if ($event !== null) {
            $this->listeners[$event] = [];
        } else {
            $this->listeners = [];
        }
    }
}
