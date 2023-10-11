<?php

namespace SyncTools\Tests\Events;

use PhpAmqpLib\Message\AMQPMessage;
use SyncTools\Events\BaseConsumedEvent;

class DummyEvent extends BaseConsumedEvent
{
    public function __construct(public array $data)
    {
    }

    public static function produceFromMessage(AMQPMessage $message): BaseConsumedEvent
    {
        return new static(json_decode($message->getBody(), true));
    }
}
