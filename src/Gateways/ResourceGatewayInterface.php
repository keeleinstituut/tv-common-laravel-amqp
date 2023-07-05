<?php

namespace SyncTools\Gateways;

use Generator;
use SyncTools\Exceptions\ResourceGatewayConnectionException;
use SyncTools\Exceptions\ResourceNotFoundException;

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
