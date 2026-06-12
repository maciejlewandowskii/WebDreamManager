<?php

declare(strict_types=1);

namespace App\Domain\Authorization\Application\Data;

use App\Domain\Authorization\Entity\Role;
use App\Domain\Identity\Entity\User;

final class UserAdminData
{
    public string $email = '';
    public string $fullName = '';
    public ?Role $role = null;
    public bool $isActive = true;

    public static function fromUser(User $user): self
    {
        $data = new self();
        $data->email = $user->getEmail();
        $data->fullName = $user->getFullName();
        $data->role = $user->getRole();
        $data->isActive = $user->isActive();

        return $data;
    }

    public function applyTo(User $user): void
    {
        $user->setEmail($this->email);
        $user->setFullName($this->fullName);
        $user->setRole($this->role);
        $user->setActive($this->isActive);
    }
}
