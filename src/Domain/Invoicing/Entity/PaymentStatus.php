<?php

declare(strict_types=1);

namespace App\Domain\Invoicing\Entity;

enum PaymentStatus: string
{
    case Paid    = 'paid';
    case Unpaid  = 'unpaid';
    case Unknown = 'unknown';
}
