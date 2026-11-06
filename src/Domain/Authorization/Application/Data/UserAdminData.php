<?php

declare(strict_types=1);

namespace App\Domain\Authorization\Application\Data;

use App\Domain\Authorization\Entity\Role;
use App\Domain\Identity\Entity\User;
use App\Infrastructure\Validator\UniqueField;
use Symfony\Component\Validator\Constraints as Assert;

#[UniqueField(entityClass: User::class, field: 'email', message: 'This email is already used by another user.')]
final class UserAdminData
{
    public ?string $id = null;

    #[Assert\NotBlank]
    #[Assert\Email]
    #[Assert\Length(max: 180)]
    public string $email = '';

    #[Assert\Sequentially([new Assert\NotBlank(), new Assert\Length(min: 2, max: 100)])]
    public string $fullName = '';

    public ?Role $role = null;

    public bool $isActive = true;

    #[Assert\Length(min: 8, minMessage: 'Password must be at least 8 characters.')]
    public ?string $plainPassword = null;

    public static function fromUser(User $user): self
    {
        $data = new self();
        $data->id = $user->getId();
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
