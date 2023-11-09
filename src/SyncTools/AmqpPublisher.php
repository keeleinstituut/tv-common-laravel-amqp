<?php

namespace SyncTools;

use Exception;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Support\Arr;
use InvalidArgumentException;
use JsonSerializable;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Wire\AMQPTable;

class AmqpPublisher extends AmqpBase
{
    private array $declaredExchanges = [];

    public function publish(mixed $message, string $exchange, string $routingKey = '', ?array $headers = null): void
    {
        $this->declareExchangeOnce($exchange);

        $this->getChannel()->basic_publish(
            $this->composeMessage($message, $headers),
            $exchange,
            $routingKey
        );
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
