<?php

declare(strict_types=1);

namespace App\Domain\Project\Application\Pipeline\UpdateProject;

use App\Domain\Project\Application\Data\ProjectData;
use App\Domain\Project\Entity\Project;
use App\Infrastructure\Pipeline\PipelineCommandInterface;

final readonly class UpdateProjectCommand implements PipelineCommandInterface
{
    public function __construct(
        public Project $project,
        public ProjectData $data,
    ) {
    }

    public function getEntityToSave(): object
    {
        return $this->project;
    }
}
