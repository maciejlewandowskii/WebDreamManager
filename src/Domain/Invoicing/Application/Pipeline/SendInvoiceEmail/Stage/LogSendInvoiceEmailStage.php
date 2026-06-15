<?php

declare(strict_types=1);

namespace App\Domain\Invoicing\Application\Pipeline\SendInvoiceEmail\Stage;

use App\Domain\Invoicing\Application\Pipeline\SendInvoiceEmail\SendInvoiceEmailCommand;
use App\Infrastructure\Pipeline\AbstractLogStage;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('app.invoice.send_email', attributes: ['priority' => -200])]
final readonly class LogSendInvoiceEmailStage extends AbstractLogStage
{
    public function handle(mixed $payload): mixed
    {
        assert($payload instanceof SendInvoiceEmailCommand);

        $this->logUserAction(
            "Invoice email sent: #{$payload->invoice->getNumber()} to {$payload->invoice->getCustomer()->getName()}",
            'invoices',
            ['invoice_id' => $payload->invoice->getId(), 'number' => $payload->invoice->getNumber(), 'customer_email' => $payload->invoice->getCustomer()->getEmail()],
        );

        return $payload;
    }
}
