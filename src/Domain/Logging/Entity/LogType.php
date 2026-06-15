<?php

declare(strict_types=1);

namespace App\Domain\Logging\Entity;

enum LogType: string
{
    case SystemLog       = 'system';
    case UserAction      = 'user_action';
    case ExternalService = 'external_service';

    public function label(): string
    {
        return match ($this) {
            self::SystemLog       => 'System',
            self::UserAction      => 'User Action',
            self::ExternalService => 'External Service',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::SystemLog       => 'secondary',
            self::UserAction      => 'primary',
            self::ExternalService => 'warning',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::SystemLog       => 'tabler:server',
            self::UserAction      => 'tabler:user',
            self::ExternalService => 'tabler:plug',
        };
    }
}
