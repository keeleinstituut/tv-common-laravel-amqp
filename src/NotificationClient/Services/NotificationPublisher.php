<?php

namespace NotificationClient\Services;

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
    public function publishEmailNotification(EmailNotificationMessage $message): void
    {
        $exchange = Config::get('amqp.notifications.email_notification_exchange');
        throw_if(empty($exchange), 'Exchange name has not been declared.');

        $this->publisher->publish(
            $message->toArray(),
            $exchange,
            headers: ['jwt' => $this->jwtRetriever->getJwt()]
        );
    }
}