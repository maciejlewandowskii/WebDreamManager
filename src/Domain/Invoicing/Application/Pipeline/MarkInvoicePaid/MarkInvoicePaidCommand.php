<?php

declare(strict_types=1);

namespace App\Domain\Invoicing\Application\Pipeline\MarkInvoicePaid;

use App\Domain\Invoicing\Entity\Invoice;

final readonly class MarkInvoicePaidCommand
{
    public function __construct(public Invoice $invoice) {}
}
