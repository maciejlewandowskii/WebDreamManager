<?php

declare(strict_types=1);

namespace App\Domain\Identity\Application\Pipeline\UpdateProfile;

use App\Domain\Identity\Application\Data\ProfileData;
use App\Domain\Identity\Entity\User;
use App\Infrastructure\Pipeline\PipelineCommandInterface;

final readonly class UpdateProfileCommand implements PipelineCommandInterface
{
    public function __construct(
        public User $user,
        public ProfileData $data,
    ) {
    }

    public function getEntityToSave(): object
    {
        return $this->user;
    }
}
