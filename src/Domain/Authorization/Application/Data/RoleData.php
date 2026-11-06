<?php

declare(strict_types=1);

namespace App\Domain\Authorization\Application\Data;

use App\Domain\Authorization\Entity\Permission;
use App\Domain\Authorization\Entity\Role;
use App\Infrastructure\Validator\UniqueField;
use Symfony\Component\Validator\Constraints as Assert;

#[UniqueField(entityClass: Role::class, field: 'name', message: 'A role with this name already exists.')]
final class RoleData
{
    public ?string $id = null;

    #[Assert\NotBlank]
    #[Assert\Length(max: 100)]
    public string $name = '';

    /** @var string[] Permission enum values */
    public array $permissions = [];

    public static function fromRole(Role $role): self
    {
        $data = new self();
        $data->id = $role->getId();
        $data->name = $role->getName();
        $data->permissions = array_map(static fn (Permission $p) => $p->value, $role->getPermissions());

        return $data;
    }
}
