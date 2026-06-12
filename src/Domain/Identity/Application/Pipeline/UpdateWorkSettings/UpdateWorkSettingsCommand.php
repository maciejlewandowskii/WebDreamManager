<?php

declare(strict_types=1);

namespace App\Domain\Identity\Application\Pipeline\UpdateWorkSettings;

use App\Domain\Identity\Application\Data\WorkSettingsData;
use App\Domain\Identity\Entity\User;
use App\Infrastructure\Pipeline\PipelineCommandInterface;

final readonly class UpdateWorkSettingsCommand implements PipelineCommandInterface
{
    public function __construct(
        public User $user,
        public WorkSettingsData $data,
    ) {
    }

    public function getEntityToSave(): object
    {
        return $this->user;
    }
}
