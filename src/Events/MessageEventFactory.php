<?php

namespace SyncTools\Events;

use Illuminate\Support\Facades\Config;
use PhpAmqpLib\Message\AMQPMessage;
use SyncTools\Exceptions\InvalidConfigurationException;

class MessageEventFactory implements MessageEventFactoryInterface
{
    const MODE_QUEUE = 'queue';

    const MODE_ROUTING_KEY = 'routing-key';

    const STATIC = 'static';

    public function event(AMQPMessage $message, string $queue): BaseConsumedEvent
    {
        $eventClassName = match ($this->getFactoryMode()) {
            self::MODE_QUEUE => $this->getEventClassName($queue),
            self::MODE_ROUTING_KEY => $this->getEventClassName($message->getRoutingKey()),
            self::STATIC => $this->getEventClassName('default'),
            default => throw new InvalidConfigurationException('Factory mode is invalid')
        };

        if (! is_a($eventClassName, BaseConsumedEvent::class, true)) {
            throw new InvalidConfigurationException("$eventClassName should extend ".BaseConsumedEvent::class);
        }

        return $eventClassName::produceFromMessage(json_decode($message->getBody(), true));
    }

    private function getEventClassName(string $key): string
    {
        $handlerClassName = Config::get('amqp.consumer.events.map', [])[$key] ?? '';
        if (empty($handlerClassName)) {
            throw new InvalidConfigurationException("Event not found for '$key'");
        }

        return $handlerClassName;
    }

    private function getFactoryMode(): string
    {
        return Config::get('amqp.consumer.events.mode', '');
    }
}
