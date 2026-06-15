<?php

declare(strict_types=1);

namespace App\Domain\Invoicing\Application\Pipeline\CreateInvoiceFromTimeTracking\Stage;

use App\Domain\Invoicing\Application\Pipeline\CreateInvoiceFromTimeTracking\CreateInvoiceFromTimeTrackingCommand;
use App\Infrastructure\Pipeline\AbstractLogStage;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('app.invoice.from_time_tracking', attributes: ['priority' => -200])]
final readonly class LogCreateInvoiceFromTimeTrackingStage extends AbstractLogStage
{
    public function handle(mixed $payload): mixed
    {
        assert($payload instanceof CreateInvoiceFromTimeTrackingCommand);
        assert($payload->result !== null);

        $this->logUserAction(
            "Invoice created from time tracking: #{$payload->result->getNumber()}",
            'invoices',
            ['id' => $payload->result->getId(), 'number' => $payload->result->getNumber(), 'project' => $payload->project->getName(), 'records' => count($payload->recordIds)],
        );

        return $payload;
    }
}
