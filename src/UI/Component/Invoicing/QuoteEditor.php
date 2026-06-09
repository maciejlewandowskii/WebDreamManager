<?php

declare(strict_types=1);

namespace App\UI\Component\Invoicing;

use App\Domain\Customer\Entity\Customer;
use App\Domain\Customer\Repository\CustomerRepositoryInterface;
use App\Domain\Invoicing\Entity\Quote;
use App\Domain\Invoicing\Entity\QuoteItem;
use App\Domain\Invoicing\Repository\QuoteRepositoryInterface;
use App\Domain\Project\Entity\Project;
use App\Domain\Project\Repository\ProjectRepositoryInterface;
use DateInterval;
use DateTimeImmutable;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent]
final class QuoteEditor
{
    use DefaultActionTrait;

    #[LiveProp]
    public ?string $quoteId = null;

    #[LiveProp(writable: true)]
    public string $customerId = '';

    #[LiveProp(writable: true)]
    public string $projectId = '';

    #[LiveProp(writable: true)]
    public string $issuedAt = '';

    #[LiveProp(writable: true)]
    public string $validUntil = '';

    #[LiveProp(writable: true)]
    public string $currency = 'PLN';

    #[LiveProp(writable: true)]
    public string $defaultTaxRate = '23';

    #[LiveProp(writable: true)]
    public string $notes = '';

    #[LiveProp(writable: true)]
    public string $introText = '';

    /** @var array<int, array{description: string, quantity: string, unitPrice: string, taxRate: string}> */
    #[LiveProp(writable: true)]
    public array $items = [];

    private ?Quote $quote = null;

    public function __construct(
        private readonly QuoteRepositoryInterface $quoteRepository,
        private readonly CustomerRepositoryInterface $customerRepository,
        private readonly ProjectRepositoryInterface $projectRepository,
    ) {
    }

    public function mount(?string $quoteId = null): void
    {
        if ($quoteId !== null) {
            $this->quoteId = $quoteId;
            $quote = $this->quoteRepository->findById($quoteId);
            if ($quote !== null) {
                $this->quote          = $quote;
                $this->customerId     = $quote->getCustomer()->getId();
                $this->projectId      = $quote->getProject()?->getId() ?? '';
                $this->issuedAt       = $quote->getIssuedAt()->format('Y-m-d');
                $this->validUntil     = $quote->getValidUntil()->format('Y-m-d');
                $this->currency       = $quote->getCurrency();
                $this->defaultTaxRate = $quote->getDefaultTaxRate();
                $this->notes          = $quote->getNotes() ?? '';
                $this->introText      = $quote->getIntroText() ?? '';
                $this->items = array_values($quote->getItems()->map(
                    static fn (QuoteItem $i) => [
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
        $this->issuedAt   = $today->format('Y-m-d');
        $due = $today->add(new DateInterval('P14D'));
        $this->validUntil = $due->format('Y-m-d');
        $this->items      = [['description' => '', 'quantity' => '1', 'unitPrice' => '0', 'taxRate' => '23']];
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

        if ($this->quote !== null) {
            $quote = $this->quote;
        } else {
            $number = $this->quoteRepository->getNextNumber();
            $quote  = new Quote($number, $customer);
        }

        $quote->setCustomer($customer);
        $quote->setCurrency($this->currency);
        $quote->setDefaultTaxRate($this->defaultTaxRate);
        $quote->setNotes($this->notes !== '' ? $this->notes : null);
        $quote->setIntroText($this->introText !== '' ? $this->introText : null);

        $issuedAt   = DateTimeImmutable::createFromFormat('Y-m-d', $this->issuedAt);
        $validUntil = DateTimeImmutable::createFromFormat('Y-m-d', $this->validUntil);
        if ($issuedAt !== false) {
            $quote->setIssuedAt($issuedAt);
        }
        if ($validUntil !== false) {
            $quote->setValidUntil($validUntil);
        }

        if ($this->projectId !== '') {
            $quote->setProject($this->projectRepository->findById($this->projectId));
        }

        foreach ($quote->getItems()->toArray() as $existingItem) {
            $quote->removeItem($existingItem);
        }

        foreach ($this->items as $i => $itemData) {
            $item = new QuoteItem($quote);
            $item->setDescription($itemData['description']);
            $item->setQuantity($itemData['quantity']);
            $item->setUnitPrice($itemData['unitPrice']);
            $item->setTaxRate($itemData['taxRate']);
            $item->setSortOrder($i);
            $quote->addItem($item);
        }

        $this->quoteRepository->save($quote);
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
