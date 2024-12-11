<?php

namespace NotificationClient\Services;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use KeycloakAuthGuard\Services\ServiceAccountJwtRetrieverInterface;
use NotificationClient\DataTransferObjects\EmailNotificationMessage;
use SyncTools\AmqpPublisher;
use Throwable;

readonly class NotificationPublisher
{
    public function __construct(
        private AmqpPublisher $publisher,
        private ServiceAccountJwtRetrieverInterface $jwtRetriever
    )
    {
    }

    /**
     * @throws Throwable
     */
    public function publishEmailNotification(EmailNotificationMessage $message, $institutionId = null): void
    {
        $exchange = Config::get('amqp.notifications.email_notification_exchange');
        throw_if(empty($exchange), 'Exchange name has not been declared.');

        if ($institutionId == null) {
            $institutionId = Auth::getCustomClaimsTokenData('selectedInstitution.id');
        }

        $this->publisher->publish(
            $message->toArray(),
            $exchange,
            headers: [
                'jwt' => $this->jwtRetriever->getJwt(),
                'institutionId' => $institutionId,
            ]
        );
    }
}