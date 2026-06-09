<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Project;

use App\Domain\Customer\Entity\Customer;
use App\Domain\Project\Entity\Project;
use App\Domain\Project\Repository\ProjectRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Project>
 */
final class DoctrineProjectRepository extends ServiceEntityRepository implements ProjectRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Project::class);
    }

    public function findById(string $id): ?Project
    {
        return $this->find($id);
    }

    public function findAll(): array
    {
        return $this->createQueryBuilder('p')
            ->leftJoin('p.customer', 'c')
            ->addSelect('c')
            ->orderBy('p.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findByCustomer(Customer $customer): array
    {
        return $this->createQueryBuilder('p')
            ->where('p.customer = :customer')
            ->setParameter('customer', $customer)
            ->orderBy('p.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function search(string $query): array
    {
        return $this->createQueryBuilder('p')
            ->leftJoin('p.customer', 'c')
            ->where('p.name LIKE :q OR c.name LIKE :q')
            ->setParameter('q', '%' . $query . '%')
            ->orderBy('p.name', 'ASC')
            ->setMaxResults(20)
            ->getQuery()
            ->getResult();
    }

    public function save(Project $project, bool $flush = true): void
    {
        $this->getEntityManager()->persist($project);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Project $project, bool $flush = true): void
    {
        $this->getEntityManager()->remove($project);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
