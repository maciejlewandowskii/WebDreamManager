<?php

declare(strict_types=1);

namespace App\Domain\Authorization\Infrastructure;

use App\Domain\Authorization\Entity\Role;
use App\Domain\Authorization\Repository\RoleRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Role>
 */
final class DoctrineRoleRepository extends ServiceEntityRepository implements RoleRepositoryInterface
{
    private const SORT_FIELDS = [
        'name'     => 'r.name',
        'isSystem' => 'r.isSystem',
    ];

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Role::class);
    }

    public function findById(string $id): ?Role
    {
        return $this->find($id);
    }

    public function findByName(string $name): ?Role
    {
        return $this->findOneBy(['name' => $name]);
    }

    public function findAll(): array
    {
        return $this->createQueryBuilder('r')
            ->orderBy('r.isSystem', 'DESC')
            ->addOrderBy('r.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findFiltered(
        ?string $search,
        string $sortBy = 'name',
        string $sortDirection = 'ASC',
        int $offset = 0,
        int $limit = 0,
    ): array {
        [$field, $direction] = $this->resolveSorting($sortBy, $sortDirection, 'name', 'ASC');

        $qb = $this->createQueryBuilder('r')
            ->orderBy($field, $direction);

        if ($search !== null) {
            $qb->andWhere('TRGM_MATCH(:search, r.name) = true')
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
        $qb = $this->createQueryBuilder('r')
            ->select('COUNT(r.id)');

        if ($search !== null) {
            $qb->andWhere('TRGM_MATCH(:search, r.name) = true')
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

    public function findAdminRole(): ?Role
    {
        return $this->findOneBy(['isSystem' => true]);
    }

    public function save(Role $role, bool $flush = true): void
    {
        $this->getEntityManager()->persist($role);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Role $role, bool $flush = true): void
    {
        $this->getEntityManager()->remove($role);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
