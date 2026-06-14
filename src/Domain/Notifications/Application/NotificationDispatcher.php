<?php

declare(strict_types=1);

namespace App\Domain\Notifications\Application;

use App\Infrastructure\Messenger\Notification\DispatchNotificationMessage;
use Symfony\Component\Messenger\MessageBusInterface;

final readonly class NotificationDispatcher
{
    public function __construct(private MessageBusInterface $bus) {}

    /** @param array<string, mixed> $templateContext */
    public function dispatch(
        string $eventName,
        string $emailSubject,
        string $emailTemplate,
        string $smsText,
        array $templateContext = [],
    ): void {
        $this->bus->dispatch(new DispatchNotificationMessage(
            $eventName,
            $emailSubject,
            $emailTemplate,
            $smsText,
            $templateContext,
        ));
    }
}
