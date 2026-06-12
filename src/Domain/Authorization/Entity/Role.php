<?php

declare(strict_types=1);

namespace App\Domain\Authorization\Entity;

use App\Domain\Authorization\Infrastructure\DoctrineRoleRepository;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DoctrineRoleRepository::class)]
#[ORM\Table(name: 'roles')]
#[ORM\HasLifecycleCallbacks]
class Role
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    private string $id;

    #[ORM\Column(type: 'string', length: 100, unique: true)]
    private string $name;

    /** @var string[] */
    #[ORM\Column(type: 'json')]
    private array $permissions = [];

    /** Cannot be deleted — set via migration for the built-in Admin role */
    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $isSystem = false;

    #[ORM\Column(type: 'datetime_immutable')]
    private DateTimeImmutable $createdAt;

    #[ORM\Column(type: 'datetime_immutable')]
    private DateTimeImmutable $updatedAt;

    public function __construct(string $name, bool $isSystem = false)
    {
        $this->name = $name;
        $this->isSystem = $isSystem;
        $this->createdAt = new DateTimeImmutable();
        $this->updatedAt = new DateTimeImmutable();
    }

    #[ORM\PreUpdate]
    public function onPreUpdate(): void
    {
        $this->updatedAt = new DateTimeImmutable();
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function isSystem(): bool
    {
        return $this->isSystem;
    }

    public function hasPermission(Permission $permission): bool
    {
        return in_array($permission->value, $this->permissions, true);
    }

    /** @return Permission[] */
    public function getPermissions(): array
    {
        return array_values(array_filter(
            array_map(fn (string $v) => Permission::tryFrom($v), $this->permissions),
        ));
    }

    /** @param Permission[] $permissions */
    public function setPermissions(array $permissions): void
    {
        $this->permissions = array_values(array_map(fn (Permission $p) => $p->value, $permissions));
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): DateTimeImmutable
    {
        return $this->updatedAt;
    }
}
