<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Invoicing;

use App\Domain\Customer\Entity\Customer;
use App\Domain\Invoicing\Entity\Quote;
use App\Domain\Invoicing\Entity\QuoteStatus;
use App\Domain\Invoicing\Repository\QuoteRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Quote>
 */
final class DoctrineQuoteRepository extends ServiceEntityRepository implements QuoteRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Quote::class);
    }

    public function findById(string $id): ?Quote
    {
        return $this->find($id);
    }

    public function findByNumber(string $number): ?Quote
    {
        return $this->findOneBy(['number' => $number]);
    }

    public function findByCustomer(Customer $customer): array
    {
        return $this->createQueryBuilder('q')
            ->where('q.customer = :customer')
            ->setParameter('customer', $customer)
            ->orderBy('q.issuedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findByStatus(QuoteStatus $status): array
    {
        return $this->createQueryBuilder('q')
            ->where('q.status = :status')
            ->setParameter('status', $status->value)
            ->orderBy('q.issuedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findAll(): array
    {
        return $this->createQueryBuilder('q')
            ->leftJoin('q.customer', 'c')
            ->addSelect('c')
            ->orderBy('q.issuedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function getNextNumber(): string
    {
        $year = date('Y');
        $count = (int) $this->createQueryBuilder('q')
            ->select('COUNT(q.id)')
            ->where('q.number LIKE :prefix')
            ->setParameter('prefix', 'QT/' . $year . '/%')
            ->getQuery()
            ->getSingleScalarResult();

        return sprintf('QT/%s/%04d', $year, $count + 1);
    }

    public function save(Quote $quote, bool $flush = true): void
    {
        $this->getEntityManager()->persist($quote);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Quote $quote, bool $flush = true): void
    {
        $this->getEntityManager()->remove($quote);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
