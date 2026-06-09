<?php

declare(strict_types=1);

namespace App\Domain\Invoicing\Port;

use App\Domain\Invoicing\Entity\Invoice;

interface PaymentGatewayInterface
{
    /** @return array{id: string, url: string, status: string} */
    public function createPaymentLink(Invoice $invoice): array;

    public function getPaymentStatus(string $externalId): string;

    public function cancelPayment(string $externalId): bool;
}
