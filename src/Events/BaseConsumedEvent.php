<?php

namespace SyncTools\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;

abstract class BaseConsumedEvent
{
    use Dispatchable, InteractsWithSockets;

    /**
     * Method should produce dispatchable event based on the AMQPMessage body
     *
     * @return static
     */
    abstract public static function produceFromMessage(array $body): self;
}
