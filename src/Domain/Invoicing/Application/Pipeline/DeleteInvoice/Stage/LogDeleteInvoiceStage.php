<?php

declare(strict_types=1);

namespace App\Domain\Invoicing\Application\Pipeline\DeleteInvoice\Stage;

use App\Domain\Invoicing\Application\Pipeline\DeleteInvoice\DeleteInvoiceCommand;
use App\Infrastructure\Pipeline\AbstractLogStage;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('app.invoice.delete', attributes: ['priority' => -200])]
final readonly class LogDeleteInvoiceStage extends AbstractLogStage
{
    public function handle(mixed $payload): mixed
    {
        assert($payload instanceof DeleteInvoiceCommand);

        $this->logUserAction(
            "Invoice deleted: #{$payload->invoice->getNumber()}",
            'invoices',
            ['id' => $payload->invoice->getId(), 'number' => $payload->invoice->getNumber(), 'customer' => $payload->invoice->getCustomer()->getName()],
        );

        return $payload;
    }
}
