<?php

declare(strict_types=1);

namespace App\Domain\Project\Application\Pipeline\CreateProject;

use App\Domain\Project\Application\Data\ProjectData;
use App\Domain\Project\Entity\Project;
use App\Infrastructure\Pipeline\PipelineCommandInterface;

final class CreateProjectCommand implements PipelineCommandInterface
{
    public ?Project $result = null;

    public function __construct(public readonly ProjectData $data)
    {
    }

    public static function fromData(ProjectData $data): self
    {
        return new self($data);
    }

    public function getEntityToSave(): object
    {
        assert($this->result !== null);

        return $this->result;
    }
}
