<?php

declare(strict_types=1);

namespace App\Domain\Authorization\Application\Pipeline\UpdateUser;

use App\Domain\Authorization\Application\Data\UserAdminData;
use App\Domain\Identity\Entity\User;
use App\Infrastructure\Pipeline\PipelineCommandInterface;

final readonly class UpdateUserCommand implements PipelineCommandInterface
{
    public function __construct(
        public User $user,
        public UserAdminData $data,
        public ?string $plainPassword,
    ) {
    }

    public function getEntityToSave(): object
    {
        return $this->user;
    }
}
