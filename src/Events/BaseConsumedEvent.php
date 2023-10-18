<?php

namespace SyncTools\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use PhpAmqpLib\Message\AMQPMessage;

abstract class BaseConsumedEvent
{
    use Dispatchable, InteractsWithSockets;

    /**
     * Method should produce dispatchable event based on the AMQPMessage body
     *
     * @return static
     */
    abstract public static function produceFromMessage(AMQPMessage $message): self;
}
