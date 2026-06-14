<?php

declare(strict_types=1);

namespace App\Infrastructure\Messenger\Notification;

final readonly class DispatchNotificationMessage
{
    /**
     * @param array<string, mixed> $templateContext
     */
    public function __construct(
        public string $eventName,
        public string $emailSubject,
        public string $emailTemplate,
        public string $smsText,
        public array $templateContext,
    ) {}
}
