<?php

declare(strict_types=1);

namespace App\Domain\Identity\Application\Data;

use Symfony\Component\Security\Core\Validator\Constraints\UserPassword;
use Symfony\Component\Validator\Constraints as Assert;

final class ChangePasswordData
{
    #[UserPassword(message: 'Current password is incorrect.')]
    public ?string $currentPassword = null;

    #[Assert\Sequentially([
        new Assert\NotBlank(),
        new Assert\Length(min: 8, minMessage: 'Password must be at least 8 characters.'),
        new Assert\NotEqualTo(propertyPath: 'currentPassword', message: 'New password must differ from the current one.'),
    ])]
    public ?string $newPassword = null;
}
