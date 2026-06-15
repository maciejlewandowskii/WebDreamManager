<?php

declare(strict_types=1);

namespace App\Domain\Invoicing\Application\Pipeline\PrepareInvoicePayment\Stage;

use App\Domain\Invoicing\Application\Pipeline\PrepareInvoicePayment\PrepareInvoicePaymentCommand;
use App\Infrastructure\Pipeline\AbstractLogStage;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('app.invoice.prepare_payment', attributes: ['priority' => -200])]
final readonly class LogPrepareInvoicePaymentStage extends AbstractLogStage
{
    public function handle(mixed $payload): mixed
    {
        assert($payload instanceof PrepareInvoicePaymentCommand);

        $this->logSystem(
            "Payment session started for invoice #{$payload->invoice->getNumber()}",
            'invoices',
            ['invoice_id' => $payload->invoice->getId(), 'number' => $payload->invoice->getNumber(), 'total' => $payload->invoice->getGrossTotal()],
        );

        return $payload;
    }
}
