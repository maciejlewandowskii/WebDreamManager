<?php

declare(strict_types=1);

namespace App\Domain\Authorization\Application\Pipeline\AddProjectMember;

use App\Domain\Authorization\Application\Data\ProjectMemberData;
use App\Domain\Authorization\Entity\ProjectMember;
use App\Domain\Project\Entity\Project;
use App\Infrastructure\Pipeline\PipelineCommandInterface;

final class AddProjectMemberCommand implements PipelineCommandInterface
{
    public ?ProjectMember $result = null;

    public function __construct(
        public readonly ProjectMemberData $data,
        public readonly Project $project,
    ) {
    }

    public function getEntityToSave(): object
    {
        assert($this->result !== null);

        return $this->result;
    }
}
