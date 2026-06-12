<?php

declare(strict_types=1);

namespace App\Domain\Invoicing\Application\Pipeline\PrepareInvoicePayment\Stage;

use App\Domain\Invoicing\Application\Pipeline\PrepareInvoicePayment\PrepareInvoicePaymentCommand;
use App\Domain\Invoicing\Port\PaymentGatewayInterface;
use App\Domain\Invoicing\Repository\InvoiceRepositoryInterface;
use App\Infrastructure\Pipeline\PipelineHandlerInterface;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('app.invoice.prepare_payment', attributes: ['priority' => 100])]
final readonly class CreatePaymentIntentStage implements PipelineHandlerInterface
{
    public function __construct(
        private PaymentGatewayInterface $gateway,
        private InvoiceRepositoryInterface $invoices,
    ) {}

    public function handle(mixed $payload): mixed
    {
        assert($payload instanceof PrepareInvoicePaymentCommand);

        $payload->clientSecret = $this->gateway->createEmbeddedCheckout($payload->invoice, '');
        $this->invoices->save($payload->invoice);

        return $payload;
    }
}
