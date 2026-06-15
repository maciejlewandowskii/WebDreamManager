<?php

declare(strict_types=1);

namespace App\Domain\Invoicing\Application\Pipeline\UpdateInvoice\Stage;

use App\Domain\Invoicing\Application\Pipeline\UpdateInvoice\UpdateInvoiceCommand;
use App\Infrastructure\Pipeline\AbstractLogStage;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('app.invoice.update', attributes: ['priority' => -200])]
final readonly class LogUpdateInvoiceStage extends AbstractLogStage
{
    public function handle(mixed $payload): mixed
    {
        assert($payload instanceof UpdateInvoiceCommand);

        $this->logUserAction(
            "Invoice updated: #{$payload->invoice->getNumber()}",
            'invoices',
            ['id' => $payload->invoice->getId(), 'number' => $payload->invoice->getNumber(), 'customer' => $payload->invoice->getCustomer()->getName()],
        );

        return $payload;
    }
}
