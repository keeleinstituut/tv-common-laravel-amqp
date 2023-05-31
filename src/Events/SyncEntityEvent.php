<?php

namespace SyncTools\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;

class SyncEntityEvent extends BaseConsumedEvent
{
    use Dispatchable, InteractsWithSockets;

    public function __construct(public readonly string $id)
    {
    }

    /**
     * Method should produce dispatchable event based on the AMQPMessage body
     *
     * @return static
     */
    public static function produceFromMessage(array $body): self
    {
        return new static($body['id']);
    }
}
