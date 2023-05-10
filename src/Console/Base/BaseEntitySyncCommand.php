<?php

namespace Amqp\Console\Base;

use Amqp\Exceptions\ResourceGatewayConnectionException;
use Amqp\Exceptions\ResourceNotFoundException;
use Amqp\Gateways\ResourceGatewayInterface;
use Amqp\Repositories\CachedEntityRepositoryInterface;
use Illuminate\Console\Command;

abstract class BaseEntitySyncCommand extends Command
{
    /**
     * @throws ResourceGatewayConnectionException
     */
    public function handle(): void
    {
        $entityId = $this->argument('id');
        try {
            $this->getRepository()->save(
                $this->getGateway()->getResource($entityId)
            );
        } catch (ResourceNotFoundException) {
            $this->getRepository()->delete($entityId);
        }
    }

    abstract public function getGateway(): ResourceGatewayInterface;

    abstract public function getRepository(): CachedEntityRepositoryInterface;
}
