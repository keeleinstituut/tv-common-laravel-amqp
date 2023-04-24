<?php

namespace Amqp\Tests\Events;

use Amqp\Events\BaseConsumedEvent;

class DummyEvent extends BaseConsumedEvent
{
    public function __construct(public array $data)
    {
    }

    public static function produceFromMessage(array $body): BaseConsumedEvent
    {
        return new static($body);
    }
}
