<?php

declare(strict_types=1);

namespace App\Domain\Identity\Application\Data;

use App\Domain\Identity\Entity\User;
use App\Infrastructure\Validator\UniqueField;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

#[UniqueField(entityClass: User::class, field: 'email', message: 'This email is already used by another user.')]
final class ProfileData
{
    public ?string $id = null;

    #[Assert\Sequentially([new Assert\NotBlank(), new Assert\Length(min: 2, max: 100, minMessage: 'Full name must be at least 2 characters.')])]
    public string $fullName = '';

    #[Assert\NotBlank]
    #[Assert\Email]
    #[Assert\Length(max: 180)]
    public string $email = '';

    #[Assert\Length(max: 50, maxMessage: 'Phone number cannot exceed 50 characters.')]
    public ?string $phone = null;

    #[Assert\Image(maxSize: '2M', mimeTypes: ['image/jpeg', 'image/png', 'image/webp'])]
    public ?UploadedFile $avatarFile = null;

    public static function fromUser(User $user): self
    {
        $data = new self();
        $data->id       = $user->getId();
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
