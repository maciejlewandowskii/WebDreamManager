<?php

declare(strict_types=1);

namespace App\Domain\Identity\Application\Data;

use Symfony\Component\Validator\Constraints as Assert;

final class SetPasswordData
{
    #[Assert\Length(min: 8, minMessage: 'Password must be at least 8 characters.')]
    public string $password = '';

    #[Assert\EqualTo(propertyPath: 'password', message: 'Passwords do not match.')]
    public string $confirmPassword = '';
}
