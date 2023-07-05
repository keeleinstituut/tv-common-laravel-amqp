<?php

namespace SyncTools\Tests;

use Exception;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PhpAmqpLib\Message\AMQPMessage;
use RuntimeException;
use SyncTools\AmqpConsumer;

class ConsumerTest extends AmqpTestCase
{
    use MockeryPHPUnitIntegration;

    private AmqpConsumer $consumer;

    public function setUp(): void
    {
        parent::setUp();
        $this->consumer = new AmqpConsumer($this->registry);
    }

    /**
     * @throws Exception
     */
    public function test_successful_consume()
    {
        $queue = 'some-queue';

        $this->channel->shouldReceive('queue_declare')
            ->with($queue, false, true, false, false, false, ['x-ha-policy' => ['S', 'all']])
            ->atLeast()
            ->once();

        $this->channel->shouldReceive('queue_bind')
            ->with($queue, 'some-exchange', 'some-key')
            ->atLeast()
            ->once();

        $this->channel->shouldReceive('basic_consume')
            ->with(
                $queue,
                implode('-', [Str::slug(Config::get('app.name', 'laravel')), getmypid()]),
                false,
                false,
                false,
                false,
                Mockery::on(fn ($closure) => is_callable($closure))
            )->atLeast()->once();

        $this->channel->callbacks = ['some-callback'];

        $this->channel->shouldReceive('wait')->andReturnUsing(function () {
            return array_pop($this->channel->callbacks);
        })->atLeast()->once();

        $this->consumer->setCallback(fn () => null)
            ->consume($queue);
    }

    public function test_successful_consume_with_params()
    {
        $this->channel->shouldReceive('basic_qos')
            ->with(100, 200, false)
            ->once();

        $this->channel->callbacks = ['some-callback'];

        $this->channel->shouldReceive('wait')->with(null, false, 10)
            ->andReturnUsing(function () {
                return array_pop($this->channel->callbacks);
            })->atLeast()->once();

        $this->consumer->setCallback(fn () => null)
            ->consume('some-queue', [
                'timeout' => 10,
                'qos' => [
                    'prefetch_size' => 100,
                    'prefetch_count' => 200,
                ],
            ]);
    }

    public function test_requeue_on_callback_exception()
    {
        $deliveryTag = Str::random();
        $message = Mockery::mock(AMQPMessage::class)
            ->shouldIgnoreMissing();

        $message->shouldReceive('getChannel')
            ->andReturn($this->channel);

        $message->shouldReceive('getDeliveryTag')
            ->andReturn($deliveryTag);

        $this->channel->shouldReceive('basic_reject')
            ->with($deliveryTag, true)->once();

        $this->expectException(RuntimeException::class);

        $this->consumer->setCallback(fn () => throw new RuntimeException())
            ->handle($message);
    }
}
