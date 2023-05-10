<?php

namespace Amqp\Console\Base;

use Amqp\Exceptions\ResourceGatewayConnectionException;
use Amqp\Gateways\ResourceGatewayInterface;
use Amqp\Repositories\CachedEntityRepositoryInterface;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Date;

abstract class BaseEntityFullSyncCommand extends Command
{
    /**
     * @throws ResourceGatewayConnectionException
     */
    public function handle(): void
    {
        $entityRepository = $this->getEntityRepository();

        $syncStartTime = Date::now();
        foreach ($this->getResourceGateway()->getResources() as $resource) {
            $this->getEntityRepository()->save($resource);
        }

        $entityRepository->deleteNotSynced($syncStartTime);
    }

    abstract public function getResourceGateway(): ResourceGatewayInterface;

    abstract public function getEntityRepository(): CachedEntityRepositoryInterface;
}
