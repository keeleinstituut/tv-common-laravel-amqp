<?php

namespace SyncTools\Listeners;

use SyncTools\Events\SyncEntityEvent;
use SyncTools\Repositories\CachedEntityRepositoryInterface;

abstract class EntityDeleteEventListener
{
    public function handle(SyncEntityEvent $event): void
    {
        $this->getRepository()->delete($event->id);
    }

    abstract protected function getRepository(): CachedEntityRepositoryInterface;
}
