<?php

declare(strict_types=1);

namespace App\Domain\Invoicing\Infrastructure;

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
    private const SORT_FIELDS = [
        'number'     => 'q.number',
        'customer'   => 'c.name',
        'createdAt'  => 'q.createdAt',
        'netTotal'   => 'q.netTotal',
        'grossTotal' => 'q.grossTotal',
        'status'     => 'q.status',
    ];

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

    public function findFiltered(
        ?string $search,
        string $sortBy = 'createdAt',
        string $sortDirection = 'DESC',
        int $offset = 0,
        int $limit = 0,
    ): array {
        [$field, $direction] = $this->resolveSorting($sortBy, $sortDirection, 'createdAt', 'DESC');

        $qb = $this->createQueryBuilder('q')
            ->leftJoin('q.customer', 'c')
            ->addSelect('c')
            ->orderBy($field, $direction);

        if ($search !== null) {
            $qb->andWhere('TRGM_MATCH(:search, q.number) = true OR TRGM_MATCH(:search, c.name) = true')
               ->setParameter('search', $search);
        }

        if ($offset > 0) {
            $qb->setFirstResult($offset);
        }

        if ($limit > 0) {
            $qb->setMaxResults($limit);
        }

        return $qb->getQuery()->getResult();
    }

    public function countFiltered(?string $search): int
    {
        $qb = $this->createQueryBuilder('q')
            ->select('COUNT(q.id)')
            ->leftJoin('q.customer', 'c');

        if ($search !== null) {
            $qb->andWhere('TRGM_MATCH(:search, q.number) = true OR TRGM_MATCH(:search, c.name) = true')
               ->setParameter('search', $search);
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    /** @return array{0: string, 1: 'ASC'|'DESC'} */
    private function resolveSorting(
        string $sortBy,
        string $sortDirection,
        string $defaultSortBy,
        string $defaultSortDirection,
    ): array {
        $field = self::SORT_FIELDS[$sortBy] ?? self::SORT_FIELDS[$defaultSortBy];
        $direction = strtoupper($sortDirection) === 'ASC' ? 'ASC' : 'DESC';

        if (!isset(self::SORT_FIELDS[$sortBy])) {
            $direction = strtoupper($defaultSortDirection) === 'ASC' ? 'ASC' : 'DESC';
        }

        return [$field, $direction];
    }

    public function getNextNumber(): string
    {
        $year  = date('Y');
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
