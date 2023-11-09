<?php

namespace SyncTools\Listeners;

use SyncTools\Events\SyncEntityEvent;
use SyncTools\Exceptions\ResourceGatewayConnectionException;
use SyncTools\Exceptions\ResourceNotFoundException;
use SyncTools\Gateways\ResourceGatewayInterface;
use SyncTools\Repositories\CachedEntityRepositoryInterface;

abstract class EntitySaveEventListener
{
    /**
     * @throws ResourceGatewayConnectionException
     */
    public function handle(SyncEntityEvent $event): void
    {
        try {
            $this->getRepository()->save(
                $this->getGateway()->getResource($event->id)
            );
        } catch (ResourceNotFoundException) {
            $this->getRepository()->delete($event->id);
        }
    }

    abstract protected function getRepository(): CachedEntityRepositoryInterface;

    abstract protected function getGateway(): ResourceGatewayInterface;
}
