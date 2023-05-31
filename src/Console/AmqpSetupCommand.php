<?php

namespace SyncTools\Console;

use Exception;
use Illuminate\Console\Command;
use SyncTools\AmqpConsumer;
use SyncTools\AmqpPublisher;

class AmqpSetupCommand extends Command
{
    protected $signature = 'amqp:setup';

    protected $description = 'Setup exchanges, queues, bindings from config';

    /**
     * @throws Exception
     */
    public function handle(AmqpConsumer $consumer, AmqpPublisher $publisher): void
    {
        $publisher->setupExchanges();
        $consumer->setupQueues();
    }
}
