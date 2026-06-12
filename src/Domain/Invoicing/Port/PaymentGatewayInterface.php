<?php

declare(strict_types=1);

namespace App\Domain\Invoicing\Port;

use App\Domain\Invoicing\Entity\Invoice;
use App\Domain\Invoicing\Entity\PaymentStatus;

interface PaymentGatewayInterface
{
    /**
     * Returns opaque config passed through to the client-side payment component.
     * Must include at minimum a 'type' key identifying the provider.
     *
     * @return array<string, mixed>
     */
    public function getClientConfig(): array;

    /**
     * Creates a payment session, binds it to the invoice (sets the session reference on the entity),
     * and returns the client secret needed to mount the payment form.
     */
    public function createEmbeddedCheckout(Invoice $invoice, string $returnUrl): string;

    public function getPaymentStatus(string $sessionId): PaymentStatus;

    public function cancelPayment(string $sessionId): bool;
}
