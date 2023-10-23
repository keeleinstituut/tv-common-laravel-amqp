<?php

namespace AuditLogClient\Services;

use AuditLogClient\DataTransferObjects\AuditLogMessage;
use Illuminate\Support\Facades\Config;
use Illuminate\Validation\ValidationException;
use KeycloakAuthGuard\Services\ServiceAccountJwtRetrieverInterface;
use SyncTools\AmqpPublisher;
use Throwable;

readonly class AuditLogPublisher
{
    public function __construct(
        private AmqpPublisher $publisher,
        private AuditLogMessageValidationService $validationService,
        private ServiceAccountJwtRetrieverInterface $jwtRetriever
    ) {
    }

    /**
     * @throws ValidationException
     * @throws Throwable
     */
    public function publish(AuditLogMessage $auditLogEvent): void
    {
        $validator = $this->validationService->makeValidator($auditLogEvent->toArray());
        $validator->validate();

        $exchange = Config::get('amqp.audit_logs.exchange');
        throw_if(empty($exchange), 'Exchange name has not been declared.');

        $jwt = $this->jwtRetriever->getJwt();

        $this->publisher->publish(
            $validator->validated(),
            $exchange,
            headers: ['jwt' => $jwt]
        );
    }
}
