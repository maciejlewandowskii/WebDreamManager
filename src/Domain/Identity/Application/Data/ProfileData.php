<?php

declare(strict_types=1);

namespace App\Domain\Identity\Application\Data;

use App\Domain\Identity\Entity\User;

final class ProfileData
{
    public string $fullName = '';
    public string $email = '';

    public static function fromUser(User $user): self
    {
        $data = new self();
        $data->fullName = $user->getFullName();
        $data->email    = $user->getEmail();

        return $data;
    }

    public function applyTo(User $user): void
    {
        $user->setFullName($this->fullName);
        $user->setEmail($this->email);
    }
}
