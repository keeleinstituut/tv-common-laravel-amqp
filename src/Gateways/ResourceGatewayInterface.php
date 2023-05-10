<?php

namespace Amqp\Gateways;

use Amqp\Exceptions\ResourceGatewayConnectionException;
use Amqp\Exceptions\ResourceNotFoundException;
use Generator;

interface ResourceGatewayInterface
{
    /**
     * @throws ResourceNotFoundException
     * @throws ResourceGatewayConnectionException
     */
    public function getResource(string $id): array;

    /**
     * @throws ResourceGatewayConnectionException
     */
    public function getResources(): Generator;
}
