<?php

declare(strict_types=1);

namespace App\Infrastructure\External\IFirma;

use App\Domain\Invoicing\Entity\Invoice;
use App\Domain\Invoicing\Port\AccountingServiceInterface;
final class StubIFirmaGateway implements AccountingServiceInterface
{
    public function exportInvoice(Invoice $invoice): string
    {
        return sprintf(
            '{"number":"%s","stub":true}',
            $invoice->getNumber(),
        );
    }

    public function syncInvoice(Invoice $invoice): bool
    {
        return true;
    }

    public function isConfigured(): bool
    {
        return false;
    }
}
