<?php

namespace SyncTools\Console\Base;

use Carbon\Carbon;
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

        $lastSyncDateTime = $this->prepareLastSyncDateTime();
        foreach ($this->getResourceGateway()->getResources() as $resource) {
            $this->getEntityRepository()->save($resource);
        }

        if (filled($lastSyncDateTime)) {
            $entityRepository->deleteNotSynced($lastSyncDateTime);
        }
    }

    abstract public function getResourceGateway(): ResourceGatewayInterface;

    abstract public function getEntityRepository(): CachedEntityRepositoryInterface;

    private function prepareLastSyncDateTime(): ?Carbon
    {
        if (empty($lastSyncDateTimeAsString = $this->getEntityRepository()->getLastSyncDateTime())) {
            return null;
        }

        $lastSyncDateTime = Carbon::parse($lastSyncDateTimeAsString);
        if ($lastSyncDateTime === Carbon::now()->setTimezone($lastSyncDateTime->getTimezone())) {
            sleep(1);
        }

        return $lastSyncDateTime;
    }
}
