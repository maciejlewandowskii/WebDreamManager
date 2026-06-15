<?php

declare(strict_types=1);

namespace App\Domain\Authorization\Application\Pipeline\AddProjectMember\Stage;

use App\Domain\Authorization\Application\Pipeline\AddProjectMember\AddProjectMemberCommand;
use App\Infrastructure\Pipeline\AbstractLogStage;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('app.authorization.project_member.add', attributes: ['priority' => -200])]
final readonly class LogAddProjectMemberStage extends AbstractLogStage
{
    public function handle(mixed $payload): mixed
    {
        assert($payload instanceof AddProjectMemberCommand);
        assert($payload->result !== null);

        $this->logUserAction(
            "Project member added to {$payload->project->getName()}",
            'projects',
            ['project_id' => $payload->project->getId(), 'user_id' => $payload->result->getUser()->getId(), 'user_email' => $payload->result->getUser()->getEmail()],
        );

        return $payload;
    }
}
