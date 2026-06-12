<?php

declare(strict_types=1);

namespace App\Domain\Project\Application\Pipeline\UpdateProject\Stage;

use App\Domain\Project\Application\Pipeline\UpdateProject\UpdateProjectCommand;
use App\Infrastructure\Pipeline\PipelineHandlerInterface;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('app.project.update', attributes: ['priority' => 200])]
final class ApplyProjectDataStage implements PipelineHandlerInterface
{
    public function handle(mixed $payload): mixed
    {
        assert($payload instanceof UpdateProjectCommand);

        $payload->data->applyTo($payload->project);

        return $payload;
    }
}
