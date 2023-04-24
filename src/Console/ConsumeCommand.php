<?php

namespace Amqp\Console;

use Amqp\Consumer;
use Amqp\Events\MessageEventFactory;
use Exception;
use Illuminate\Console\Command;
use PhpAmqpLib\Message\AMQPMessage;

class ConsumeCommand extends Command
{
    protected $signature = 'amqp:consume
                            {queue : The name of the queue to work}
                            {--prefetch-size : When the client finishes processing a message, the following message is already held locally, rather than needing to be sent down the channel}
                            {--prefetch-count : Specifies a prefetch window in terms of whole messages. This field may be used in combination with the prefetch-size field}
                            {--timeout : The number of seconds the process can run}
                            ';

    protected $description = 'Consume messages';

    /**
     * @throws Exception
     */
    public function handle(Consumer $consumer)
    {
        $queue = $this->argument('queue');
        $consumer->setCallback(function (AMQPMessage $message) use ($queue) {
            $event = (new MessageEventFactory)->event($message, $queue);
            event($event);
        })->consume($queue, $this->getProperties());
    }

    private function getProperties(): array
    {
        $properties = [];
        if (! is_null($this->option('timeout'))) {
            $properties['timeout'] = $this->option('timeout');
        }

        if (! is_null($this->option('prefetch-size'))) {
            $properties['qos']['prefetch_size'] = $this->option('prefetch-size');
        }

        if (! is_null($this->option('prefetch-count'))) {
            $properties['qos']['prefetch_count'] = $this->option('prefetch-count');
        }

        return $properties;
    }
}
