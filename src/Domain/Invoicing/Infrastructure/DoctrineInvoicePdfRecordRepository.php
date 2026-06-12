<?php

declare(strict_types=1);

namespace App\Domain\Invoicing\Infrastructure;

use App\Domain\Invoicing\Entity\Invoice;
use App\Domain\Invoicing\Entity\InvoicePdfRecord;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<InvoicePdfRecord>
 */
class DoctrineInvoicePdfRecordRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, InvoicePdfRecord::class);
    }

    public function save(InvoicePdfRecord $record): void
    {
        $em = $this->getEntityManager();
        $em->detach($record->getInvoice());
        $em->persist($record);
        $em->flush();
    }

    /** @return InvoicePdfRecord[] */
    public function findByInvoice(Invoice $invoice): array
    {
        return $this->findBy(['invoice' => $invoice], ['generatedAt' => 'DESC']);
    }
}
