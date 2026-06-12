<?php

declare(strict_types=1);

namespace App\Domain\Project\Infrastructure;

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
    private const SORT_FIELDS = [
        'name' => 'p.name',
        'customer' => 'c.name',
        'status' => 'p.status',
        'budget' => 'p.budget',
        'dueDate' => 'p.dueDate',
        'createdAt' => 'p.createdAt',
    ];
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Project::class);
    }

    public function findById(string $id): ?Project
    {
        return $this->find($id);
    }

    public function findAll(string $sortBy = 'createdAt', string $sortDirection = 'DESC'): array
    {
        [$field, $direction] = $this->resolveSorting($sortBy, $sortDirection, 'createdAt', 'DESC');

        return $this->createQueryBuilder('p')
            ->leftJoin('p.customer', 'c')
            ->addSelect('c')
            ->orderBy($field, $direction)
            ->addOrderBy('p.createdAt', 'DESC')
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

    public function search(string $query, string $sortBy = 'name', string $sortDirection = 'ASC'): array
    {
        [$field, $direction] = $this->resolveSorting($sortBy, $sortDirection, 'name', 'ASC');

        return $this->createQueryBuilder('p')
            ->leftJoin('p.customer', 'c')
            ->where('p.name LIKE :q OR c.name LIKE :q')
            ->setParameter('q', '%' . $query . '%')
            ->orderBy($field, $direction)
            ->addOrderBy('p.createdAt', 'DESC')
            ->setMaxResults(20)
            ->getQuery()
            ->getResult();
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
            $direction = $defaultSortDirection;
        }

        return [$field, $direction];
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
