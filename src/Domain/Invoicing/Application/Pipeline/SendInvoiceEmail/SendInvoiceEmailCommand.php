<?php

declare(strict_types=1);

namespace App\Domain\Invoicing\Application\Pipeline\SendInvoiceEmail;

use App\Domain\Invoicing\Entity\Invoice;

final readonly class SendInvoiceEmailCommand
{
    public function __construct(
        public Invoice $invoice,
        public ?string $pdfId,
    ) {}
}
