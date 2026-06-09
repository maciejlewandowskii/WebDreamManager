<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\TimeTracking;

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

    public function findFiltered(?string $search, ?Project $project, bool $uninvoicedOnly = false): array
    {
        $qb = $this->createQueryBuilder('t')
            ->leftJoin('t.project', 'p')
            ->leftJoin('t.worker', 'w')
            ->addSelect('p', 'w')
            ->orderBy('t.date', 'DESC');

        if ($search !== null) {
            $qb->andWhere('t.title LIKE :search OR t.description LIKE :search')
               ->setParameter('search', '%' . $search . '%');
        }

        if ($project !== null) {
            $qb->andWhere('t.project = :project')
               ->setParameter('project', $project);
        }

        if ($uninvoicedOnly) {
            $qb->andWhere('t.invoiced = false');
        }

        return $qb->getQuery()->getResult();
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
