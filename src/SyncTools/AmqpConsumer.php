<?php

namespace SyncTools;

use Closure;
use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use InvalidArgumentException;
use PhpAmqpLib\Exception\AMQPChannelClosedException;
use PhpAmqpLib\Exception\AMQPConnectionClosedException;
use PhpAmqpLib\Exception\AMQPHeartbeatMissedException;
use PhpAmqpLib\Exception\AMQPTimeoutException;
use PhpAmqpLib\Message\AMQPMessage;
use SyncTools\Exceptions\InvalidConfigurationException;
use Throwable;

class AmqpConsumer extends AmqpBase
{
    const RETRY_ATTEMPTS = 5;

    const SECONDS_BETWEEN_ATTEMPTS = 1;

    protected ?Closure $callback;

    /**
     * @throws Exception
     */
    public function consume(string $queue, ?array $properties = []): bool
    {
        try {
            if (! empty($this->getConsumerPropertyBasedOnInput($properties, 'qos', []))) {
                $this->getChannel()->basic_qos(
                    $this->getConsumerPropertyBasedOnInput($properties, 'qos.prefetch_size'),
                    $this->getConsumerPropertyBasedOnInput($properties, 'qos.prefetch_count'),
                    false
                );
            }

            $this->declareQueue($queue);

            $this->getChannel()->basic_consume(
                $queue,
                $this->getConsumerTag(),
                $this->getConsumerProperty('no_local', false),
                $this->getConsumerProperty('no_ack', false),
                $this->getConsumerProperty('exclusive', false),
                $this->getConsumerProperty('nowait', false),
                [$this, 'handle']
            );

            while (! empty($this->getChannel()->callbacks)) {
                $this->getChannel()->wait(
                    null,
                    false,
                    $this->getConsumerPropertyBasedOnInput($properties, 'timeout', 0)
                );
            }
        } catch (AMQPTimeoutException) {
            return true;
        }

        return true;
    }

    public function setCallback($callback): static
    {
        if (! is_callable($callback)) {
            throw new InvalidConfigurationException("Callback $callback is not callable.");
        }

        $this->callback = $callback;

        return $this;
    }

    /**
     * @throws Throwable
     */
    public function handle(AMQPMessage $message): void
    {
        Log::info('Processing message - exchange: ' . $message->getExchange() . '; delivery-tag: ' . $message->getDeliveryTag());
        //        Log::debug('Message body: ' . $message->getBody());

        if (! is_callable($this->callback)) {
            throw new InvalidConfigurationException("Callback $this->callback is not callable.");
        }

        try {
            call_user_func($this->callback, $message, $this);
            $this->acknowledgeIfRequired($message);

            Log::info('Processing successful');
        } catch (Throwable $e) {
            $message->getChannel()->basic_reject(
                $message->getDeliveryTag(),
                true
            );

            Log::info('Processing failed');
            throw $e;
        }
    }

    /**
     * @throws Exception
     */
    public function setupQueues(): void
    {
        $queuesConfig = $this->getConsumerProperty('queues', []);
        foreach ($queuesConfig as $queueConfig) {
            $this->declareQueue($queueConfig['queue']);
        }
    }

    private function acknowledgeIfRequired(AMQPMessage $message): void
    {
        if (! $this->getConsumerProperty('enable_manual_acknowledgement', false)) {
            $message->getChannel()->basic_ack($message->getDeliveryTag());
        }

        if ($message->body === 'quit') {
            $message->getChannel()->basic_cancel($message->getConsumerTag());
        }
    }

    private function declareQueue(string $queue): void
    {
        $queues = $this->getConsumerProperty('queues');

        if (empty($queues)) {
            throw new InvalidArgumentException("Queue '$queue' is not defined");
        }

        $queuesMap = Arr::keyBy($this->getConsumerProperty('queues'), 'queue');

        if (! isset($queuesMap[$queue])) {
            throw new InvalidArgumentException("Queue '$queue' is not defined");
        }

        $queueConfig = $queuesMap[$queue];

        $declared = false;
        $attempt = 0;
        do {
            try {
                $attempt++;
                $this->getChannel()->queue_declare(
                    $queue,
                    $queueConfig['passive'] ?? false,
                    $queueConfig['durable'] ?? true,
                    $queueConfig['exclusive'] ?? false,
                    $queueConfig['auto_delete'] ?? false,
                    $queueConfig['nowait'] ?? false,
                    $queueConfig['properties'] ?? [],
                );
                $declared = true;
            } catch (AMQPHeartbeatMissedException | AMQPChannelClosedException | AMQPConnectionClosedException $e) {
                if ($attempt > self::RETRY_ATTEMPTS) {
                    throw $e;
                }

                sleep(self::SECONDS_BETWEEN_ATTEMPTS);
            }
        } while (! $declared);


        foreach ($queueConfig['bindings'] as $bindingConfig) {
            $bound = false;
            $attempt = 0;
            do {
                try {
                    $attempt++;
                    $this->getChannel()->queue_bind(
                        $queueConfig['queue'],
                        $bindingConfig['exchange'],
                        $bindingConfig['routingKey'] ?? '',
                    );
                    $bound = true;
                } catch (AMQPHeartbeatMissedException | AMQPChannelClosedException | AMQPConnectionClosedException $e) {
                    if ($attempt > self::RETRY_ATTEMPTS) {
                        throw $e;
                    }

                    sleep(self::SECONDS_BETWEEN_ATTEMPTS);
                }
            } while (! $bound);
        }
    }

    private function getConsumerProperty(string $key, $default = null): mixed
    {
        return $this->getProperty("consumer.$key", $default);
    }

    private function getConsumerPropertyBasedOnInput(array $input, string $key, $default = null): mixed
    {
        return Arr::get($input, $key, $this->getConsumerProperty($key, $default));
    }

    private function getConsumerTag(): string
    {
        return implode('-', [Str::slug(Config::get('app.name', 'laravel')), getmypid()]);
    }
}
