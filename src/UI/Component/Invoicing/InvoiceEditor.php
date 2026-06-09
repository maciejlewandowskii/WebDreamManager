<?php

declare(strict_types=1);

namespace App\UI\Component\Invoicing;

use App\Domain\Customer\Entity\Customer;
use App\Domain\Customer\Repository\CustomerRepositoryInterface;
use App\Domain\Invoicing\Entity\Invoice;
use App\Domain\Invoicing\Entity\InvoiceItem;
use App\Domain\Invoicing\Repository\InvoiceRepositoryInterface;
use App\Domain\Project\Entity\Project;
use App\Domain\Project\Repository\ProjectRepositoryInterface;
use DateInterval;
use DateTimeImmutable;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent]
final class InvoiceEditor
{
    use DefaultActionTrait;

    #[LiveProp]
    public ?string $invoiceId = null;

    #[LiveProp(writable: true)]
    public string $customerId = '';

    #[LiveProp(writable: true)]
    public string $projectId = '';

    #[LiveProp(writable: true)]
    public string $issuedAt = '';

    #[LiveProp(writable: true)]
    public string $dueAt = '';

    #[LiveProp(writable: true)]
    public string $currency = 'PLN';

    #[LiveProp(writable: true)]
    public string $defaultTaxRate = '23';

    #[LiveProp(writable: true)]
    public string $notes = '';

    #[LiveProp(writable: true)]
    public string $paymentTerms = '';

    #[LiveProp(writable: true)]
    public string $bankAccount = '';

    /** @var array<int, array{description: string, quantity: string, unitPrice: string, taxRate: string}> */
    #[LiveProp(writable: true)]
    public array $items = [];

    private ?Invoice $invoice = null;

    public function __construct(
        private readonly InvoiceRepositoryInterface $invoiceRepository,
        private readonly CustomerRepositoryInterface $customerRepository,
        private readonly ProjectRepositoryInterface $projectRepository,
    ) {
    }

    public function mount(?string $invoiceId = null): void
    {
        if ($invoiceId !== null) {
            $this->invoiceId = $invoiceId;
            $invoice = $this->invoiceRepository->findById($invoiceId);
            if ($invoice !== null) {
                $this->invoice         = $invoice;
                $this->customerId      = $invoice->getCustomer()->getId();
                $this->projectId       = $invoice->getProject()?->getId() ?? '';
                $this->issuedAt        = $invoice->getIssuedAt()->format('Y-m-d');
                $this->dueAt           = $invoice->getDueAt()->format('Y-m-d');
                $this->currency        = $invoice->getCurrency();
                $this->defaultTaxRate  = $invoice->getDefaultTaxRate();
                $this->notes           = $invoice->getNotes() ?? '';
                $this->paymentTerms    = $invoice->getPaymentTerms() ?? '';
                $this->bankAccount     = $invoice->getBankAccount() ?? '';
                $this->items = array_values($invoice->getItems()->map(
                    static fn (InvoiceItem $i) => [
                        'description' => $i->getDescription(),
                        'quantity'    => $i->getQuantity(),
                        'unitPrice'   => $i->getUnitPrice(),
                        'taxRate'     => $i->getTaxRate(),
                    ],
                )->toArray());
                return;
            }
        }

        $today = new DateTimeImmutable();
        $this->issuedAt = $today->format('Y-m-d');
        $this->dueAt    = $today->add(new DateInterval('P30D'))->format('Y-m-d');
        $this->items    = [['description' => '', 'quantity' => '1', 'unitPrice' => '0', 'taxRate' => '23']];
    }

    #[LiveAction]
    public function addItem(): void
    {
        $this->items[] = ['description' => '', 'quantity' => '1', 'unitPrice' => '0', 'taxRate' => $this->defaultTaxRate];
    }

    #[LiveAction]
    public function removeItem(int $index): void
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);
    }

    #[LiveAction]
    public function save(): void
    {
        $customer = $this->customerRepository->findById($this->customerId);
        if ($customer === null) {
            return;
        }

        if ($this->invoice !== null) {
            $invoice = $this->invoice;
        } else {
            $number  = $this->invoiceRepository->getNextNumber();
            $invoice = new Invoice($number, $customer);
        }

        $invoice->setCustomer($customer);
        $invoice->setCurrency($this->currency);
        $invoice->setDefaultTaxRate($this->defaultTaxRate);
        $invoice->setNotes($this->notes !== '' ? $this->notes : null);
        $invoice->setPaymentTerms($this->paymentTerms !== '' ? $this->paymentTerms : null);
        $invoice->setBankAccount($this->bankAccount !== '' ? $this->bankAccount : null);

        $issuedAt = DateTimeImmutable::createFromFormat('Y-m-d', $this->issuedAt);
        $dueAt    = DateTimeImmutable::createFromFormat('Y-m-d', $this->dueAt);
        if ($issuedAt !== false) {
            $invoice->setIssuedAt($issuedAt);
        }
        if ($dueAt !== false) {
            $invoice->setDueAt($dueAt);
        }

        if ($this->projectId !== '') {
            $invoice->setProject($this->projectRepository->findById($this->projectId));
        }

        foreach ($invoice->getItems()->toArray() as $existingItem) {
            $invoice->removeItem($existingItem);
        }

        foreach ($this->items as $i => $itemData) {
            $item = new InvoiceItem($invoice);
            $item->setDescription($itemData['description']);
            $item->setQuantity($itemData['quantity']);
            $item->setUnitPrice($itemData['unitPrice']);
            $item->setTaxRate($itemData['taxRate']);
            $item->setSortOrder($i);
            $invoice->addItem($item);
        }

        $this->invoiceRepository->save($invoice);
    }

    public function getNetTotal(): float
    {
        return array_sum(array_map(
            static fn (array $i) => (float) $i['quantity'] * (float) $i['unitPrice'],
            $this->items,
        ));
    }

    public function getTaxTotal(): float
    {
        return array_sum(array_map(
            static fn (array $i) => (float) $i['quantity'] * (float) $i['unitPrice'] * ((float) $i['taxRate'] / 100),
            $this->items,
        ));
    }

    public function getGrossTotal(): float
    {
        return $this->getNetTotal() + $this->getTaxTotal();
    }

    /** @return Customer[] */
    public function getAvailableCustomers(): array
    {
        return $this->customerRepository->findAll();
    }

    /** @return Project[] */
    public function getAvailableProjects(): array
    {
        if ($this->customerId === '') {
            return [];
        }
        $customer = $this->customerRepository->findById($this->customerId);

        return $customer !== null ? $this->projectRepository->findByCustomer($customer) : [];
    }
}
