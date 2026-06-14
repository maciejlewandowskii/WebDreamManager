<?php

declare(strict_types=1);

namespace App\UI\Component\Invoicing;

use App\Domain\Customer\Entity\Customer;
use App\Domain\Customer\Repository\CustomerRepositoryInterface;
use App\Domain\Invoicing\Application\Data\InvoiceFormData;
use App\Domain\Invoicing\Application\Pipeline\CreateInvoice\CreateInvoiceCommand;
use App\Domain\Invoicing\Application\Pipeline\UpdateInvoice\UpdateInvoiceCommand;
use App\Domain\Invoicing\Entity\InvoiceItem;
use App\Domain\Invoicing\Repository\InvoiceRepositoryInterface;
use App\Domain\Project\Entity\Project;
use App\Domain\Project\Repository\ProjectRepositoryInterface;
use App\Infrastructure\Pipeline\PipelineProcessor;
use DateInterval;
use DateTimeImmutable;
use App\Infrastructure\Pipeline\PipelineHandlerInterface;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveArg;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent]
final class InvoiceEditor
{
    use DefaultActionTrait;

    #[LiveProp]
    public ?string $invoiceId = null;

    #[LiveProp(writable: true, onUpdated: 'updatedCustomerId')]
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

    /** @var array<int, array{description: string, quantity: string, unit: string, unitPrice: string, taxRate: string}> */
    #[LiveProp(writable: true)]
    public array $items = [];

    /**
     * @param iterable<PipelineHandlerInterface> $createHandlers
     * @param iterable<PipelineHandlerInterface> $updateHandlers
     */
    public function __construct(
        private readonly InvoiceRepositoryInterface $invoiceRepository,
        private readonly CustomerRepositoryInterface $customerRepository,
        private readonly ProjectRepositoryInterface $projectRepository,
        private readonly RequestStack $requestStack,
        private readonly UrlGeneratorInterface $router,
        #[AutowireIterator('app.invoice.create')] private readonly iterable $createHandlers,
        #[AutowireIterator('app.invoice.update')] private readonly iterable $updateHandlers,
    ) {
    }

    #[LiveProp]
    public bool $isReadonly = false;

    public function mount(?string $invoiceId = null): void
    {
        if ($invoiceId !== null) {
            $this->invoiceId = $invoiceId;
            $invoice = $this->invoiceRepository->findById($invoiceId);
            if ($invoice !== null) {
                $this->isReadonly      = $invoice->getStatus() === \App\Domain\Invoicing\Entity\InvoiceStatus::Paid;
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
                        'unit'        => $i->getUnit(),
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
        $this->items    = [['description' => '', 'quantity' => '1', 'unit' => 'unit', 'unitPrice' => '0', 'taxRate' => '23']];
    }

    public function updatedCustomerId(): void
    {
        $rate = $this->resolveHourlyRate();
        $this->items = array_map(
            static fn (array $item) => $item['unit'] === 'h' ? array_merge($item, ['unitPrice' => $rate]) : $item,
            $this->items,
        );
    }

    #[LiveAction]
    public function addItem(): void
    {
        $rate = $this->resolveHourlyRate();
        $this->items[] = ['description' => '', 'quantity' => '1', 'unit' => 'h', 'unitPrice' => $rate, 'taxRate' => $this->defaultTaxRate];
    }

    private function resolveHourlyRate(): string
    {
        if ($this->customerId === '') {
            return '0';
        }
        $customer = $this->customerRepository->findById($this->customerId);

        return ($customer !== null && $customer->getHourlyRate() !== null) ? $customer->getHourlyRate() : '0';
    }

    #[LiveAction]
    public function removeItem(#[LiveArg] int $index): void
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);
    }

    #[LiveAction]
    public function save(): RedirectResponse
    {
        if ($this->isReadonly) {
            return new RedirectResponse($this->router->generate('app_invoice_show', ['id' => $this->invoiceId]));
        }

        $data                 = new InvoiceFormData();
        $data->customerId     = $this->customerId;
        $data->projectId      = $this->projectId;
        $data->issuedAt       = $this->issuedAt;
        $data->dueAt          = $this->dueAt;
        $data->currency       = $this->currency;
        $data->defaultTaxRate = $this->defaultTaxRate;
        $data->notes          = $this->notes;
        $data->paymentTerms   = $this->paymentTerms;
        $data->bankAccount    = $this->bankAccount;
        $data->items          = $this->items;

        if ($this->invoiceId !== null) {
            $invoice = $this->invoiceRepository->findById($this->invoiceId);
            if ($invoice === null) {
                return new RedirectResponse($this->router->generate('app_invoice_index'));
            }
            new PipelineProcessor($this->updateHandlers)->run(new UpdateInvoiceCommand($invoice, $data));
            $invoiceId = $invoice->getId();
        } else {
            $command = new CreateInvoiceCommand($data);
            new PipelineProcessor($this->createHandlers)->run($command);
            assert($command->result !== null);
            $invoiceId = $command->result->getId();
        }

        $session = $this->requestStack->getSession();
        if ($session instanceof \Symfony\Component\HttpFoundation\Session\FlashBagAwareSessionInterface) {
            $session->getFlashBag()->add('success', 'Invoice saved successfully.');
        }

        return new RedirectResponse($this->router->generate('app_invoice_show', ['id' => $invoiceId]));
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
