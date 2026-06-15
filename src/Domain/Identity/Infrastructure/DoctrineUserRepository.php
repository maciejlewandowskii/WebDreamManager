<?php

declare(strict_types=1);

namespace App\Domain\Identity\Infrastructure;

use App\Domain\Identity\Entity\User;
use App\Domain\Identity\Repository\UserRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @extends ServiceEntityRepository<User>
 */
final class DoctrineUserRepository extends ServiceEntityRepository implements UserRepositoryInterface, PasswordUpgraderInterface
{
    private const SORT_FIELDS = [
        'fullName' => 'u.fullName',
        'email'    => 'u.email',
        'isActive' => 'u.isActive',
        'role'     => 'r.name',
    ];

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function findById(string $id): ?User
    {
        return $this->find($id);
    }

    public function findByEmail(string $email): ?User
    {
        return $this->findOneBy(['email' => $email]);
    }

    public function findBySetupToken(string $token): ?User
    {
        return $this->findOneBy(['setupToken' => $token]);
    }

    public function findAll(): array
    {
        return $this->createQueryBuilder('u')
            ->leftJoin('u.role', 'r')
            ->addSelect('r')
            ->orderBy('u.fullName', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findActive(): array
    {
        return $this->createQueryBuilder('u')
            ->leftJoin('u.role', 'r')
            ->addSelect('r')
            ->where('u.isActive = true')
            ->orderBy('u.fullName', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findActiveWithPermission(string $permission): array
    {
        return $this->createQueryBuilder('u')
            ->innerJoin('u.role', 'r')
            ->addSelect('r')
            ->where('u.isActive = true')
            ->andWhere("r.permissions LIKE :perm")
            ->setParameter('perm', '%"' . $permission . '"%')
            ->orderBy('u.fullName', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findFiltered(
        ?string $search,
        string $sortBy = 'fullName',
        string $sortDirection = 'ASC',
        int $offset = 0,
        int $limit = 0,
    ): array {
        [$field, $direction] = $this->resolveSorting($sortBy, $sortDirection, 'fullName', 'ASC');

        $qb = $this->createQueryBuilder('u')
            ->leftJoin('u.role', 'r')
            ->addSelect('r')
            ->orderBy($field, $direction);

        if ($search !== null) {
            $qb->andWhere('TRGM_MATCH(:search, u.fullName) = true OR TRGM_MATCH(:search, u.email) = true')
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
        $qb = $this->createQueryBuilder('u')
            ->select('COUNT(u.id)');

        if ($search !== null) {
            $qb->andWhere('TRGM_MATCH(:search, u.fullName) = true OR TRGM_MATCH(:search, u.email) = true')
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

    public function save(User $user, bool $flush = true): void
    {
        $this->getEntityManager()->persist($user);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(User $user, bool $flush = true): void
    {
        $this->getEntityManager()->remove($user);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }
        $user->setPassword($newHashedPassword);
        $this->getEntityManager()->flush();
    }
}
