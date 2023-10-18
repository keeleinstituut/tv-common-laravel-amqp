<?php

namespace AuditLogClient\Services;

use AuditLogClient\DataTransferObjects\AuditLogEvent;
use Illuminate\Support\Facades\Config;
use Illuminate\Validation\ValidationException;
use SyncTools\AmqpPublisher;
use Throwable;

readonly class AuditLogPublisher
{
    public function __construct(
        private AmqpPublisher $publisher,
        private AuditLogEventValidationService $validationService
    ) {}

    /**
     * @throws ValidationException
     * @throws Throwable
     */
    public function publish(AuditLogEvent $auditLogEvent): void {
        $validator = $this->validationService->makeValidator($auditLogEvent->toArray());
        $validator->validate();

        $exchange = Config::get('amqp.audit-logs_exchange');
        throw_if(empty($exchange), 'Exchange name has not been declared.');

        $this->publisher->publish($validator->validated(), $exchange);
    }
}
