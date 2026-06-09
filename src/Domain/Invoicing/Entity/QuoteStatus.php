<?php

declare(strict_types=1);

namespace App\Domain\Invoicing\Entity;

enum QuoteStatus: string
{
    case Draft = 'draft';
    case Sent = 'sent';
    case Accepted = 'accepted';
    case Rejected = 'rejected';
    case Expired = 'expired';

    public function label(): string
    {
        return match($this) {
            self::Draft    => 'Draft',
            self::Sent     => 'Sent',
            self::Accepted => 'Accepted',
            self::Rejected => 'Rejected',
            self::Expired  => 'Expired',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::Draft    => 'secondary',
            self::Sent     => 'primary',
            self::Accepted => 'success',
            self::Rejected => 'destructive',
            self::Expired  => 'outline',
        };
    }
}
