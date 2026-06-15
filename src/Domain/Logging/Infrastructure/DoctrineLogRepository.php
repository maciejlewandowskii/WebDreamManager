<?php

declare(strict_types=1);

namespace App\Domain\Logging\Infrastructure;

use App\Domain\Logging\Application\Data\LogFilter;
use App\Domain\Logging\Entity\LogEntry;
use App\Domain\Logging\Repository\LogRepositoryInterface;
use DateTimeImmutable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<LogEntry>
 */
final class DoctrineLogRepository extends ServiceEntityRepository implements LogRepositoryInterface
{
    /** @var array<string, string> */
    private const array SORT_FIELDS = [
        'createdAt' => 'l.createdAt',
        'level'     => 'l.level',
        'type'      => 'l.type',
        'category'  => 'l.category',
        'service'   => 'l.service',
    ];

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LogEntry::class);
    }

    public function findById(string $id): ?LogEntry
    {
        return $this->find($id);
    }

    public function filter(LogFilter $filter): array
    {
        $qb = $this->createQueryBuilder('l');
        $this->applyFilters($qb, $filter);
        $this->applySorting($qb, $filter->sortBy, $filter->sortDirection);

        $qb->setFirstResult(($filter->page - 1) * $filter->perPage)
            ->setMaxResults($filter->perPage);

        return $qb->getQuery()->getResult();
    }

    public function countByFilter(LogFilter $filter): int
    {
        $qb = $this->createQueryBuilder('l')
            ->select('COUNT(l.id)');
        $this->applyFilters($qb, $filter);

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    public function findDistinctCategories(): array
    {
        return array_column(
            $this->createQueryBuilder('l')
                ->select('DISTINCT l.category')
                ->orderBy('l.category', 'ASC')
                ->getQuery()
                ->getScalarResult(),
            'category',
        );
    }

    public function findDistinctServices(): array
    {
        return array_column(
            $this->createQueryBuilder('l')
                ->select('DISTINCT l.service')
                ->where('l.service IS NOT NULL')
                ->orderBy('l.service', 'ASC')
                ->getQuery()
                ->getScalarResult(),
            'service',
        );
    }

    public function save(LogEntry $entry, bool $flush = true): void
    {
        $this->getEntityManager()->persist($entry);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function deleteOlderThan(DateTimeImmutable $before): int
    {
        return (int) $this->createQueryBuilder('l')
            ->delete()
            ->where('l.createdAt < :before')
            ->setParameter('before', $before)
            ->getQuery()
            ->execute();
    }

    private function applyFilters(QueryBuilder $qb, LogFilter $filter): void
    {
        if ($filter->type !== null) {
            $qb->andWhere('l.type = :type')
                ->setParameter('type', $filter->type->value);
        }

        if ($filter->level !== null) {
            $qb->andWhere('l.level = :level')
                ->setParameter('level', $filter->level->value);
        }

        if ($filter->category !== null) {
            $qb->andWhere('l.category = :category')
                ->setParameter('category', $filter->category);
        }

        if ($filter->service !== null) {
            $qb->andWhere('l.service = :service')
                ->setParameter('service', $filter->service);
        }

        if ($filter->userId !== null) {
            $qb->andWhere('l.userId = :userId')
                ->setParameter('userId', $filter->userId);
        }

        if ($filter->search !== null && $filter->search !== '') {
            $qb->andWhere('l.message LIKE :search')
                ->setParameter('search', '%' . $filter->search . '%');
        }

        if ($filter->dateFrom !== null) {
            $qb->andWhere('l.createdAt >= :dateFrom')
                ->setParameter('dateFrom', $filter->dateFrom);
        }

        if ($filter->dateTo !== null) {
            $qb->andWhere('l.createdAt <= :dateTo')
                ->setParameter('dateTo', $filter->dateTo);
        }
    }

    private function applySorting(QueryBuilder $qb, string $sortBy, string $sortDirection): void
    {
        $field     = self::SORT_FIELDS[$sortBy] ?? self::SORT_FIELDS['createdAt'];
        $direction = strtoupper($sortDirection) === 'ASC' ? 'ASC' : 'DESC';

        $qb->orderBy($field, $direction);
    }
}
