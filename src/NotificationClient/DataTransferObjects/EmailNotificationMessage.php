<?php

namespace NotificationClient\DataTransferObjects;

use InvalidArgumentException;
use NotificationClient\Enums\NotificationType;
use Throwable;

readonly class EmailNotificationMessage
{
    /**
     * @throws Throwable
     */
    public function __construct(
        public NotificationType $notificationType,
        public string $receiverEmail,
        public array $templateVariables = [],
        public ?string $receiverName = null
    )
    {
        throw_if(
            filter_var($this->receiverEmail, FILTER_VALIDATE_EMAIL) === false,
            'Email has incorrect format'
        );
    }

    /**
     * @throws Throwable
     */
    public static function make(array $params): static
    {
        $notificationType = data_get($params, 'notification_type');
        if (is_string($notificationType)) {
            $notificationType = NotificationType::tryFrom($notificationType);
        }

        if (empty($notificationType)) {
            throw new InvalidArgumentException('Not supported notification type passed');
        }

        return new static(
            $notificationType,
            data_get($params, 'receiver_email', ''),
            data_get($params, 'variables', []),
            data_get($params, 'receiver_name'),
        );
    }

    public function toArray(): array
    {
        return [
            'receiver_email' => $this->receiverEmail,
            'receiver_name' => $this->receiverName,
            'type' => $this->notificationType->value,
            'variables' => $this->templateVariables
        ];
    }
}