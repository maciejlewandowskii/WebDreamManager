<?php

declare(strict_types=1);

namespace App\Domain\Project\Application\Pipeline\DeleteProject;

use App\Domain\Project\Entity\Project;
use App\Infrastructure\Pipeline\HasRemovableEntity;

final readonly class DeleteProjectCommand implements HasRemovableEntity
{
    public function __construct(public Project $project)
    {
    }

    public function getEntityToRemove(): object
    {
        return $this->project;
    }
}
