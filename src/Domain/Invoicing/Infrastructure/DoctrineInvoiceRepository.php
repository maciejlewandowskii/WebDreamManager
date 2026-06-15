<?php

declare(strict_types=1);

namespace App\Domain\Invoicing\Infrastructure;

use App\Domain\Customer\Entity\Customer;
use App\Domain\Invoicing\Entity\Invoice;
use App\Domain\Invoicing\Entity\InvoiceStatus;
use App\Domain\Invoicing\Repository\InvoiceRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Invoice>
 */
final class DoctrineInvoiceRepository extends ServiceEntityRepository implements InvoiceRepositoryInterface
{
    private const SORT_FIELDS = [
        'number'     => 'i.number',
        'customer'   => 'c.name',
        'issuedAt'   => 'i.issuedAt',
        'dueAt'      => 'i.dueAt',
        'netTotal'   => 'i.netTotal',
        'grossTotal' => 'i.grossTotal',
        'status'     => 'i.status',
    ];

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Invoice::class);
    }

    public function findById(string $id): ?Invoice
    {
        return $this->find($id);
    }

    public function findByNumber(string $number): ?Invoice
    {
        return $this->findOneBy(['number' => $number]);
    }

    public function findByPaymentToken(string $token): ?Invoice
    {
        return $this->findOneBy(['paymentToken' => $token]);
    }

    public function findByCustomer(Customer $customer): array
    {
        return $this->createQueryBuilder('i')
            ->where('i.customer = :customer')
            ->setParameter('customer', $customer)
            ->orderBy('i.issuedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findByStatus(InvoiceStatus $status): array
    {
        return $this->createQueryBuilder('i')
            ->where('i.status = :status')
            ->setParameter('status', $status->value)
            ->orderBy('i.issuedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findAll(): array
    {
        return $this->createQueryBuilder('i')
            ->leftJoin('i.customer', 'c')
            ->addSelect('c')
            ->orderBy('i.issuedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findFiltered(
        ?string $search,
        string $sortBy = 'issuedAt',
        string $sortDirection = 'DESC',
        int $offset = 0,
        int $limit = 0,
    ): array {
        [$field, $direction] = $this->resolveSorting($sortBy, $sortDirection, 'issuedAt', 'DESC');

        $qb = $this->createQueryBuilder('i')
            ->leftJoin('i.customer', 'c')
            ->addSelect('c')
            ->orderBy($field, $direction);

        if ($search !== null) {
            $qb->andWhere('TRGM_MATCH(:search, i.number) = true OR TRGM_MATCH(:search, c.name) = true')
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
        $qb = $this->createQueryBuilder('i')
            ->select('COUNT(i.id)')
            ->leftJoin('i.customer', 'c');

        if ($search !== null) {
            $qb->andWhere('TRGM_MATCH(:search, i.number) = true OR TRGM_MATCH(:search, c.name) = true')
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
        $count = (int) $this->createQueryBuilder('i')
            ->select('COUNT(i.id)')
            ->where('i.number LIKE :prefix')
            ->setParameter('prefix', 'INV/' . $year . '/%')
            ->getQuery()
            ->getSingleScalarResult();

        return sprintf('INV/%s/%04d', $year, $count + 1);
    }

    public function save(Invoice $invoice, bool $flush = true): void
    {
        $this->getEntityManager()->persist($invoice);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Invoice $invoice, bool $flush = true): void
    {
        $this->getEntityManager()->remove($invoice);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
