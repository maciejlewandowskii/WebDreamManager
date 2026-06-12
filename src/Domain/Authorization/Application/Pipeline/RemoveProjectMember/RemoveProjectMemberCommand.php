<?php

declare(strict_types=1);

namespace App\Domain\Authorization\Application\Pipeline\RemoveProjectMember;

use App\Domain\Authorization\Entity\ProjectMember;
use App\Infrastructure\Pipeline\HasRemovableEntity;

final readonly class RemoveProjectMemberCommand implements HasRemovableEntity
{
    public function __construct(public ProjectMember $member)
    {
    }

    public function getEntityToRemove(): object
    {
        return $this->member;
    }
}
