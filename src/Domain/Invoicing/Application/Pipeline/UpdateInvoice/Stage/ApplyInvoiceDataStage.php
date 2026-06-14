<?php

declare(strict_types=1);

namespace App\Domain\Invoicing\Application\Pipeline\UpdateInvoice\Stage;

use App\Domain\Customer\Repository\CustomerRepositoryInterface;
use App\Domain\Invoicing\Application\Pipeline\UpdateInvoice\UpdateInvoiceCommand;
use App\Domain\Invoicing\Entity\InvoiceItem;
use App\Domain\Project\Repository\ProjectRepositoryInterface;
use App\Infrastructure\Pipeline\PipelineHandlerInterface;
use DateTimeImmutable;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('app.invoice.update', attributes: ['priority' => 200])]
final class ApplyInvoiceDataStage implements PipelineHandlerInterface
{
    public function __construct(
        private readonly CustomerRepositoryInterface $customerRepository,
        private readonly ProjectRepositoryInterface $projectRepository,
    ) {
    }

    public function handle(mixed $payload): mixed
    {
        assert($payload instanceof UpdateInvoiceCommand);

        $invoice = $payload->invoice;
        $data    = $payload->data;

        $customer = $this->customerRepository->findById($data->customerId);
        if ($customer !== null) {
            $invoice->setCustomer($customer);
        }

        $invoice->setCurrency($data->currency);
        $invoice->setDefaultTaxRate($data->defaultTaxRate);
        $invoice->setNotes($data->notes !== '' ? $data->notes : null);
        $invoice->setPaymentTerms($data->paymentTerms !== '' ? $data->paymentTerms : null);
        $invoice->setBankAccount($data->bankAccount !== '' ? $data->bankAccount : null);

        $issuedAt = DateTimeImmutable::createFromFormat('Y-m-d', $data->issuedAt);
        $dueAt    = DateTimeImmutable::createFromFormat('Y-m-d', $data->dueAt);
        if ($issuedAt !== false) {
            $invoice->setIssuedAt($issuedAt);
        }
        if ($dueAt !== false) {
            $invoice->setDueAt($dueAt);
        }

        $invoice->setProject($data->projectId !== '' ? $this->projectRepository->findById($data->projectId) : null);

        foreach ($invoice->getItems()->toArray() as $existingItem) {
            $invoice->removeItem($existingItem);
        }

        foreach ($data->items as $i => $itemData) {
            $item = new InvoiceItem($invoice);
            $item->setDescription($itemData['description']);
            $item->setQuantity($itemData['quantity']);
            $item->setUnit($itemData['unit']);
            $item->setUnitPrice($itemData['unitPrice']);
            $item->setTaxRate($itemData['taxRate']);
            $item->setSortOrder($i);
            $invoice->addItem($item);
        }

        return $payload;
    }
}
