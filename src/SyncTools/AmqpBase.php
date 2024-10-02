<?php

namespace SyncTools;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;
use PhpAmqpLib\Channel\AMQPChannel;

class AmqpBase
{
    private array $properties;

    private ?int $channelId = null;

    public function __construct(private readonly AmqpConnectionRegistry $registry)
    {
        $this->properties = Config::get('amqp', []);
    }

    public function getChannel(): AMQPChannel
    {
        $channel = $this->registry->getConnection()->channel($this->channelId);
        $this->channelId = $channel->getChannelId();
        return $channel;
    }

    protected function getProperty(string $key, $default = null): mixed
    {
        return Arr::get($this->properties, $key, $default);
    }
}
