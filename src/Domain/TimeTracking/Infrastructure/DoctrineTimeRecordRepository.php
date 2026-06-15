<?php

declare(strict_types=1);

namespace App\Domain\TimeTracking\Infrastructure;

use App\Domain\Identity\Entity\User;
use App\Domain\Project\Entity\Project;
use App\Domain\TimeTracking\Entity\TimeRecord;
use App\Domain\TimeTracking\Repository\TimeRecordRepositoryInterface;
use DateTimeImmutable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<TimeRecord>
 */
final class DoctrineTimeRecordRepository extends ServiceEntityRepository implements TimeRecordRepositoryInterface
{
    private const SORT_FIELDS = [
        'date'           => 't.date',
        'title'          => 't.title',
        'project'        => 'p.name',
        'estimatedHours' => 't.estimatedHours',
        'spentHours'     => 't.spentHours',
        'invoiced'       => 't.invoiced',
        'worker'         => 'w.fullName',
    ];

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TimeRecord::class);
    }

    public function findById(string $id): ?TimeRecord
    {
        return $this->find($id);
    }

    public function findAll(): array
    {
        return $this->createQueryBuilder('t')
            ->leftJoin('t.project', 'p')
            ->leftJoin('t.worker', 'w')
            ->addSelect('p', 'w')
            ->orderBy('t.date', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findByProject(Project $project): array
    {
        return $this->createQueryBuilder('t')
            ->where('t.project = :project')
            ->setParameter('project', $project)
            ->orderBy('t.date', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findByWorker(User $worker): array
    {
        return $this->createQueryBuilder('t')
            ->where('t.worker = :worker')
            ->setParameter('worker', $worker)
            ->orderBy('t.date', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findByDateRange(DateTimeImmutable $from, DateTimeImmutable $to): array
    {
        return $this->createQueryBuilder('t')
            ->where('t.date >= :from AND t.date <= :to')
            ->setParameter('from', $from->format('Y-m-d'))
            ->setParameter('to', $to->format('Y-m-d'))
            ->orderBy('t.date', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findUninvoicedByProject(Project $project): array
    {
        return $this->createQueryBuilder('t')
            ->where('t.project = :project AND t.invoiced = false')
            ->setParameter('project', $project)
            ->orderBy('t.date', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findFiltered(
        ?string $search,
        ?Project $project,
        bool $uninvoicedOnly = false,
        string $sortBy = 'date',
        string $sortDirection = 'DESC',
        ?User $workerFilter = null,
        array $visibleProjectIds = [],
        ?\DateTimeImmutable $dateFilter = null,
        int $offset = 0,
        int $limit = 0,
    ): array {
        $qb = $this->buildFilteredQuery($search, $project, $uninvoicedOnly, $workerFilter, $visibleProjectIds, $dateFilter);

        [$field, $direction] = $this->resolveSorting($sortBy, $sortDirection, 'date', 'DESC');
        $qb->orderBy($field, $direction)->addOrderBy('t.date', 'DESC');

        if ($offset > 0) {
            $qb->setFirstResult($offset);
        }

        if ($limit > 0) {
            $qb->setMaxResults($limit);
        }

        return $qb->getQuery()->getResult();
    }

    public function countFiltered(
        ?string $search,
        ?Project $project,
        bool $uninvoicedOnly = false,
        ?User $workerFilter = null,
        array $visibleProjectIds = [],
        ?\DateTimeImmutable $dateFilter = null,
    ): int {
        $qb = $this->buildFilteredQuery($search, $project, $uninvoicedOnly, $workerFilter, $visibleProjectIds, $dateFilter);
        $qb->select('COUNT(t.id)');

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * @param string[] $visibleProjectIds
     */
    private function buildFilteredQuery(
        ?string $search,
        ?Project $project,
        bool $uninvoicedOnly,
        ?User $workerFilter,
        array $visibleProjectIds,
        ?\DateTimeImmutable $dateFilter,
    ): \Doctrine\ORM\QueryBuilder {
        $qb = $this->createQueryBuilder('t')
            ->leftJoin('t.project', 'p')
            ->leftJoin('t.worker', 'w')
            ->addSelect('p', 'w');

        if ($search !== null) {
            $qb->andWhere('TRGM_MATCH(:search, t.title) = true OR TRGM_MATCH(:search, COALESCE(t.description, \'\')) = true')
               ->setParameter('search', $search);
        }

        if ($project !== null) {
            $qb->andWhere('t.project = :project')
               ->setParameter('project', $project);
        }

        if ($uninvoicedOnly) {
            $qb->andWhere('t.invoiced = false');
        }

        if ($workerFilter !== null) {
            if ($visibleProjectIds !== []) {
                $qb->andWhere('t.worker = :worker OR t.project IN (:visibleProjects)')
                   ->setParameter('worker', $workerFilter)
                   ->setParameter('visibleProjects', $visibleProjectIds);
            } else {
                $qb->andWhere('t.worker = :worker')
                   ->setParameter('worker', $workerFilter);
            }
        }

        if ($dateFilter !== null) {
            $qb->andWhere('t.date = :dateFilter')
               ->setParameter('dateFilter', $dateFilter->format('Y-m-d'));
        }

        return $qb;
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

    public function sumHoursByDate(DateTimeImmutable $date): array
    {
        /** @var array{spent: string, estimated: string} $row */
        $row = $this->createQueryBuilder('t')
            ->select('COALESCE(SUM(t.spentHours), 0) AS spent, COALESCE(SUM(t.estimatedHours), 0) AS estimated')
            ->where('t.date = :date')
            ->setParameter('date', $date->format('Y-m-d'))
            ->getQuery()
            ->getSingleResult();

        return ['spent' => (float) $row['spent'], 'estimated' => (float) $row['estimated']];
    }

    public function sumHoursByMonth(int $year, int $month): array
    {
        $from = new DateTimeImmutable(sprintf('%d-%02d-01', $year, $month));
        $to   = $from->modify('last day of this month');

        /** @var array{spent: string} $row */
        $row = $this->createQueryBuilder('t')
            ->select('COALESCE(SUM(t.spentHours), 0) AS spent')
            ->where('t.date >= :from AND t.date <= :to')
            ->setParameter('from', $from->format('Y-m-d'))
            ->setParameter('to', $to->format('Y-m-d'))
            ->getQuery()
            ->getSingleResult();

        return ['spent' => (float) $row['spent']];
    }

    public function sumHoursByDateForUser(DateTimeImmutable $date, User $user): array
    {
        /** @var array{spent: string, estimated: string} $row */
        $row = $this->createQueryBuilder('t')
            ->select('COALESCE(SUM(t.spentHours), 0) AS spent, COALESCE(SUM(t.estimatedHours), 0) AS estimated')
            ->where('t.date = :date AND t.worker = :user')
            ->setParameter('date', $date->format('Y-m-d'))
            ->setParameter('user', $user)
            ->getQuery()
            ->getSingleResult();

        return ['spent' => (float) $row['spent'], 'estimated' => (float) $row['estimated']];
    }

    public function sumHoursByMonthForUser(int $year, int $month, User $user): array
    {
        $from = new DateTimeImmutable(sprintf('%d-%02d-01', $year, $month));
        $to   = $from->modify('last day of this month');

        /** @var array{spent: string} $row */
        $row = $this->createQueryBuilder('t')
            ->select('COALESCE(SUM(t.spentHours), 0) AS spent')
            ->where('t.date >= :from AND t.date <= :to AND t.worker = :user')
            ->setParameter('from', $from->format('Y-m-d'))
            ->setParameter('to', $to->format('Y-m-d'))
            ->setParameter('user', $user)
            ->getQuery()
            ->getSingleResult();

        return ['spent' => (float) $row['spent']];
    }

    public function save(TimeRecord $record, bool $flush = true): void
    {
        $this->getEntityManager()->persist($record);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(TimeRecord $record, bool $flush = true): void
    {
        $this->getEntityManager()->remove($record);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
