<?php

declare(strict_types=1);

namespace App\Domain\Invoicing\Port;

use App\Domain\Invoicing\Entity\Invoice;

interface AccountingServiceInterface
{
    public function exportInvoice(Invoice $invoice): string;

    public function syncInvoice(Invoice $invoice): bool;

    public function isConfigured(): bool;
}
