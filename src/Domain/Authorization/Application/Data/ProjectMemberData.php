<?php

declare(strict_types=1);

namespace App\Domain\Authorization\Application\Data;

use App\Domain\Authorization\Entity\Permission;
use App\Domain\Authorization\Entity\ProjectMember;
use App\Domain\Identity\Entity\User;
use Symfony\Component\Validator\Constraints as Assert;

final class ProjectMemberData
{
    #[Assert\NotNull(message: 'Please select a user.')]
    public ?User $user = null;

    /** @var string[] Permission enum values */
    public array $permissions = [];

    public static function fromMember(ProjectMember $member): self
    {
        $data = new self();
        $data->user = $member->getUser();
        $data->permissions = array_map(static fn (Permission $p) => $p->value, $member->getPermissions());

        return $data;
    }
}
