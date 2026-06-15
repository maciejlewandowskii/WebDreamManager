<?php

declare(strict_types=1);

namespace App\Domain\Authorization\Application\Pipeline\RemoveProjectMember\Stage;

use App\Domain\Authorization\Application\Pipeline\RemoveProjectMember\RemoveProjectMemberCommand;
use App\Infrastructure\Pipeline\AbstractLogStage;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('app.authorization.project_member.remove', attributes: ['priority' => -200])]
final readonly class LogRemoveProjectMemberStage extends AbstractLogStage
{
    public function handle(mixed $payload): mixed
    {
        assert($payload instanceof RemoveProjectMemberCommand);

        $this->logUserAction(
            "Project member removed: {$payload->member->getUser()->getEmail()} from {$payload->member->getProject()->getName()}",
            'projects',
            ['project_id' => $payload->member->getProject()->getId(), 'user_id' => $payload->member->getUser()->getId()],
        );

        return $payload;
    }
}
