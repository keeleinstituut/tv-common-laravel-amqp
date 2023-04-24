<?php

namespace Amqp\Tests;

use Amqp\ConnectionRegistry;
use Mockery;
use Orchestra\Testbench\TestCase as Orchestra;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AbstractConnection;

class TestCase extends Orchestra
{
    protected AbstractConnection $connection;

    protected AMQPChannel $channel;

    protected ConnectionRegistry $registry;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpConnection();
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('amqp', [
            'publisher' => [
                'exchanges' => [
                    [
                        'exchange' => 'amq.topic',
                        'type' => 'topic',
                        'passive' => false,
                        'durable' => true,
                        'auto_delete' => false,
                        'internal' => false,
                        'nowait' => false,
                        'properties' => [],
                    ],
                ],
            ],
            'consumer' => [
                'queues' => [
                    [
                        'queue' => 'some-queue',
                        'passive' => false,
                        'durable' => true,
                        'exclusive' => false,
                        'auto_delete' => false,
                        'nowait' => false,
                        'properties' => ['x-ha-policy' => ['S', 'all']],
                        'bindings' => [
                            [
                                'exchange' => 'some-exchange',
                                'routingKey' => 'some-key',
                            ],
                        ],
                    ],
                ],
                'events' => [
                    'mode' => 'routing-key',
                    'map' => [
                        'institution.created' => '',
                    ],
                ],
                'consumer_tag' => '',
                'no_local' => false,
                'no_ack' => false,
                'exclusive' => false,
                'nowait' => false,
                'properties' => [],
                'timeout' => 0,
            ],
        ]);
    }

    protected function setUpConnection()
    {
        $this->channel = Mockery::mock(AMQPChannel::class)
            ->shouldIgnoreMissing();

        $this->connection = Mockery::mock(AbstractConnection::class)
            ->shouldIgnoreMissing();

        $this->connection->shouldReceive('channel')
            ->andReturn($this->channel);

        $this->registry = Mockery::mock(ConnectionRegistry::class)
            ->shouldIgnoreMissing();

        $this->registry->shouldReceive('getConnection')
            ->andReturn($this->connection);
    }
}
