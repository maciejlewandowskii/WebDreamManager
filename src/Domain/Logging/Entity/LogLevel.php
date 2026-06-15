<?php

declare(strict_types=1);

namespace App\Domain\Logging\Entity;

enum LogLevel: string
{
    case Debug     = 'debug';
    case Info      = 'info';
    case Notice    = 'notice';
    case Warning   = 'warning';
    case Error     = 'error';
    case Critical  = 'critical';
    case Alert     = 'alert';
    case Emergency = 'emergency';

    public function label(): string
    {
        return match ($this) {
            self::Debug     => 'Debug',
            self::Info      => 'Info',
            self::Notice    => 'Notice',
            self::Warning   => 'Warning',
            self::Error     => 'Error',
            self::Critical  => 'Critical',
            self::Alert     => 'Alert',
            self::Emergency => 'Emergency',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Debug                                         => 'default',
            self::Info                                          => 'primary',
            self::Notice                                        => 'secondary',
            self::Warning                                       => 'warning',
            self::Error, self::Critical, self::Alert, self::Emergency => 'destructive',
        };
    }

    public function severity(): int
    {
        return match ($this) {
            self::Debug     => 0,
            self::Info      => 1,
            self::Notice    => 2,
            self::Warning   => 3,
            self::Error     => 4,
            self::Critical  => 5,
            self::Alert     => 6,
            self::Emergency => 7,
        };
    }
}
