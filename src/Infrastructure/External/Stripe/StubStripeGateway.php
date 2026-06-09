<?php

declare(strict_types=1);

namespace App\Infrastructure\External\Stripe;

use App\Domain\Invoicing\Entity\Invoice;
use App\Domain\Invoicing\Port\PaymentGatewayInterface;

final class StubStripeGateway implements PaymentGatewayInterface
{
    public function createPaymentLink(Invoice $invoice): array
    {
        return [
            'id' => 'stub_' . $invoice->getId(),
            'url' => 'https://stripe.com/stub-payment/' . $invoice->getNumber(),
            'status' => 'pending',
        ];
    }

    public function getPaymentStatus(string $externalId): string
    {
        return 'pending';
    }

    public function cancelPayment(string $externalId): bool
    {
        return true;
    }
}
