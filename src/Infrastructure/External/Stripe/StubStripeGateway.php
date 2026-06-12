<?php

declare(strict_types=1);

namespace App\Infrastructure\External\Stripe;

use App\Domain\Invoicing\Entity\Invoice;
use App\Domain\Invoicing\Entity\PaymentStatus;
use App\Domain\Invoicing\Port\PaymentGatewayInterface;

final class StubStripeGateway implements PaymentGatewayInterface
{
    public function getClientConfig(): array
    {
        return ['type' => 'stub', 'publicKey' => 'stub_public_key'];
    }

    public function createEmbeddedCheckout(Invoice $invoice, string $returnUrl): string
    {
        $invoice->setStripeSessionId('stub_' . $invoice->getId());

        return 'stub_secret_' . $invoice->getId();
    }

    public function getPaymentStatus(string $sessionId): PaymentStatus
    {
        return PaymentStatus::Unpaid;
    }

    public function cancelPayment(string $sessionId): bool
    {
        return true;
    }
}
