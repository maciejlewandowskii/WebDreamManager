<?php

declare(strict_types=1);

namespace App\Domain\Invoicing\Infrastructure;

use App\Domain\Invoicing\Entity\Quote;
use App\Domain\Invoicing\Entity\QuotePdfRecord;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<QuotePdfRecord>
 */
class DoctrineQuotePdfRecordRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, QuotePdfRecord::class);
    }

    public function save(QuotePdfRecord $record): void
    {
        $em = $this->getEntityManager();
        $em->detach($record->getQuote());
        $em->persist($record);
        $em->flush();
    }

    /** @return QuotePdfRecord[] */
    public function findByQuote(Quote $quote): array
    {
        return $this->findBy(['quote' => $quote], ['generatedAt' => 'DESC']);
    }
}
