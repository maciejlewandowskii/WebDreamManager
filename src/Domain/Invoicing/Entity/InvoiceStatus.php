<?php

declare(strict_types=1);

namespace App\Domain\Invoicing\Entity;

enum InvoiceStatus: string
{
    case Draft = 'draft';
    case Issued = 'issued';
    case Sent = 'sent';
    case Paid = 'paid';
    case Overdue = 'overdue';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match($this) {
            self::Draft     => 'Draft',
            self::Issued    => 'Issued',
            self::Sent      => 'Sent',
            self::Paid      => 'Paid',
            self::Overdue   => 'Overdue',
            self::Cancelled => 'Cancelled',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::Draft     => 'secondary',
            self::Issued, self::Sent => 'primary',
            self::Paid      => 'success',
            self::Overdue   => 'destructive',
            self::Cancelled => 'outline',
        };
    }
}
