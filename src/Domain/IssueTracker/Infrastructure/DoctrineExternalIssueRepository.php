<?php

declare(strict_types=1);

namespace App\Domain\IssueTracker\Infrastructure;

use App\Domain\IssueTracker\Entity\ExternalIssue;
use App\Domain\IssueTracker\Enum\IssueStatus;
use App\Domain\IssueTracker\Enum\TrackerType;
use App\Domain\IssueTracker\Port\ExternalIssueRepositoryInterface;
use App\Domain\Project\Entity\Project;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<ExternalIssue> */
class DoctrineExternalIssueRepository extends ServiceEntityRepository implements ExternalIssueRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ExternalIssue::class);
    }

    public function findById(string $id): ?ExternalIssue
    {
        return $this->find($id);
    }

    /** @return ExternalIssue[] */
    public function findByProject(Project $project): array
    {
        return $this->createQueryBuilder('i')
            ->where('i.project = :project')
            ->setParameter('project', $project->getId())
            ->orderBy('i.status', 'ASC')
            ->addOrderBy('i.externalNumber', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /** @return ExternalIssue[] */
    public function findOpenByProject(Project $project): array
    {
        return $this->createQueryBuilder('i')
            ->where('i.project = :project')
            ->andWhere('i.status IN (:open)')
            ->setParameter('project', $project->getId())
            ->setParameter('open', [IssueStatus::Open->value, IssueStatus::InProgress->value])
            ->orderBy('i.externalNumber', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findByExternalId(Project $project, TrackerType $type, string $externalId): ?ExternalIssue
    {
        return $this->createQueryBuilder('i')
            ->where('i.project = :project')
            ->andWhere('i.trackerType = :type')
            ->andWhere('i.externalId = :externalId')
            ->setParameter('project', $project->getId())
            ->setParameter('type', $type->value)
            ->setParameter('externalId', $externalId)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /** @return ExternalIssue[] */
    public function findFiltered(Project $project, ?IssueStatus $status = null, ?string $search = null): array
    {
        $qb = $this->createQueryBuilder('i')
            ->where('i.project = :project')
            ->setParameter('project', $project->getId())
            ->orderBy('i.status', 'ASC')
            ->addOrderBy('i.externalNumber', 'ASC');

        if ($status !== null) {
            $qb->andWhere('i.status = :status')->setParameter('status', $status->value);
        }

        if ($search !== null && $search !== '') {
            $qb->andWhere('i.title LIKE :search')->setParameter('search', '%' . $search . '%');
        }

        return $qb->getQuery()->getResult();
    }

    public function save(ExternalIssue $issue, bool $flush = true): void
    {
        $this->getEntityManager()->persist($issue);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function flush(): void
    {
        $this->getEntityManager()->flush();
    }

    public function removeByProject(Project $project): void
    {
        $this->createQueryBuilder('i')
            ->delete()
            ->where('i.project = :project')
            ->setParameter('project', $project->getId())
            ->getQuery()
            ->execute();
    }
}
