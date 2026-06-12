<?php

declare(strict_types=1);

namespace App\Domain\Authorization\Application\Pipeline\CreateRole;

use App\Domain\Authorization\Application\Data\RoleData;
use App\Domain\Authorization\Entity\Role;
use App\Infrastructure\Pipeline\PipelineCommandInterface;

final class CreateRoleCommand implements PipelineCommandInterface
{
    public ?Role $result = null;

    public function __construct(public readonly RoleData $data)
    {
    }

    public function getEntityToSave(): object
    {
        assert($this->result !== null);

        return $this->result;
    }
}
