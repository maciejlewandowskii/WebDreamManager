<?php

declare(strict_types=1);

namespace App\Domain\Invoicing\Entity;

use App\Domain\Invoicing\Infrastructure\DoctrineInvoicePdfRecordRepository;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: DoctrineInvoicePdfRecordRepository::class)]
#[ORM\Table(name: 'invoice_pdf_records')]
class InvoicePdfRecord
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    private Uuid $id;

    #[ORM\ManyToOne(targetEntity: Invoice::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Invoice $invoice;

    #[ORM\Column(type: 'string', length: 20)]
    private string $colorMode;

    #[ORM\Column(type: 'string', length: 500)]
    private string $filePath;

    #[ORM\Column(type: 'string', length: 200)]
    private string $fileName;

    #[ORM\Column(type: 'datetime_immutable')]
    private DateTimeImmutable $generatedAt;

    public function __construct(Invoice $invoice, string $colorMode, string $filePath, string $fileName)
    {
        $this->invoice     = $invoice;
        $this->colorMode   = $colorMode;
        $this->filePath    = $filePath;
        $this->fileName    = $fileName;
        $this->generatedAt = new DateTimeImmutable();
    }

    public function getId(): string { return (string) $this->id; }
    public function getInvoice(): Invoice { return $this->invoice; }
    public function getColorMode(): string { return $this->colorMode; }
    public function getFilePath(): string { return $this->filePath; }
    public function getFileName(): string { return $this->fileName; }
    public function getGeneratedAt(): DateTimeImmutable { return $this->generatedAt; }
}
