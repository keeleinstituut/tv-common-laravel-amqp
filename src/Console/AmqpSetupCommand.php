<?php

namespace Amqp\Console;

use Amqp\Consumer;
use Amqp\Publisher;
use Exception;
use Illuminate\Console\Command;

class AmqpSetupCommand extends Command
{
    protected $signature = 'amqp:setup';

    protected $description = 'Setup exchanges, queues, bindings from config';

    /**
     * @throws Exception
     */
    public function handle(Consumer $consumer, Publisher $publisher): void
    {
        $publisher->setupExchanges();
        $consumer->setupQueues();
    }
}
