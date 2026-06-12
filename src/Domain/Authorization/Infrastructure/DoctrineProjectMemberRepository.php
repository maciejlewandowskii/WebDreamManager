<?php

declare(strict_types=1);

namespace App\Domain\Authorization\Infrastructure;

use App\Domain\Authorization\Entity\Permission;
use App\Domain\Authorization\Entity\ProjectMember;
use App\Domain\Authorization\Repository\ProjectMemberRepositoryInterface;
use App\Domain\Identity\Entity\User;
use App\Domain\Project\Entity\Project;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ProjectMember>
 */
final class DoctrineProjectMemberRepository extends ServiceEntityRepository implements ProjectMemberRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProjectMember::class);
    }

    public function findById(string $id): ?ProjectMember
    {
        return $this->find($id);
    }

    public function findByUserAndProject(User $user, Project $project): ?ProjectMember
    {
        return $this->findOneBy(['user' => $user, 'project' => $project]);
    }

    public function findByProject(Project $project): array
    {
        return $this->createQueryBuilder('pm')
            ->leftJoin('pm.user', 'u')
            ->addSelect('u')
            ->where('pm.project = :project')
            ->setParameter('project', $project)
            ->orderBy('u.fullName', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findByUser(User $user): array
    {
        return $this->createQueryBuilder('pm')
            ->leftJoin('pm.project', 'p')
            ->addSelect('p')
            ->where('pm.user = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult();
    }

    public function findProjectIdsWithPermission(User $user, Permission $permission): array
    {
        // Filter in PHP: JSON contains check is simpler here than a custom DQL function
        $members = $this->findByUser($user);
        $ids = [];
        foreach ($members as $member) {
            if ($member->hasPermission($permission)) {
                $ids[] = $member->getProject()->getId();
            }
        }

        return $ids;
    }

    public function save(ProjectMember $member, bool $flush = true): void
    {
        $this->getEntityManager()->persist($member);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(ProjectMember $member, bool $flush = true): void
    {
        $this->getEntityManager()->remove($member);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
