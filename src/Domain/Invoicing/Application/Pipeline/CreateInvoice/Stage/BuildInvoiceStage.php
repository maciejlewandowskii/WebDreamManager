<?php

declare(strict_types=1);

namespace App\Domain\Invoicing\Application\Pipeline\CreateInvoice\Stage;

use App\Domain\Customer\Repository\CustomerRepositoryInterface;
use App\Domain\Invoicing\Application\Pipeline\CreateInvoice\CreateInvoiceCommand;
use App\Domain\Invoicing\Entity\Invoice;
use App\Domain\Invoicing\Entity\InvoiceItem;
use App\Domain\Invoicing\Repository\InvoiceRepositoryInterface;
use App\Domain\Project\Repository\ProjectRepositoryInterface;
use App\Infrastructure\Pipeline\PipelineHandlerInterface;
use DateTimeImmutable;
use RuntimeException;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('app.invoice.create', attributes: ['priority' => 200])]
final class BuildInvoiceStage implements PipelineHandlerInterface
{
    public function __construct(
        private readonly CustomerRepositoryInterface $customerRepository,
        private readonly InvoiceRepositoryInterface $invoiceRepository,
        private readonly ProjectRepositoryInterface $projectRepository,
    ) {
    }

    public function handle(mixed $payload): mixed
    {
        assert($payload instanceof CreateInvoiceCommand);

        $customer = $this->customerRepository->findById($payload->data->customerId);
        if ($customer === null) {
            throw new RuntimeException('Customer not found.');
        }

        $invoice = new Invoice($this->invoiceRepository->getNextNumber(), $customer);
        $invoice->setCurrency($payload->data->currency);
        $invoice->setDefaultTaxRate($payload->data->defaultTaxRate);
        $invoice->setNotes($payload->data->notes !== '' ? $payload->data->notes : null);
        $invoice->setPaymentTerms($payload->data->paymentTerms !== '' ? $payload->data->paymentTerms : null);
        $invoice->setBankAccount($payload->data->bankAccount !== '' ? $payload->data->bankAccount : null);

        $issuedAt = DateTimeImmutable::createFromFormat('Y-m-d', $payload->data->issuedAt);
        $dueAt    = DateTimeImmutable::createFromFormat('Y-m-d', $payload->data->dueAt);
        if ($issuedAt !== false) {
            $invoice->setIssuedAt($issuedAt);
        }
        if ($dueAt !== false) {
            $invoice->setDueAt($dueAt);
        }

        if ($payload->data->projectId !== '') {
            $invoice->setProject($this->projectRepository->findById($payload->data->projectId));
        }

        foreach ($payload->data->items as $i => $itemData) {
            $item = new InvoiceItem($invoice);
            $item->setDescription($itemData['description']);
            $item->setQuantity($itemData['quantity']);
            $item->setUnit($itemData['unit'] ?? 'unit');
            $item->setUnitPrice($itemData['unitPrice']);
            $item->setTaxRate($itemData['taxRate']);
            $item->setSortOrder($i);
            $invoice->addItem($item);
        }

        $payload->result = $invoice;

        return $payload;
    }
}
