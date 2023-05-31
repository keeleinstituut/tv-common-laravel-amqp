<?php

namespace SyncTools\Tests;

use Exception;
use Illuminate\Support\Str;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Wire\AMQPTable;
use SyncTools\AmqpPublisher;

class PublisherTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    private AmqpPublisher $publisher;

    public function setUp(): void
    {
        parent::setUp();
        $this->publisher = new AmqpPublisher($this->registry);
    }

    /**
     * @throws Exception
     */
    public function test_successful_publish()
    {
        $body = Str::random();
        $routingKey = Str::random();
        $exchange = 'amq.topic';

        $this->channel->shouldReceive('exchange_declare')
            ->with($exchange, 'topic', false, true, false, false, false, [])
            ->once();

        $this->channel->shouldReceive('basic_publish')
            ->with(
                Mockery::on(fn (AMQPMessage $message) => $message->body === $body),
                $exchange,
                $routingKey
            )->once();

        $this->publisher->publish($body, $exchange, $routingKey);
    }

    /**
     * @throws Exception
     */
    public function test_publish_with_headers()
    {
        $body = Str::random();
        $routingKey = Str::random();
        $exchange = 'amq.topic';

        $this->channel->shouldReceive('basic_publish')
            ->with(
                Mockery::on(function (AMQPMessage $message) {
                    /** @var AMQPTable $headers */
                    $headers = $message->get('application_headers');

                    return $headers['custom-header'] === 'custom-header-value';
                }),
                $exchange,
                $routingKey
            )->once();

        $this->publisher->publish($body, $exchange, $routingKey, [
            'custom-header' => 'custom-header-value',
        ]);
    }
}
