<?php

namespace SyncTools\Console\Base;

use Illuminate\Console\Command;
use SyncTools\Exceptions\ResourceGatewayConnectionException;
use SyncTools\Gateways\ResourceGatewayInterface;
use SyncTools\Repositories\CachedEntityRepositoryInterface;

abstract class BaseEntityFullSyncCommand extends Command
{
    /**
     * @throws ResourceGatewayConnectionException
     */
    public function handle(): void
    {
        $entityRepository = $this->getEntityRepository();
        $entityRepository->cleanupLastSyncDateTime();

        foreach ($this->getResourceGateway()->getResources() as $resource) {
            $entityRepository->save($resource);
        }

        $entityRepository->deleteNotSynced();
    }

    abstract protected function getResourceGateway(): ResourceGatewayInterface;

    abstract protected function getEntityRepository(): CachedEntityRepositoryInterface;
}
