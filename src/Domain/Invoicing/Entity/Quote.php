<?php

declare(strict_types=1);

namespace App\Domain\Invoicing\Entity;

use App\Domain\Customer\Entity\Customer;
use App\Domain\Project\Entity\Project;
use App\Domain\Invoicing\Infrastructure\DoctrineQuoteRepository;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DoctrineQuoteRepository::class)]
#[ORM\Table(name: 'quotes')]
#[ORM\HasLifecycleCallbacks]
class Quote
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    private string $id;

    #[ORM\Column(type: 'string', length: 50, unique: true)]
    private string $number;

    #[ORM\ManyToOne(targetEntity: Customer::class)]
    #[ORM\JoinColumn(nullable: false)]
    private Customer $customer;

    #[ORM\ManyToOne(targetEntity: Project::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?Project $project = null;

    #[ORM\Column(type: 'string', length: 50, enumType: QuoteStatus::class)]
    private QuoteStatus $status = QuoteStatus::Draft;

    #[ORM\Column(type: 'date_immutable')]
    private DateTimeImmutable $issuedAt;

    #[ORM\Column(type: 'date_immutable', nullable: true)]
    private ?DateTimeImmutable $validUntil = null;

    #[ORM\Column(type: 'string', length: 3, options: ['default' => 'PLN'])]
    private string $currency = 'PLN';

    #[ORM\Column(type: 'decimal', precision: 5, scale: 2, options: ['default' => '23'])]
    private string $defaultTaxRate = '23';

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $notes = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $introText = null;

    /** @var Collection<int, QuoteItem> */
    #[ORM\OneToMany(targetEntity: QuoteItem::class, mappedBy: 'quote', cascade: ['persist', 'remove'], orphanRemoval: true)]
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
        $this->validUntil = new DateTimeImmutable('+30 days');
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

    public function getId(): string { return $this->id; }
    public function getNumber(): string { return $this->number; }
    public function setNumber(string $number): void { $this->number = $number; }
    public function getCustomer(): Customer { return $this->customer; }
    public function setCustomer(Customer $customer): void { $this->customer = $customer; }
    public function getProject(): ?Project { return $this->project; }
    public function setProject(?Project $project): void { $this->project = $project; }
    public function getStatus(): QuoteStatus { return $this->status; }
    public function setStatus(QuoteStatus $status): void { $this->status = $status; }
    public function getIssuedAt(): DateTimeImmutable { return $this->issuedAt; }
    public function setIssuedAt(DateTimeImmutable $issuedAt): void { $this->issuedAt = $issuedAt; }
    public function getValidUntil(): ?DateTimeImmutable { return $this->validUntil; }
    public function setValidUntil(?DateTimeImmutable $validUntil): void { $this->validUntil = $validUntil; }
    public function getCurrency(): string { return $this->currency; }
    public function setCurrency(string $currency): void { $this->currency = $currency; }
    public function getDefaultTaxRate(): string { return $this->defaultTaxRate; }
    public function setDefaultTaxRate(string $taxRate): void { $this->defaultTaxRate = $taxRate; }
    public function getNotes(): ?string { return $this->notes; }
    public function setNotes(?string $notes): void { $this->notes = $notes; }
    public function getIntroText(): ?string { return $this->introText; }
    public function setIntroText(?string $introText): void { $this->introText = $introText; }

    /** @return Collection<int, QuoteItem> */
    public function getItems(): Collection { return $this->items; }

    public function addItem(QuoteItem $item): void
    {
        if (!$this->items->contains($item)) {
            $this->items->add($item);
        }
    }

    public function removeItem(QuoteItem $item): void
    {
        $this->items->removeElement($item);
    }

    public function getNetTotal(): float
    {
        return array_sum($this->items->map(fn (QuoteItem $i) => $i->getNetTotal())->toArray());
    }

    public function getTaxTotal(): float
    {
        return array_sum($this->items->map(fn (QuoteItem $i) => $i->getTaxAmount())->toArray());
    }

    public function getGrossTotal(): float
    {
        return $this->getNetTotal() + $this->getTaxTotal();
    }

    public function getCreatedAt(): DateTimeImmutable { return $this->createdAt; }
    public function getUpdatedAt(): DateTimeImmutable { return $this->updatedAt; }
}
