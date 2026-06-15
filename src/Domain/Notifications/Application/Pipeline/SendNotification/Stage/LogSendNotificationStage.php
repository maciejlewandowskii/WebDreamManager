<?php

declare(strict_types=1);

namespace App\Domain\Notifications\Application\Pipeline\SendNotification\Stage;

use App\Domain\Notifications\Application\Pipeline\SendNotification\SendNotificationCommand;
use App\Infrastructure\Pipeline\AbstractLogStage;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('app.notifications.send', attributes: ['priority' => -200])]
final readonly class LogSendNotificationStage extends AbstractLogStage
{
    public function handle(mixed $payload): mixed
    {
        assert($payload instanceof SendNotificationCommand);

        $this->logSystem(
            'Notification sent to ' . $payload->recipient->getEmail() . ': ' . $payload->emailSubject,
            'notifications',
            ['recipient_id' => $payload->recipient->getId(), 'recipient_email' => $payload->recipient->getEmail(), 'rule' => $payload->rule->getEventName()],
        );

        return $payload;
    }
}
