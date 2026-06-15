<?php

declare(strict_types=1);

namespace App\Domain\Customer\Infrastructure;

use App\Domain\Customer\Entity\Customer;
use App\Domain\Customer\Repository\CustomerRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Customer>
 */
final class DoctrineCustomerRepository extends ServiceEntityRepository implements CustomerRepositoryInterface
{
    private const SORT_FIELDS = [
        'name' => 'c.name',
        'company' => 'c.company',
        'email' => 'c.email',
        'status' => 'c.status',
        'createdAt' => 'c.createdAt',
    ];
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Customer::class);
    }

    public function findById(string $id): ?Customer
    {
        return $this->find($id);
    }

    public function findAll(string $sortBy = 'name', string $sortDirection = 'ASC'): array
    {
        [$field, $direction] = $this->resolveSorting($sortBy, $sortDirection, 'name', 'ASC');

        return $this->createQueryBuilder('c')
            ->orderBy($field, $direction)
            ->addOrderBy('c.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function search(string $query, string $sortBy = 'name', string $sortDirection = 'ASC'): array
    {
        [$field, $direction] = $this->resolveSorting($sortBy, $sortDirection, 'name', 'ASC');

        return $this->createQueryBuilder('c')
            ->where('c.name LIKE :q OR c.email LIKE :q OR c.company LIKE :q')
            ->setParameter('q', '%' . $query . '%')
            ->orderBy($field, $direction)
            ->addOrderBy('c.name', 'ASC')
            ->setMaxResults(20)
            ->getQuery()
            ->getResult();
    }

    public function findFiltered(?string $search, string $sortBy = 'name', string $sortDirection = 'ASC', int $offset = 0, int $limit = 0): array
    {
        [$field, $direction] = $this->resolveSorting($sortBy, $sortDirection, 'name', 'ASC');

        $qb = $this->createQueryBuilder('c')
            ->orderBy($field, $direction)
            ->addOrderBy('c.name', 'ASC');

        if ($search !== null && $search !== '') {
            $qb->where('c.name LIKE :q OR c.email LIKE :q OR c.company LIKE :q')
               ->setParameter('q', '%' . $search . '%');
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
        $qb = $this->createQueryBuilder('c')
            ->select('COUNT(c.id)');

        if ($search !== null && $search !== '') {
            $qb->where('c.name LIKE :q OR c.email LIKE :q OR c.company LIKE :q')
               ->setParameter('q', '%' . $search . '%');
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

    public function save(Customer $customer, bool $flush = true): void
    {
        $this->getEntityManager()->persist($customer);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Customer $customer, bool $flush = true): void
    {
        $this->getEntityManager()->remove($customer);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
