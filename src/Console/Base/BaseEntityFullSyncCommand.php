<?php

namespace Amqp\Console\Base;

use Amqp\Exceptions\ResourceGatewayConnectionException;
use Amqp\Gateways\ResourceGatewayInterface;
use Amqp\Repositories\CachedEntityRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Console\Command;

abstract class BaseEntityFullSyncCommand extends Command
{
    /**
     * @throws ResourceGatewayConnectionException
     */
    public function handle(): void
    {
        $entityRepository = $this->getEntityRepository();

        $lastSyncDateTime = $this->prepareLastSyncDateTime();
        foreach ($this->getResourceGateway()->getResources() as $resource) {
            $this->getEntityRepository()->save($resource);
        }

        if (! is_null($lastSyncDateTime)) {
            $entityRepository->deleteNotSynced($lastSyncDateTime);
        }
    }

    abstract public function getResourceGateway(): ResourceGatewayInterface;

    abstract public function getEntityRepository(): CachedEntityRepositoryInterface;

    private function prepareLastSyncDateTime(): ?Carbon
    {
        if (! $lastSyncDateTimeAsString = $this->getEntityRepository()->getLastSyncDateTime()) {
            return null;
        }

        $lastSyncDateTime = Carbon::parse($lastSyncDateTimeAsString);
        $now = Carbon::now()->setTimezone($lastSyncDateTime->getTimezone());

        if ($lastSyncDateTime === $now) {
            sleep(1);
        }

        return $lastSyncDateTime;
    }
}
