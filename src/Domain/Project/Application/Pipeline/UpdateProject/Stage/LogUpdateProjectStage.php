<?php

declare(strict_types=1);

namespace App\Domain\Project\Application\Pipeline\UpdateProject\Stage;

use App\Domain\Project\Application\Pipeline\UpdateProject\UpdateProjectCommand;
use App\Infrastructure\Pipeline\AbstractLogStage;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('app.project.update', attributes: ['priority' => -200])]
final readonly class LogUpdateProjectStage extends AbstractLogStage
{
    public function handle(mixed $payload): mixed
    {
        assert($payload instanceof UpdateProjectCommand);

        $this->logUserAction(
            "Project updated: {$payload->project->getName()}",
            'projects',
            ['id' => $payload->project->getId(), 'name' => $payload->project->getName()],
        );

        return $payload;
    }
}
