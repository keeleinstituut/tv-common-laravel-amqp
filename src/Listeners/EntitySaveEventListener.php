<?php

namespace Amqp\Listeners;

use Amqp\Events\SyncEntityEvent;
use Amqp\Exceptions\ResourceGatewayConnectionException;
use Amqp\Exceptions\ResourceNotFoundException;
use Amqp\Gateways\ResourceGatewayInterface;
use Amqp\Repositories\CachedEntityRepositoryInterface;

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
