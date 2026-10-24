<?php

declare(strict_types=1);

namespace App\Domain\IssueTracker\Enum;

enum IssueStatus: string
{
    case Open       = 'open';
    case Closed     = 'closed';
    case InProgress = 'in_progress';
    case Resolved   = 'resolved';
    case Cancelled  = 'cancelled';
    case Unknown    = 'unknown';

    public function label(): string
    {
        return match ($this) {
            self::Open       => 'Open',
            self::Closed     => 'Closed',
            self::InProgress => 'In Progress',
            self::Resolved   => 'Resolved',
            self::Cancelled  => 'Cancelled',
            self::Unknown    => 'Unknown',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Open       => 'green',
            self::InProgress => 'blue',
            self::Resolved   => 'purple',
            self::Closed, self::Cancelled, self::Unknown => 'gray',
        };
    }

    public function isOpen(): bool
    {
        return $this === self::Open || $this === self::InProgress;
    }
}
