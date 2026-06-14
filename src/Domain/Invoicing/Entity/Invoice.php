<?php

declare(strict_types=1);

namespace App\Domain\Invoicing\Entity;

use App\Domain\Customer\Entity\Customer;
use App\Domain\Project\Entity\Project;
use App\Domain\Invoicing\Infrastructure\DoctrineInvoiceRepository;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: DoctrineInvoiceRepository::class)]
#[ORM\Table(name: 'invoices')]
#[ORM\HasLifecycleCallbacks]
class Invoice
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    private Uuid $id;

    #[ORM\Column(type: 'string', length: 50, unique: true)]
    private string $number;

    #[ORM\ManyToOne(targetEntity: Customer::class)]
    #[ORM\JoinColumn(nullable: false)]
    private Customer $customer;

    #[ORM\ManyToOne(targetEntity: Project::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?Project $project = null;

    #[ORM\Column(type: 'string', length: 50, enumType: InvoiceStatus::class)]
    private InvoiceStatus $status = InvoiceStatus::Draft;

    #[ORM\Column(type: 'date_immutable')]
    private DateTimeImmutable $issuedAt;

    #[ORM\Column(type: 'date_immutable')]
    private DateTimeImmutable $dueAt;

    #[ORM\Column(type: 'string', length: 3, options: ['default' => 'PLN'])]
    private string $currency = 'PLN';

    #[ORM\Column(type: 'decimal', precision: 5, scale: 2, options: ['default' => '23'])]
    private string $defaultTaxRate = '23';

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $notes = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $paymentTerms = null;

    #[ORM\Column(type: 'string', length: 200, nullable: true)]
    private ?string $bankAccount = null;

    #[ORM\Column(type: 'string', length: 100, nullable: true)]
    private ?string $stripeSessionId = null;

    #[ORM\Column(type: 'string', length: 100, unique: true, nullable: true)]
    private ?string $paymentToken = null;

    /** @var Collection<int, InvoiceItem> */
    #[ORM\OneToMany(targetEntity: InvoiceItem::class, mappedBy: 'invoice', cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[ORM\OrderBy(['sortOrder' => 'ASC'])]
    private Collection $items;

    #[ORM\Column(type: 'datetime_immutable')]
    private DateTimeImmutable $createdAt;

    #[ORM\Column(type: 'datetime_immutable')]
    private DateTimeImmutable $updatedAt;

    /** @var array<int, array<string, mixed>>|null */
    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $itemsSnapshot = [];

    public function __construct(string $number, Customer $customer)
    {
        $this->number = $number;
        $this->customer = $customer;
        $this->items = new ArrayCollection();
        $this->issuedAt = new DateTimeImmutable();
        $this->dueAt = new DateTimeImmutable('+30 days');
        $this->createdAt = new DateTimeImmutable();
        $this->updatedAt = new DateTimeImmutable();
    }

    #[ORM\PreUpdate]
    #[ORM\PrePersist]
    public function onPreUpdate(): void
    {
        $this->updatedAt = new DateTimeImmutable();
        $this->itemsSnapshot = array_values(array_map(fn($i) => [
            'description' => $i->getDescription(),
            'qty' => $i->getQuantity(),
            'unit' => $i->getUnit(),
            'price' => $i->getUnitPrice(),
            'taxRate' => $i->getTaxRate(),
        ], $this->items->toArray()));
    }

    /** @return array<int, array<string, mixed>>|null */
    public function getItemsSnapshot(): ?array
    {
        return $this->itemsSnapshot;
    }

    public function getId(): string
    {
        return (string) $this->id;
    }

    public function getNumber(): string
    {
        return $this->number;
    }

    public function setNumber(string $number): void
    {
        $this->number = $number;
    }

    public function getCustomer(): Customer
    {
        return $this->customer;
    }

    public function setCustomer(Customer $customer): void
    {
        $this->customer = $customer;
    }

    public function getProject(): ?Project
    {
        return $this->project;
    }

    public function setProject(?Project $project): void
    {
        $this->project = $project;
    }

    public function getStatus(): InvoiceStatus
    {
        return $this->status;
    }

    public function setStatus(InvoiceStatus $status): void
    {
        $this->status = $status;
    }

    public function getIssuedAt(): DateTimeImmutable
    {
        return $this->issuedAt;
    }

    public function setIssuedAt(DateTimeImmutable $issuedAt): void
    {
        $this->issuedAt = $issuedAt;
    }

    public function getDueAt(): DateTimeImmutable
    {
        return $this->dueAt;
    }

    public function setDueAt(DateTimeImmutable $dueAt): void
    {
        $this->dueAt = $dueAt;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function setCurrency(string $currency): void
    {
        $this->currency = $currency;
    }

    public function getDefaultTaxRate(): string
    {
        return $this->defaultTaxRate;
    }

    public function setDefaultTaxRate(string $taxRate): void
    {
        $this->defaultTaxRate = $taxRate;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(?string $notes): void
    {
        $this->notes = $notes;
    }

    public function getPaymentTerms(): ?string
    {
        return $this->paymentTerms;
    }

    public function setPaymentTerms(?string $paymentTerms): void
    {
        $this->paymentTerms = $paymentTerms;
    }

    public function getBankAccount(): ?string
    {
        return $this->bankAccount;
    }

    public function setBankAccount(?string $bankAccount): void
    {
        $this->bankAccount = $bankAccount;
    }

    /** @return Collection<int, InvoiceItem> */
    public function getItems(): Collection
    {
        return $this->items;
    }

    public function addItem(InvoiceItem $item): void
    {
        if (!$this->items->contains($item)) {
            $this->items->add($item);
        }
    }

    public function removeItem(InvoiceItem $item): void
    {
        $this->items->removeElement($item);
    }

    public function getNetTotal(): float
    {
        return array_sum($this->items->map(fn (InvoiceItem $i) => $i->getNetTotal())->toArray());
    }

    public function getTaxTotal(): float
    {
        return array_sum($this->items->map(fn (InvoiceItem $i) => $i->getTaxAmount())->toArray());
    }

    public function getGrossTotal(): float
    {
        return $this->getNetTotal() + $this->getTaxTotal();
    }

    public function getStripeSessionId(): ?string
    {
        return $this->stripeSessionId;
    }

    public function setStripeSessionId(?string $stripeSessionId): void
    {
        $this->stripeSessionId = $stripeSessionId;
    }

    public function getPaymentToken(): ?string
    {
        return $this->paymentToken;
    }

    public function setPaymentToken(string $paymentToken): void
    {
        $this->paymentToken = $paymentToken;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): DateTimeImmutable
    {
        return $this->updatedAt;
    }
}
