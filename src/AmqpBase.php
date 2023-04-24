<?php

namespace Amqp;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;
use PhpAmqpLib\Channel\AMQPChannel;

class AmqpBase
{
    private array $properties;

    private AMQPChannel $channel;

    public function __construct(private readonly ConnectionRegistry $registry)
    {
        $this->properties = Config::get('amqp', []);
        $this->channel = $this->registry->getConnection()->channel();
    }

    protected function getChannel(): AMQPChannel
    {
        return $this->channel;
    }

    protected function getProperty(string $key, $default = null): mixed
    {
        return Arr::get($this->properties, $key, $default);
    }
}
