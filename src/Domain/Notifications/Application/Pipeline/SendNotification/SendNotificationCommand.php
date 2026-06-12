<?php

declare(strict_types=1);

namespace App\Domain\Notifications\Application\Pipeline\SendNotification;

use App\Domain\Identity\Entity\User;
use App\Domain\Notifications\Entity\NotificationRule;

final readonly class SendNotificationCommand
{
    public function __construct(
        public NotificationRule $rule,
        public User $recipient,
        public string $emailSubject,
        public string $emailTemplate,
        public string $smsText,
        public array $templateContext,
    ) {}
}
