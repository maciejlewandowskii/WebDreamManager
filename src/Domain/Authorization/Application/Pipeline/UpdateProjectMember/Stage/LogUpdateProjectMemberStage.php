<?php

declare(strict_types=1);

namespace App\Domain\Authorization\Application\Pipeline\UpdateProjectMember\Stage;

use App\Domain\Authorization\Application\Pipeline\UpdateProjectMember\UpdateProjectMemberCommand;
use App\Infrastructure\Pipeline\AbstractLogStage;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('app.authorization.project_member.update', attributes: ['priority' => -200])]
final readonly class LogUpdateProjectMemberStage extends AbstractLogStage
{
    public function handle(mixed $payload): mixed
    {
        assert($payload instanceof UpdateProjectMemberCommand);

        $this->logUserAction(
            "Project member permissions updated for {$payload->member->getUser()->getEmail()} on {$payload->member->getProject()->getName()}",
            'projects',
            ['project_id' => $payload->member->getProject()->getId(), 'user_id' => $payload->member->getUser()->getId()],
        );

        return $payload;
    }
}
