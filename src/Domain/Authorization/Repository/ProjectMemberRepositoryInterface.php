<?php

declare(strict_types=1);

namespace App\Domain\Authorization\Repository;

use App\Domain\Authorization\Entity\Permission;
use App\Domain\Authorization\Entity\ProjectMember;
use App\Domain\Identity\Entity\User;
use App\Domain\Project\Entity\Project;

interface ProjectMemberRepositoryInterface
{
    public function findById(string $id): ?ProjectMember;

    public function findByUserAndProject(User $user, Project $project): ?ProjectMember;

    /** @return ProjectMember[] */
    public function findByProject(Project $project): array;

    /** @return ProjectMember[] */
    public function findByUser(User $user): array;

    /** @return string[] project IDs */
    public function findProjectIdsWithPermission(User $user, Permission $permission): array;

    public function save(ProjectMember $member, bool $flush = true): void;

    public function remove(ProjectMember $member, bool $flush = true): void;
}
