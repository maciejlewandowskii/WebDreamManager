<?php

declare(strict_types=1);

namespace App\Domain\Invoicing\Application\Pipeline\CreateInvoice\Stage;

use App\Domain\Invoicing\Application\Pipeline\CreateInvoice\CreateInvoiceCommand;
use App\Infrastructure\Pipeline\AbstractLogStage;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('app.invoice.create', attributes: ['priority' => -200])]
final readonly class LogCreateInvoiceStage extends AbstractLogStage
{
    public function handle(mixed $payload): mixed
    {
        assert($payload instanceof CreateInvoiceCommand);
        assert($payload->result !== null);

        $this->logUserAction(
            "Invoice created: #{$payload->result->getNumber()}",
            'invoices',
            ['id' => $payload->result->getId(), 'number' => $payload->result->getNumber(), 'customer' => $payload->result->getCustomer()->getName()],
        );

        return $payload;
    }
}
