<?php

namespace SyncTools\Console\Base;

use Illuminate\Console\Command;
use SyncTools\Exceptions\ResourceGatewayConnectionException;
use SyncTools\Exceptions\ResourceNotFoundException;
use SyncTools\Gateways\ResourceGatewayInterface;
use SyncTools\Repositories\CachedEntityRepositoryInterface;

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

    abstract protected function getGateway(): ResourceGatewayInterface;

    abstract protected function getRepository(): CachedEntityRepositoryInterface;
}
