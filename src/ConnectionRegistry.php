<?php

namespace Amqp;

use Amqp\Exceptions\InvalidConfigurationException;
use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;
use PhpAmqpLib\Connection\AbstractConnection;
use PhpAmqpLib\Connection\AMQPSSLConnection;
use PhpAmqpLib\Connection\AMQPStreamConnection;

class ConnectionRegistry
{
    private array $connectionProperties;

    private ?AbstractConnection $connection = null;

    public function __construct()
    {
        $this->connectionProperties = Config::get('amqp.connection', []);
    }

    public function getConnection(): AbstractConnection
    {
        if (is_null($this->connection) || ! $this->connection->isConnected()) {
            try {
                $this->createConnection();
            } catch (Exception $e) {
                throw new InvalidConfigurationException('Connection configuration error', 0, $e);
            }
        }

        return $this->connection;
    }

    /**
     * @throws Exception
     */
    private function createConnection(): void
    {
        if ($this->getProperty('connection.ssl_options')) {
            $this->connection = new AMQPSSLConnection(
                $this->getProperty('host'),
                $this->getProperty('port'),
                $this->getProperty('username'),
                $this->getProperty('password'),
                $this->getProperty('vhost'),
                $this->getProperty('ssl_options'),
                $this->getProperty('connect_options')
            );
        } else {
            $this->connection = new AMQPStreamConnection(
                $this->getProperty('host'),
                $this->getProperty('port'),
                $this->getProperty('username'),
                $this->getProperty('password'),
                $this->getProperty('vhost'),
                $this->getProperty('connect_options.insist', false),
                $this->getProperty('connect_options.login_method', 'AMQPLAIN'),
                $this->getProperty('connect_options.login_response'),
                $this->getProperty('connect_options.locale', 3),
                $this->getProperty('connect_options.connection_timeout', 3.0),
                $this->getProperty('connect_options.read_write_timeout', 130),
                $this->getProperty('connect_options.context'),
                $this->getProperty('connect_options.keepalive', false),
                $this->getProperty('connect_options.heartbeat', 60),
                $this->getProperty('connect_options.channel_rpc_timeout', 0.0),
                $this->getProperty('connect_options.ssl_protocol')
            );
        }

        $this->connection->set_close_on_destruct();
    }

    private function getProperty(string $key, $default = null): mixed
    {
        return Arr::get($this->connectionProperties, $key, $default);
    }
}
