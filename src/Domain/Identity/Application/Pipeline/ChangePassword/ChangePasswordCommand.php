<?php

declare(strict_types=1);

namespace App\Domain\Identity\Application\Pipeline\ChangePassword;

use App\Domain\Identity\Entity\User;
use App\Infrastructure\Pipeline\PipelineCommandInterface;

final readonly class ChangePasswordCommand implements PipelineCommandInterface
{
    public function __construct(
        public User $user,
        public string $newPassword,
    ) {
    }

    public function getEntityToSave(): object
    {
        return $this->user;
    }
}
