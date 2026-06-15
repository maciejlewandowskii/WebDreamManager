<?php

declare(strict_types=1);

namespace App\Domain\Invoicing\Application\Pipeline\MarkInvoicePaid\Stage;

use App\Domain\Invoicing\Application\Pipeline\MarkInvoicePaid\MarkInvoicePaidCommand;
use App\Infrastructure\Pipeline\AbstractLogStage;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('app.invoice.mark_paid', attributes: ['priority' => -200])]
final readonly class LogMarkInvoicePaidStage extends AbstractLogStage
{
    public function handle(mixed $payload): mixed
    {
        assert($payload instanceof MarkInvoicePaidCommand);

        $this->logSystem(
            "Invoice marked as paid: #{$payload->invoice->getNumber()}",
            'invoices',
            ['invoice_id' => $payload->invoice->getId(), 'number' => $payload->invoice->getNumber(), 'total' => $payload->invoice->getGrossTotal(), 'currency' => $payload->invoice->getCurrency()],
        );

        return $payload;
    }
}
