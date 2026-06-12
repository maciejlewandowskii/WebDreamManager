<?php

declare(strict_types=1);

namespace App\Domain\Authorization\Entity;

use App\Domain\Authorization\Infrastructure\DoctrineProjectMemberRepository;
use App\Domain\Identity\Entity\User;
use App\Domain\Project\Entity\Project;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DoctrineProjectMemberRepository::class)]
#[ORM\Table(name: 'project_members')]
#[ORM\UniqueConstraint(name: 'uq_project_member', columns: ['user_id', 'project_id'])]
class ProjectMember
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    private string $id;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private User $user;

    #[ORM\ManyToOne(targetEntity: Project::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Project $project;

    /** @var string[] */
    #[ORM\Column(type: 'json')]
    private array $permissions = [];

    #[ORM\Column(type: 'datetime_immutable')]
    private DateTimeImmutable $createdAt;

    public function __construct(User $user, Project $project)
    {
        $this->user = $user;
        $this->project = $project;
        $this->createdAt = new DateTimeImmutable();
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getProject(): Project
    {
        return $this->project;
    }

    public function hasPermission(Permission $permission): bool
    {
        return in_array($permission->value, $this->permissions, true);
    }

    /** @return Permission[] */
    public function getPermissions(): array
    {
        return array_values(array_filter(
            array_map(static fn (string $v) => Permission::tryFrom($v), $this->permissions),
        ));
    }

    /** @param Permission[] $permissions */
    public function setPermissions(array $permissions): void
    {
        $this->permissions = array_values(array_map(static fn (Permission $p) => $p->value, $permissions));
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }
}
