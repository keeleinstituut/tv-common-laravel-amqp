<?php

namespace Amqp\Listeners;

use Amqp\Events\SyncEntityEvent;
use Amqp\Repositories\CachedEntityRepositoryInterface;

abstract class EntityDeleteEventListener
{
    public function handle(SyncEntityEvent $event): void
    {
        $this->getRepository()->delete($event->id);
    }

    abstract protected function getRepository(): CachedEntityRepositoryInterface;
}
