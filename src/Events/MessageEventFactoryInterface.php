<?php

namespace Amqp\Events;

use Illuminate\Contracts\Container\BindingResolutionException;
use InvalidArgumentException;
use PhpAmqpLib\Message\AMQPMessage;

interface MessageEventFactoryInterface
{
    /**
     * @throws BindingResolutionException
     * @throws InvalidArgumentException
     */
    public function event(AMQPMessage $message, string $queue): BaseConsumedEvent;
}
