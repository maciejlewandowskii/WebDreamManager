<?php

declare(strict_types=1);

namespace App\Domain\Notifications\Entity;

enum NotificationChannelType: string
{
    case Email = 'email';
    case Sms   = 'sms';

    public function label(): string
    {
        return match ($this) {
            self::Email => 'Email',
            self::Sms   => 'SMS',
        };
    }
}
