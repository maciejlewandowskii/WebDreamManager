<?php

declare(strict_types=1);

namespace App\Domain\Project\Application\Pipeline\CreateProject\Stage;

use App\Domain\Project\Application\Pipeline\CreateProject\CreateProjectCommand;
use App\Infrastructure\Pipeline\AbstractLogStage;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('app.project.create', attributes: ['priority' => -200])]
final readonly class LogCreateProjectStage extends AbstractLogStage
{
    public function handle(mixed $payload): mixed
    {
        assert($payload instanceof CreateProjectCommand);
        assert($payload->result !== null);

        $this->logUserAction(
            "Project created: {$payload->result->getName()}",
            'projects',
            ['id' => $payload->result->getId(), 'name' => $payload->result->getName()],
        );

        return $payload;
    }
}
