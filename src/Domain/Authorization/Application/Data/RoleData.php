<?php

declare(strict_types=1);

namespace App\Domain\Authorization\Application\Data;

use App\Domain\Authorization\Entity\Permission;
use App\Domain\Authorization\Entity\Role;

final class RoleData
{
    public string $name = '';

    /** @var string[] Permission enum values */
    public array $permissions = [];

    public static function fromRole(Role $role): self
    {
        $data = new self();
        $data->name = $role->getName();
        $data->permissions = array_map(static fn (Permission $p) => $p->value, $role->getPermissions());

        return $data;
    }
}
