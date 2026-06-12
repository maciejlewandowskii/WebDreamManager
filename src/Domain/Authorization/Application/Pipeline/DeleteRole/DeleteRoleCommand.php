<?php

declare(strict_types=1);

namespace App\Domain\Authorization\Application\Pipeline\DeleteRole;

use App\Domain\Authorization\Entity\Role;
use App\Infrastructure\Pipeline\HasRemovableEntity;

final readonly class DeleteRoleCommand implements HasRemovableEntity
{
    public function __construct(public Role $role)
    {
    }

    public function getEntityToRemove(): object
    {
        return $this->role;
    }
}
