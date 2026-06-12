<?php

declare(strict_types=1);

namespace App\Domain\Identity\Application\Data;

use App\Domain\Identity\Entity\User;

final class ProfileData
{
    public string $fullName = '';
    public string $email = '';
    public ?string $phone = null;

    public static function fromUser(User $user): self
    {
        $data = new self();
        $data->fullName = $user->getFullName();
        $data->email    = $user->getEmail();
        $data->phone    = $user->getPhone();

        return $data;
    }

    public function applyTo(User $user): void
    {
        $user->setFullName($this->fullName);
        $user->setEmail($this->email);
        $user->setPhone($this->phone);
    }
}
