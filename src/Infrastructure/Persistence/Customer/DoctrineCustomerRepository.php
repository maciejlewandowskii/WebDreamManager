<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Customer;

use App\Domain\Customer\Entity\Customer;
use App\Domain\Customer\Repository\CustomerRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Customer>
 */
final class DoctrineCustomerRepository extends ServiceEntityRepository implements CustomerRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Customer::class);
    }

    public function findById(string $id): ?Customer
    {
        return $this->find($id);
    }

    public function findAll(): array
    {
        return $this->createQueryBuilder('c')
            ->orderBy('c.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function search(string $query): array
    {
        return $this->createQueryBuilder('c')
            ->where('c.name LIKE :q OR c.email LIKE :q OR c.company LIKE :q')
            ->setParameter('q', '%' . $query . '%')
            ->orderBy('c.name', 'ASC')
            ->setMaxResults(20)
            ->getQuery()
            ->getResult();
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
