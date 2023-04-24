<?php

namespace Amqp\Tests;

use Amqp\Events\MessageEventFactory;
use Amqp\Exceptions\InvalidConfigurationException;
use Amqp\Tests\Events\DummyEvent;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Orchestra\Testbench\TestCase;
use PhpAmqpLib\Message\AMQPMessage;

class MessageEventFactoryTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    private MessageEventFactory $factory;

    protected function setUp(): void
    {
        parent::setUp();
        $this->factory = new MessageEventFactory;
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('amqp', [
            'consumer' => [
                'events' => [
                    'mode' => '',
                    'map' => [],
                ],
            ],
        ]);
    }

    public function test_queue_factory()
    {
        $queue = 'some-queue';
        $this->setUpEventsConfig(
            MessageEventFactory::MODE_QUEUE,
            [$queue => DummyEvent::class]
        );

        $body = ['key' => 'value'];
        $event = $this->factory->event(
            $this->getMessage('whatever-routing-key', $body),
            $queue
        );

        $this->assertInstanceOf(
            DummyEvent::class,
            $event
        );

        $this->assertEqualsCanonicalizing($event->data, $body);
    }

    public function test_routing_key_factory()
    {
        $routingKey = 'some-routing-key';
        $this->setUpEventsConfig(
            MessageEventFactory::MODE_ROUTING_KEY,
            [$routingKey => DummyEvent::class]
        );

        $body = ['key' => 'value'];
        $event = $this->factory->event(
            $this->getMessage($routingKey, $body),
            'whatever-queue'
        );

        $this->assertInstanceOf(
            DummyEvent::class,
            $event
        );

        $this->assertEqualsCanonicalizing($event->data, $body);
    }

    public function test_static_factory()
    {
        $this->setUpEventsConfig(
            MessageEventFactory::STATIC,
            ['default' => DummyEvent::class]
        );

        $body = ['key' => 'value'];
        $event = $this->factory->event(
            $this->getMessage('whatever-routing-key', $body),
            'whatever-queue'
        );

        $this->assertInstanceOf(
            DummyEvent::class,
            $event
        );

        $this->assertEqualsCanonicalizing($event->data, $body);
    }

    public function test_invalid_configuration()
    {
        $this->setUpEventsConfig(
            MessageEventFactory::STATIC,
            ['wrong-key' => DummyEvent::class]
        );

        $this->expectException(InvalidConfigurationException::class);

        $this->factory->event(
            $this->getMessage('whatever-routing-key', ['key' => 'value']),
            'whatever-queue'
        );
    }

    private function getMessage(string $routingKey, array $body): AMQPMessage
    {
        $message = Mockery::mock(AMQPMessage::class)
            ->shouldIgnoreMissing();

        $message->shouldReceive('getRoutingKey')
            ->andReturn($routingKey);

        $message->shouldReceive('getBody')
            ->andReturn(json_encode($body));

        return $message;
    }

    private function setUpEventsConfig(string $mode, array $map)
    {
        config(['amqp.consumer.events.mode' => $mode]);
        config(['amqp.consumer.events.map' => $map]);
    }
}
