<?php

declare(strict_types=1);

namespace App\Domain\Invoicing\Application\Pipeline\CreateInvoiceFromTimeTracking\Stage;

use App\Domain\Invoicing\Application\Pipeline\CreateInvoiceFromTimeTracking\CreateInvoiceFromTimeTrackingCommand;
use App\Domain\Invoicing\Entity\Invoice;
use App\Domain\Invoicing\Entity\InvoiceItem;
use App\Domain\Invoicing\Repository\InvoiceRepositoryInterface;
use App\Domain\TimeTracking\Repository\TimeRecordRepositoryInterface;
use App\Infrastructure\Pipeline\PipelineHandlerInterface;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('app.invoice.from_time_tracking', attributes: ['priority' => 200])]
final class BuildInvoiceFromTimeRecordsStage implements PipelineHandlerInterface
{
    public function __construct(
        private readonly InvoiceRepositoryInterface $invoiceRepository,
        private readonly TimeRecordRepositoryInterface $timeRecordRepository,
    ) {}

    public function handle(mixed $payload): mixed
    {
        assert($payload instanceof CreateInvoiceFromTimeTrackingCommand);

        $project  = $payload->project;
        $customer = $project->getCustomer();
        $invoice  = new Invoice($this->invoiceRepository->getNextNumber(), $customer);
        $invoice->setProject($project);

        $sortOrder = 0;
        foreach ($payload->recordIds as $recordId) {
            $record = $this->timeRecordRepository->findById((string) $recordId);
            if ($record === null || $record->getProject()->getId() !== $project->getId()) {
                continue;
            }

            $label = $record->getTitle() . ' (' . $record->getDate()->format('d.m.Y') . ')';

            $item = new InvoiceItem($invoice);
            $item->setDescription($label);
            $item->setQuantity($record->getSpentHours());
            $item->setUnitPrice($customer->getHourlyRate() ?? '0');
            $item->setTaxRate($invoice->getDefaultTaxRate());
            $item->setSortOrder($sortOrder++);
            $invoice->addItem($item);

            $record->setInvoiced(true);
        }

        $payload->result = $invoice;

        return $payload;
    }
}
