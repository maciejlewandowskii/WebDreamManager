<?php

declare(strict_types=1);

namespace App\Domain\Identity\Application\Data;

final class ChangePasswordData
{
    public string $currentPassword = '';
    public string $newPassword = '';
    public string $confirmPassword = '';
}
