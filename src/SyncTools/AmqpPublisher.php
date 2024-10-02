<?php

namespace SyncTools;

use Exception;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Support\Arr;
use InvalidArgumentException;
use JsonSerializable;
use PhpAmqpLib\Exception\AMQPChannelClosedException;
use PhpAmqpLib\Exception\AMQPConnectionClosedException;
use PhpAmqpLib\Exception\AMQPHeartbeatMissedException;
use PhpAmqpLib\Exception\AMQPRuntimeException;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Wire\AMQPTable;

class AmqpPublisher extends AmqpBase
{
    const RETRY_ATTEMPTS = 5;

    const SECONDS_BETWEEN_ATTEMPTS = 1;

    private array $declaredExchanges = [];

    public function publish(mixed $message, string $exchange, string $routingKey = '', ?array $headers = null): void
    {
        $this->declareExchangeOnce($exchange);

        $published = false;
        $attempt = 0;
        $message = $this->composeMessage($message, $headers);
        do {
            try {
                $attempt++;
                $this->getChannel()->basic_publish(
                    $message,
                    $exchange,
                    $routingKey
                );
                $published = true;
            } catch (AMQPHeartbeatMissedException | AMQPChannelClosedException | AMQPConnectionClosedException $e) {
                if ($attempt > self::RETRY_ATTEMPTS) {
                    throw $e;
                }

                sleep(self::SECONDS_BETWEEN_ATTEMPTS);
            }
        } while (! $published);
    }

    /**
     * @throws Exception
     */
    public function setupExchanges(): void
    {
        foreach ($this->getProperty('publisher.exchanges', []) as $exchangeConfig) {
            $this->declareExchangeOnce($exchangeConfig['exchange']);
        }
    }

    private function declareExchangeOnce(string $exchange): void
    {
        if (empty($exchange)) {
            throw new InvalidArgumentException('Exchange is not set');
        }

        if ($this->declaredExchanges[$exchange] ?? false) {
            return;
        }

        $exchangesMap = Arr::keyBy($this->getProperty('publisher.exchanges', []), 'exchange');
        $exchangeConfig = $exchangesMap[$exchange] ?? null;

        if (empty($exchangeConfig)) {
            throw new InvalidArgumentException("Exchange '$exchange' is not declared");
        }

        $declared = false;
        $attempt = 0;
        do {
            try {
                $attempt++;
                $this->getChannel()->exchange_declare(
                    $exchangeConfig['exchange'],
                    $exchangeConfig['type'],
                    $exchangeConfig['passive'] ?? false,
                    $exchangeConfig['durable'] ?? true,
                    $exchangeConfig['auto_delete'] ?? false,
                    $exchangeConfig['internal'] ?? false,
                    $exchangeConfig['nowait'] ?? false,
                    $exchangeConfig['properties'] ?? [],
                );
                $declared = true;
            } catch (AMQPHeartbeatMissedException | AMQPChannelClosedException | AMQPConnectionClosedException $e) {
                if ($attempt > self::RETRY_ATTEMPTS) {
                    throw $e;
                }

                sleep(self::SECONDS_BETWEEN_ATTEMPTS);
            }
        } while (! $declared);

        $this->declaredExchanges[$exchange] = true;
    }

    private function composeMessage(mixed $message, ?array $headers = null): AMQPMessage
    {
        $headers = [
            'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT,
            'application_headers' => new AMQPTable($headers ?: []),
            'content_type' => 'application/json',
        ];

        if (is_array($message) || is_a($message, JsonSerializable::class) || is_a($message, Jsonable::class)) {
            return new AMQPMessage(json_encode($message), $headers);
        }

        $headers['content_type'] = 'text/plain';
        if (! is_string($message)) {
            $message = serialize($message);
        }

        return new AMQPMessage($message, $headers);
    }
}
