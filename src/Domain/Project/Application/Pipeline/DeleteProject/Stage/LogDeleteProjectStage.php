<?php

declare(strict_types=1);

namespace App\Domain\Project\Application\Pipeline\DeleteProject\Stage;

use App\Domain\Project\Application\Pipeline\DeleteProject\DeleteProjectCommand;
use App\Infrastructure\Pipeline\AbstractLogStage;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('app.project.delete', attributes: ['priority' => -200])]
final readonly class LogDeleteProjectStage extends AbstractLogStage
{
    public function handle(mixed $payload): mixed
    {
        assert($payload instanceof DeleteProjectCommand);

        $this->logUserAction(
            "Project deleted: {$payload->project->getName()}",
            'projects',
            ['id' => $payload->project->getId(), 'name' => $payload->project->getName()],
        );

        return $payload;
    }
}
