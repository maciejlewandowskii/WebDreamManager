<?php

declare(strict_types=1);

namespace App\Domain\Authorization\Application\Pipeline\UpdateRole;

use App\Domain\Authorization\Application\Data\RoleData;
use App\Domain\Authorization\Entity\Role;
use App\Infrastructure\Pipeline\PipelineCommandInterface;

final readonly class UpdateRoleCommand implements PipelineCommandInterface
{
    public function __construct(
        public Role $role,
        public RoleData $data,
    ) {
    }

    public function getEntityToSave(): object
    {
        return $this->role;
    }
}
