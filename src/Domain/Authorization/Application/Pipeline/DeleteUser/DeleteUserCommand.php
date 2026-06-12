<?php

declare(strict_types=1);

namespace App\Domain\Authorization\Application\Pipeline\DeleteUser;

use App\Domain\Identity\Entity\User;
use App\Infrastructure\Pipeline\HasRemovableEntity;

final readonly class DeleteUserCommand implements HasRemovableEntity
{
    public function __construct(public User $user)
    {
    }

    public function getEntityToRemove(): object
    {
        return $this->user;
    }
}
