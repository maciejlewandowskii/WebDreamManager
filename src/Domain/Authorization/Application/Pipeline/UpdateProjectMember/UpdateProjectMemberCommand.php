<?php

declare(strict_types=1);

namespace App\Domain\Authorization\Application\Pipeline\UpdateProjectMember;

use App\Domain\Authorization\Application\Data\ProjectMemberData;
use App\Domain\Authorization\Entity\ProjectMember;
use App\Infrastructure\Pipeline\PipelineCommandInterface;

final readonly class UpdateProjectMemberCommand implements PipelineCommandInterface
{
    public function __construct(
        public ProjectMember $member,
        public ProjectMemberData $data,
    ) {
    }

    public function getEntityToSave(): object
    {
        return $this->member;
    }
}
